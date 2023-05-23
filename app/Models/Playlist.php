<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    // use HasFactory;

    protected $fillable = ['nama', 'gambar', 'status', 'id_user'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function detailPlaylists()
    {
        return $this->hasMany(DetailPlaylist::class, 'playlist_id', 'id');
    }
}
