<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPlaylist extends Model
{
    // use HasFactory;
    protected $table = detail_playlist;

    protected $fillable = [
        'playlist_id',
        'song_id',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class, 'playlist_id');
    }

    public function song()
    {
        return $this->belongsTo(Song::class, 'song_id');
    }
}
