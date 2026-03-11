<?php

namespace App\Application\User\UseCases;

use Illuminate\Support\Facades\Auth;

class AuthenticateUserUseCase
{
    /**
     * Attempt to authenticate the user and return a Sanctum token.
     *
     * @return array{token: string, user: object}
     * @throws \Illuminate\Validation\UnauthorizedException
     */
    public function execute(string $email, string $password): array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            throw new \Illuminate\Auth\AuthenticationException('Invalid credentials.');
        }

        /** @var \App\Infrastructure\Persistence\Models\UserModel $user */
        $user  = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }
}

