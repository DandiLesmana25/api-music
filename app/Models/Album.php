<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['albums_title', 'albums_artist', 'albums_release_date', 'albums_genre', 'albums_cover', 'users_id', 'albums_status'];


    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
