<?php

namespace App\Models;

use App\Models\Song;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Genre extends Model
{
    use HasFactory;

    protected $table = 'genre';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'genre_name',
    ];


    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}
