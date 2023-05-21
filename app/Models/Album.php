<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['judul', 'artis', 'tanggal_rilis', 'genre', 'cover', 'id_user', 'status'];


    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
