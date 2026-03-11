<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\UserTypeModel;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['standart', 'shop'];

        foreach ($types as $type) {
            UserTypeModel::firstOrCreate(['name' => $type]);
        }
    }
}

