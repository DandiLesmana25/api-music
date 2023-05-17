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

    /** 
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    /**
     * Get all of the playlist for the User
     *
     * @return \Illuminate\DatabPlaylistquent\Relaid_userny
     */


     public function playlist(): HasMany
    {
        return $this->hasMany(Playlist::class, 'id_user', 'id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
