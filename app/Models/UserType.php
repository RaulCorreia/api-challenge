<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    public const STANDART_USER = 'standart';
    public const SHOP_USER = 'shop';

    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
