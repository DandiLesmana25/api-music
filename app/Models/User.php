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
        'name',
        'email',
        'password',
        'last_login'
    ];

    public function songs()
    {
        return $this->hasMany(Song::class, 'id_user');
    }

    public function viewedSongs()
    {
        return $this->hasMany(ViewSong::class, 'id_user');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function request_creator($value)
    {
        $this->req_upgrade = $value;
        $this->save();
    }

    public function last_login()
    {
        $this->req_upgrade = now()->timestamp;
        $this->save();
    }
}
