<?php

namespace App\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'sender_id'        => $this->userIdFrom,
            'receiver_id'      => $this->userIdTo,
            'amount'           => $this->amount->amount,
            'amount_formatted' => 'R$ ' . $this->amount->format(),
            'type'             => $this->type->name,
            'transaction_date' => $this->transactionDate->format('Y-m-d H:i:s'),
        ];
    }
}

