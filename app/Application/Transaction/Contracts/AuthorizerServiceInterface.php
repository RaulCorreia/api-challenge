<?php

namespace App\Application\Transaction\Contracts;

interface AuthorizerServiceInterface
{
    /**
     * Ask the external payment provider if a transaction is authorized.
     * Throws UnauthorizedTransactionException when denied.
     */
    public function authorize(): void;
}

