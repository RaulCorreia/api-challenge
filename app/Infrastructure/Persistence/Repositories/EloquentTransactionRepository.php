<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Transaction\Contracts\TransactionRepositoryInterface;
use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Infrastructure\Persistence\Models\TransactionModel;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function create(TransactionEntity $transaction): TransactionEntity
    {
        $model = TransactionModel::create([
            'user_id_from'     => $transaction->userIdFrom,
            'user_id_to'       => $transaction->userIdTo,
            'amount'           => $transaction->amount->amount,
            'transaction_date' => $transaction->transactionDate->format('Y-m-d H:i:s'),
            'transaction_type' => $transaction->type->value,
        ]);

        return new TransactionEntity(
            userIdFrom:      $model->user_id_from,
            userIdTo:        $model->user_id_to,
            amount:          $transaction->amount,
            type:            $transaction->type,
            transactionDate: $transaction->transactionDate,
            id:              $model->id,
        );
    }
}

