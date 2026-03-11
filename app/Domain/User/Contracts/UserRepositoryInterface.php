<?php

namespace App\Domain\User\Contracts;

interface UserRepositoryInterface
{
    public function findById(int $id): ?object;

    public function findByEmail(string $email): ?object;

    public function create(array $data): object;
}

