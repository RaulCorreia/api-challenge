<?php

namespace App;

use App\Models\Transaction;
use App\Models\UserType;
use App\Models\Wallet;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public const DOCUMENT_CPF = 0;
    public const DOCUMENT_CNPJ = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'user_type_id', 'document', 'document_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->sendedTransfer->merge($this->receivedTransfer);
    }

    public function sendedTransfer()
    {
        return $this->hasMany(Transaction::class, 'user_id_from');
    }

    public function receivedTransfer()
    {
        return $this->hasMany(Transaction::class, 'user_id_to');
    }
}
