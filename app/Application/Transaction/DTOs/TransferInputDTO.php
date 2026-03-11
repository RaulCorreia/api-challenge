<?php

namespace App\Application\Transaction\DTOs;

use App\Domain\Transaction\ValueObjects\Money;

final readonly class TransferInputDTO
{
    public function __construct(
        public int   $senderId,
        public int   $recipientId,
        public Money $amount,
    ) {}
}

