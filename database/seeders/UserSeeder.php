<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        User::create([
            'users_name' => 'Administrator',
            'users_email' => 'admin@admin.com',
            'users_role' => 'admin',
            'users_password' => Hash::make('admin'),
        ]);

        User::Factory()->count(10)->create();
    }
}
