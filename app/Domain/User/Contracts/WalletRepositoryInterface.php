<?php

namespace App\Domain\User\Contracts;

interface WalletRepositoryInterface
{
    public function findByUserId(int $userId): ?object;

    public function create(int $userId, float $initialBalance = 0.0): object;

    public function debit(int $userId, float $amount): void;

    public function credit(int $userId, float $amount): void;
}

