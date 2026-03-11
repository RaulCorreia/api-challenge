<?php

namespace App\Domain\Transaction\Exceptions;

use RuntimeException;

class InsufficientFundsException extends RuntimeException
{
    public function __construct(string $message = "You don't have enough balance to complete this transfer.")
    {
        parent::__construct($message);
    }
}

