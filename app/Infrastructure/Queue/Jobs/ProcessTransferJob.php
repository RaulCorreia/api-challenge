<?php

namespace App\Infrastructure\Queue\Jobs;

use App\Application\Transaction\Contracts\AuthorizerServiceInterface;
use App\Domain\Transaction\Contracts\TransactionRepositoryInterface;
use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Exceptions\UnauthorizedTransactionException;
use App\Domain\User\Contracts\WalletRepositoryInterface;
use App\Infrastructure\Notifications\ReceiverTransactionNotification;
use App\Infrastructure\Notifications\SenderTransactionSuccessNotification;
use App\Infrastructure\Notifications\TransactionFailedNotification;
use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Max attempts before the job is failed */
    public int $tries = 3;

    /** @var array<int> Backoff in seconds between retries */
    public array $backoff = [10, 30, 60];

    /** @var int Job timeout in seconds */
    public int $timeout = 90;

    public function __construct(
        private readonly TransactionEntity $transaction
    ) {}

    public function handle(
        AuthorizerServiceInterface     $authorizer,
        TransactionRepositoryInterface $transactionRepository,
        WalletRepositoryInterface      $walletRepository,
    ): void {
        DB::transaction(function () use ($authorizer, $transactionRepository, $walletRepository): void {
            // Check external authorizer before any DB change
            $authorizer->authorize();

            // Persist the transaction
            $persisted = $transactionRepository->create($this->transaction);

            // Debit sender, credit receiver atomically
            $walletRepository->debit($persisted->userIdFrom, $persisted->amount->amount);
            $walletRepository->credit($persisted->userIdTo, $persisted->amount->amount);

            // Fire notifications on the low-priority queue
            $sender   = UserModel::find($persisted->userIdFrom);
            $receiver = UserModel::find($persisted->userIdTo);

            $sender->notify(
                (new SenderTransactionSuccessNotification($persisted))->onQueue('notifications')
            );
            $receiver->notify(
                (new ReceiverTransactionNotification($persisted))->onQueue('notifications')
            );
        });
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ProcessTransferJob failed', [
            'sender_id'    => $this->transaction->userIdFrom,
            'receiver_id'  => $this->transaction->userIdTo,
            'amount'       => $this->transaction->amount->amount,
            'error'        => $exception->getMessage(),
        ]);

        $sender = UserModel::find($this->transaction->userIdFrom);

        if ($sender) {
            $sender->notify(
                (new TransactionFailedNotification($sender))->onQueue('notifications')
            );
        }
    }
}

