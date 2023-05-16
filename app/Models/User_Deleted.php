<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class User_Deleted extends Model
{
    use HasFactory;

    protected $table = 'users_deleted';

    protected $fillable = [
        'id', 'name', 'email', 'deleted_at', 'deleted_by', 'created_at', 'updated_at'
    ];
}
