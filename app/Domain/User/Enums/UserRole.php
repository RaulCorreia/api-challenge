<?php

namespace App\Domain\User\Enums;

enum UserRole: string
{
    case STANDARD = 'standart';
    case SHOP     = 'shop';

    public function canTransfer(): bool
    {
        return $this === self::STANDARD;
    }
}

