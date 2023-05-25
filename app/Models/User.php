<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Playlist;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // definisikan tabel secara manual
    protected $table = 'users';

    protected $fillable = [
        'users_name',
        'users_email',
        'users_password',
        'users_last_login',
        'users_role',
    ];

    public function songs()
    {
        return $this->hasMany(Song::class, 'users_id');
    }

    public function viewedSongs()
    {
        return $this->hasMany(ViewSong::class, 'users_id');
    }

    protected $hidden = [
        'users_password'
    ];


    public function setPasswordAttribute($users_password)
    {
        $this->attributes['users_password'] = bcrypt($users_password);
    }


    public function request_creator($value)
    {
        $this->req_upgrade = $value;
        $this->save();
    }

    // public function last_login()
    // {
    //     $this->req_upgrade = now()->timestamp;
    //     $this->save();
    // }
}
