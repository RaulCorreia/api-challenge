<?php

namespace App\Services;


use App\Models\Wallet;
use App\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function createUser(array $data): array
    {
        DB::beginTransaction();

        try {
            $data['password'] = bcrypt($data['password']);

            if (array_key_exists('cpf', $data)) {
                $data['document_type'] = User::DOCUMENT_CPF;
                $data['document'] = $data['cpf'];
                unset($data['cpf']);
            } else if (array_key_exists('cnpj', $data)) {
                $data['document_type'] = User::DOCUMENT_CNPJ;
                $data['document'] = $data['cnpj'];
                unset($data['cnpj']);
            }

            $user = User::create($data);
            Wallet::create([
                'user_id' => $user->id,
                'total' => 0
            ]);

            DB::commit();

            return formatResponse(
                ['token' => $user->createToken('authApi')->accessToken],
                200,
                true
            );

        } catch (\Exception $e) {
            DB::rollBack();
            info($e);
            return formatResponse('Something was wrong, contact the support', 500, false);
        }
    }
}
