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
