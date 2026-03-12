<?php

namespace App\Application\User\UseCases;

use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class AuthenticateUserUseCase
{
    /**
     * Attempt to authenticate the user and return a Sanctum token.
     *
     * @return array{token: string, user: object}
     * @throws AuthenticationException
     */
    public function execute(string $email, string $password): array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            throw new AuthenticationException('Invalid credentials.');
        }

        /** @var UserModel $user */
        $user  = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }
}

