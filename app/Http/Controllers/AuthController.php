<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validatedData = $this->validate($request, [
            'name' => ['required', 'min:5'],
            'email' => ['required', 'email', 'unique:users'],
            'cpf' => ['required_without_all:cnpj', 'size:11', 'unique:users,document'],
            'cnpj' => ['required_without_all:cpf', 'size:14', 'unique:users,document'],
            'user_type_id' => ['required', 'exists:user_types,id'],
            'password' => ['required', 'min:6'],
        ]);

        $result = $this->authService->createUser($validatedData);

        return response()->json([$result['content']], $result['code']);
    }


    public function login(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => ['required'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken('authApi')->accessToken;
            $result = formatResponse(['token' => $token], 200, true);
            return response()->json($result['content'], $result['code']);
        } else {
            $result = formatResponse('Unauthorized', 401, false);
            return response()->json($result['content'], $result['code']);
        }
    }
}
