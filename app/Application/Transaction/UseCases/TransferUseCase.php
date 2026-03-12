<?php

namespace App\Application\Transaction\UseCases;

use App\Application\Transaction\DTOs\TransferInputDTO;
use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Exceptions\InsufficientFundsException;
use App\Domain\Transaction\Exceptions\ShopUserCannotTransferException;
use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\Contracts\WalletRepositoryInterface;
use App\Domain\User\Enums\UserRole;
use App\Infrastructure\Queue\Jobs\ProcessTransferJob;
use DateTimeImmutable;

class TransferUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface   $userRepository,
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    public function execute(TransferInputDTO $dto): TransactionEntity
    {
        // 1. Load sender and validate role
        $sender = $this->userRepository->findById($dto->senderId);

        $role = UserRole::from($sender->userType->name);

        if (! $role->canTransfer()) {
            throw new ShopUserCannotTransferException();
        }

        // 2. Validate balance
        $wallet = $this->walletRepository->findByUserId($dto->senderId);

        $balance = new Money((float) $wallet->total);

        if ($dto->amount->isGreaterThan($balance)) {
            throw new InsufficientFundsException();
        }

        // 3. Build domain entity and dispatch to the transactions queue
        $transaction = new TransactionEntity(
            userIdFrom:      $dto->senderId,
            userIdTo:        $dto->recipientId,
            amount:          $dto->amount,
            type:            TransactionType::TRANSFER,
            transactionDate: new DateTimeImmutable(),
        );

        ProcessTransferJob::dispatch($transaction)->onQueue('transactions');

        return $transaction;
    }
}

