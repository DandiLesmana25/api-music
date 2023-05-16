<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewSong extends Model
{
    // use HasFactory;
    protected $table = 'view_song';
    protected $fillable = ['id_user', 'id_lagu'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function song()
    {
        return $this->belongsTo(Song::class, 'id_lagu');
    }
}
