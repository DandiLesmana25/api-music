<?php

namespace App\Models;

use App\Models\User;
use App\Models\ViewSong;
use App\Models\DetailPlaylist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Song extends Model
{
    protected $table = 'songs';

    protected $fillable = ['songs_title', 'songs_cover', 'songs_song', 'songs_release_date', 'songs_status', 'users_id', 'albums_id', 'songs_mood', 'songs_genre'];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }


    public function viewedSong()
    {
        return $this->hasMany(ViewSong::class, 'songs_id');
    }



    public function detailPlaylists()
    {
        return $this->hasMany(DetailPlaylist::class, 'songs_id', 'id');
    }


    /**
     * Get the genre that owns the Song
     *
     * @return \Illuminate\Genrebase\Eloqugenre_idns\BelongsTo
     */
}
