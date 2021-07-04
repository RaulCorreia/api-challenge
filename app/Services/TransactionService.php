<?php

namespace App\Services;


use App\Jobs\TransactionJob;
use App\Models\Transaction;
use App\Models\UserType;
use App\Models\Wallet;
use App\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function transfer(array $data): array
    {
        if ($data['amount'] > auth()->user()->wallet->total) {
            return formatResponse("You don't have enough balance", 200, false);
        }

        if (!$this->validateTransfer(auth()->user())) {
            return formatResponse("Unable to perform this transaction", 200, false);
        }

        $data['transaction_type'] = Transaction::TRANSFER;
        $data['user_id_from'] = auth()->user()->id;
        $data['transaction_date'] = now();

        dispatch(new TransactionJob($data));

        return formatResponse('Transfer in progress', 200, true);
    }

    private function validateTransfer(User $userSender): bool
    {
        if ($userSender->userType->name === UserType::SHOP_USER) {
            return false;
        }

        return true;
    }
}
