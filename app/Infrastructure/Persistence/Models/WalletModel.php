<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class WalletModel extends Model
{
    protected $table = 'wallets';

    protected $fillable = ['user_id', 'total'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}

