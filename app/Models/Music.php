<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $table = 'songs';

    protected $fillable = [
        'judul', 'cover', 'lagu', 'tanggal_rilis', 'status', 'id_user', 'id_label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function label()
    {
        return $this->belongsTo(Label::class, 'id_label');
    }
}
