<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'users_name' => 'Administrator',
            'users_email' => 'admin@admin.com',
            'users_role' => 'admin',
            'users_password' => 'admin',
            'users_last_login' => now(),
        ]);

        User::Factory()->count(10)->create();
    }
}
