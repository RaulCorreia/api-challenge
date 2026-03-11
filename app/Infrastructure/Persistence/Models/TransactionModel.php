<?php

namespace App\Infrastructure\Persistence\Models;

use App\Domain\Transaction\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'user_id_from',
        'user_id_to',
        'amount',
        'transaction_date',
        'transaction_type',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'transaction_type' => TransactionType::class,
    ];

    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id_from');
    }

    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id_to');
    }
}

