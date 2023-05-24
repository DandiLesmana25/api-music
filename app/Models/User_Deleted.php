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
        'id', 'users_deleted_name', 'users_deleted_email', 'users_deleted_deleted_at', 'users_deleted_deleted_by',
    ];  // 'created_at', 'updated_at'
}
