<?php

namespace App\Domain\Transaction\Exceptions;

use RuntimeException;

class ShopUserCannotTransferException extends RuntimeException
{
    public function __construct(string $message = 'Shop accounts are not allowed to initiate transfers.')
    {
        parent::__construct($message);
    }
}

