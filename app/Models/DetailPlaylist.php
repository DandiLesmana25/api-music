<?php

namespace App\Models;

use App\Models\Song;
use App\Models\Playlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailPlaylist extends Model
{
    protected $table = 'detail_playlist';

    protected $fillable = [
        'detail_playlist_playlists_id',
        'detail_playlist_song_id',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class, 'detail_playlist_playlists_id');
    }

    public function song()
    {
        return $this->belongsTo(Song::class, 'detail_playlist_song_id');
    }
}
