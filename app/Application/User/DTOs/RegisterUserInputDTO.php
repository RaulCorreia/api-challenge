<?php

namespace App\Application\User\DTOs;

use App\Domain\User\Enums\DocumentType;

final readonly class RegisterUserInputDTO
{
    public function __construct(
        public string       $name,
        public string       $email,
        public string       $password,
        public string       $document,
        public DocumentType $documentType,
        public int          $userTypeId,
    ) {}
}

