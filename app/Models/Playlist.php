<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    // use HasFactory;

    protected $fillable = ['playlists_name', 'playlists_cover', 'playlists_status', 'users_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function detailPlaylists()
    {
        return $this->hasMany(DetailPlaylist::class, 'playlist_id', 'id');
    }
}
