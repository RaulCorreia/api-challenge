<?php

namespace App\Domain\Transaction\Entities;

use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\ValueObjects\Money;
use DateTimeImmutable;

final class Transaction
{
    public function __construct(
        public readonly int             $userIdFrom,
        public readonly int             $userIdTo,
        public readonly Money           $amount,
        public readonly TransactionType $type,
        public readonly DateTimeImmutable $transactionDate,
        public readonly ?int            $id = null,
    ) {}
}

