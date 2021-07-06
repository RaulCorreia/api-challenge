<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Notifications\ReceiverTransaction;
use App\Notifications\SendTransactionSuccess;
use App\Notifications\TransactionFail;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {

            $client = app(Client::class);

            $response = $client->get("https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6");
            $authorization = json_decode($response->getBody()->getContents());

            if ($authorization->message !== Transaction::AUTHORIZED) {
                throw new \Exception('Unauthorized transaction');
            } else {
                info($authorization->message);
            }

            $transaction = Transaction::create($this->data);

            DB::commit();

            $transaction->sender->notify(new SendTransactionSuccess($transaction));
            $transaction->receiver->notify(new ReceiverTransaction($transaction));
        } catch (\Exception $e) {
            DB::rollBack();
            info($e);

            $userFrom = User::find($this->data['user_id_from']);
            $userFrom->notify(new TransactionFail($userFrom));
        }
    }
}
