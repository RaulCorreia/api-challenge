<?php

namespace App\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'document_type' => $this->document_type,
            'user_type'     => $this->whenLoaded('userType', fn () => $this->userType->name),
            'wallet'        => $this->whenLoaded('wallet', fn () => [
                'total' => (float) $this->wallet->total,
            ]),
            'created_at'    => $this->created_at?->toISOString(),
        ];
    }
}

