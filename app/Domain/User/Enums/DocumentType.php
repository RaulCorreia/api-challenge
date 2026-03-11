<?php

namespace App\Domain\User\Enums;

enum DocumentType: int
{
    case CPF  = 0;
    case CNPJ = 1;
}

