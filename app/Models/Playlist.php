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


    /**
     * Get all of the comments for the Playlist
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function detailplaylist(): HasMany
    // {
    //     return $this->hasMany(detailplaylist::class, 'playlist_id', 'id');
    // }

    public function detailPlaylists()
    {
        return $this->hasMany(DetailPlaylist::class, 'playlist_id', 'id');
    }

}