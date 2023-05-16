<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Song extends Model
{
    // use HasFactory;
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
}
