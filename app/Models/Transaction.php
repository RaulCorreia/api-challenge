<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const DEPOSIT = 0;
    public const TRANSFER = 1;

    public const AUTHORIZED = 'Autorizado';

    protected $fillable = [
        'user_id_to', 'user_id_from', 'amount', 'transaction_date', 'transaction_type'
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'user_id_to');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id_from');
    }
}
