<?php

namespace App\Presentation\Http\Requests;

use App\Domain\User\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:5', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:6'],
            'cpf'          => ['required_without:cnpj', 'string', 'size:11', 'unique:users,document'],
            'cnpj'         => ['required_without:cpf',  'string', 'size:14', 'unique:users,document'],
            'user_type_id' => ['required', 'integer', 'exists:user_types,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.required_without'  => 'Either a CPF or CNPJ must be provided.',
            'cnpj.required_without' => 'Either a CPF or CNPJ must be provided.',
            'cpf.size'              => 'CPF must be exactly 11 digits.',
            'cnpj.size'             => 'CNPJ must be exactly 14 digits.',
        ];
    }

    /**
     * Resolve document value and type from the validated input.
     */
    public function resolvedDocument(): array
    {
        if ($this->filled('cpf')) {
            return ['document' => $this->cpf, 'type' => DocumentType::CPF];
        }

        return ['document' => $this->cnpj, 'type' => DocumentType::CNPJ];
    }
}

