<?php

namespace App\Infrastructure\Persistence\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type_id',
        'document',
        'document_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserTypeModel::class, 'user_type_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(WalletModel::class, 'user_id');
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'user_id_from');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'user_id_to');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
