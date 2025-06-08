<?php

namespace App\Models;

use App\Models\Model;
use LadyPHP\Database\ORM\Concerns\HasSoftDeletes;

class User extends Model
{
    use HasSoftDeletes;

    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password'
    ];

    protected static array $hidden = [
        'password'
    ];

    protected static array $casts = [
        'is_admin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected static array $validation = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6'
    ];
} 