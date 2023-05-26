<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorRequest extends Model
{
    use HasFactory;

    protected $table = 'creator_requests';

    protected $fillable = [
        'users_id',
        'request_date',
        'status',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
