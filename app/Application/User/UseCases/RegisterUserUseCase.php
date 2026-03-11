<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\RegisterUserInputDTO;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface   $userRepository,
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    public function execute(RegisterUserInputDTO $dto): object
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->userRepository->create([
                'name'          => $dto->name,
                'email'         => $dto->email,
                'password'      => Hash::make($dto->password),
                'document'      => $dto->document,
                'document_type' => $dto->documentType->value,
                'user_type_id'  => $dto->userTypeId,
            ]);

            $this->walletRepository->create($user->id);

            return $user;
        });
    }
}

