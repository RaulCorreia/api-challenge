<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserTypeModel extends Model
{
    protected $table = 'user_types';

    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(UserModel::class, 'user_type_id');
    }
}

