<?php

use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'standart',
            'shop'
        ];

        foreach ($types as $type) {
            UserType::firstOrCreate(['name' => $type]);
        }
    }
}
