<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function transfer(Request $request)
    {
        $validatedData = $this->validate($request, [
            'user_id_to' => ['required', 'int', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'gt:0']
        ]);

        $result = $this->transactionService->transfer($validatedData);
        return response()->json([$result['content']], $result['code']);
    }

}
