<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Firebase\JWT\JWT;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Definisikan tabel secara manual
    protected $table = 'users';

    protected $fillable = [
        'users_name',
        'users_email',
        'users_password',
        'users_role',
    ];

    protected $hidden = [
        'users_password'
    ];

    public function songs()
    {
        return $this->hasMany(Song::class, 'users_id');
    }

    public function viewedSongs()
    {
        return $this->hasMany(ViewSong::class, 'users_id');
    }

    public static function createUser($userData)
    {
        User::create([
            'users_name' => $userData['name'],
            'users_email' => $userData['email'],
            'users_password' => self::hashPassword($userData['password']),
        ]);
    }

    public static function generateToken($name, $email, $role, $id)
    {
        $payload = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'iat' => now()->timestamp,
            'id_login' => $id,
        ];

        return JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');
    }


    public static function validatePassword($password, $hashedPassword)
    {
        return Hash::check($password, $hashedPassword);
    }

    private static function hashPassword($password)
    {
        return Hash::make($password);
    }

    public function request_creator($value)
    {
        $this->req_upgrade = $value;
        $this->save();
    }
}
