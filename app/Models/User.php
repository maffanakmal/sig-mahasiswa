<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $incrementing = true;

    protected $keyType = 'integer';

    protected $fillable = [
        'user_uuid',
        'nama_user',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];
}