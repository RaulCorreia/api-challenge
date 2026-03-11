<?php

namespace App\Domain\Transaction\Exceptions;

use RuntimeException;

class UnauthorizedTransactionException extends RuntimeException
{
    public function __construct(string $message = 'This transaction was not authorized by the payment provider.')
    {
        parent::__construct($message);
    }
}

