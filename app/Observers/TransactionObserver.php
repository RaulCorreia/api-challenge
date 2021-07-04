<?php

namespace App\Observers;

use App\Models\Wallet;
use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the transaction "created" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
        $senderWallet =  Wallet::where('user_id', $transaction->user_id_from)->first();
        $senderWallet->update(['total' => $senderWallet->total - $transaction->amount]);

        $receiverWallet = Wallet::where('user_id', $transaction->user_id_to)->first();
        $receiverWallet->update(['total' => $receiverWallet->total + $transaction->amount]);
    }

}
