<?php

namespace App\Domain\Transaction\Enums;

enum TransactionType: int
{
    case DEPOSIT  = 0;
    case TRANSFER = 1;
}

