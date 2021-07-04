<?php

namespace App\Jobs;

use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $authorization = $response->getBody()->getContents();

            if ($authorization !== Transaction::AUTHORIZED) {
                throw new \Exception('Unauthorized transaction');
            }

            Transaction::create($this->data);

            DB::commit();
            // Envia email informando a transação
            //return formatResponse('Transfer successful', 200, true);
        } catch (\Exception $e) {
            DB::rollBack();
            info($e);
            // Envia email informando o erro

            // or transaction is unauthorized
            //return formatResponse('Something was wrong, contact the support', 500, false);
        }
    }
}
