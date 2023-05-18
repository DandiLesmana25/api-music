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

    protected $fillable = ['judul', 'cover', 'lagu', 'tanggal_rilis', 'status', 'id_user', 'id_label'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function label()
    {
        return $this->belongsTo(Label::class, 'id_label');
    }

    public function viewedSong()
    {
        return $this->hasMany(ViewSong::class, 'id_lagu');
    }

    


    public function detailPlaylists()
    {
        return $this->hasMany(DetailPlaylist::class, 'song_id', 'id');
    }


    /**
     * Get the genre that owns the Song
     *
     * @return \Illuminate\Genrebase\Eloqugenre_idns\BelongsTo
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'genre_id', 'other_key');
    }

    

    
}
