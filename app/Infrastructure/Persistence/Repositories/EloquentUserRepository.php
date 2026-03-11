<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Infrastructure\Persistence\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?object
    {
        return UserModel::with('userType', 'wallet')->find($id);
    }

    public function findByEmail(string $email): ?object
    {
        return UserModel::where('email', $email)->first();
    }

    public function create(array $data): object
    {
        return UserModel::create($data);
    }
}

