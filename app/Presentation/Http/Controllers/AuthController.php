<?php

namespace App\Presentation\Http\Controllers;

use App\Application\User\DTOs\RegisterUserInputDTO;
use App\Application\User\UseCases\AuthenticateUserUseCase;
use App\Application\User\UseCases\RegisterUserUseCase;
use App\Presentation\Http\Requests\LoginRequest;
use App\Presentation\Http\Requests\RegisterUserRequest;
use App\Presentation\Http\Resources\UserResource;
use App\Presentation\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly RegisterUserUseCase     $registerUseCase,
        private readonly AuthenticateUserUseCase $authenticateUseCase,
    ) {}

    /**
     * Register a new user and return a Sanctum token.
     *
     * @response 201 { "success": true, "data": { "token": "...", "user": {} } }
     * @response 422 { "success": false, "message": "The given data was invalid.", "errors": {} }
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        ['document' => $document, 'type' => $documentType] = $request->resolvedDocument();

        $dto = new RegisterUserInputDTO(
            name:         $request->name,
            email:        $request->email,
            password:     $request->password,
            document:     $document,
            documentType: $documentType,
            userTypeId:   $request->user_type_id,
        );

        $user  = $this->registerUseCase->execute($dto);
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->created([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 'Account created successfully.');
    }

    /**
     * Authenticate and return a Sanctum bearer token.
     *
     * @response 200 { "success": true, "data": { "token": "..." } }
     * @response 401 { "success": false, "message": "Invalid credentials." }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticateUseCase->execute(
            email:    $request->email,
            password: $request->password,
        );

        return $this->success([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 'Logged in successfully.');
    }

    /**
     * Revoke the current token (logout).
     *
     * @response 204
     */
    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return $this->noContent();
    }
}

