<?php

namespace App\Domain\Transaction\Contracts;

use App\Domain\Transaction\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function create(Transaction $transaction): Transaction;
}

