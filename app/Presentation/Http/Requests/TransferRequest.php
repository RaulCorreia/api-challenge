<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id_to' => ['required', 'integer', 'exists:users,id', 'different:' . auth()->id()],
            'amount'     => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id_to.different' => 'You cannot transfer funds to yourself.',
            'amount.gt'            => 'The transfer amount must be greater than zero.',
        ];
    }
}

