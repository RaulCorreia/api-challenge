<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\User\Contracts\WalletRepositoryInterface;
use App\Infrastructure\Persistence\Models\WalletModel;

class EloquentWalletRepository implements WalletRepositoryInterface
{
    public function findByUserId(int $userId): ?object
    {
        return WalletModel::where('user_id', $userId)->first();
    }

    public function create(int $userId, float $initialBalance = 0.0): object
    {
        return WalletModel::create([
            'user_id' => $userId,
            'total'   => $initialBalance,
        ]);
    }

    public function debit(int $userId, float $amount): void
    {
        WalletModel::where('user_id', $userId)
            ->decrement('total', $amount);
    }

    public function credit(int $userId, float $amount): void
    {
        WalletModel::where('user_id', $userId)
            ->increment('total', $amount);
    }
}

