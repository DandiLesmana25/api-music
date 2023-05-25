<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewSong extends Model
{
    // use HasFactory;
    protected $table = 'view_song';
    protected $fillable = ['users_id', 'songs_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function song()
    {
        return $this->belongsTo(Song::class, 'songs_id');
    }
}
