<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $role
 * @property $content
 */
class Message extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'content'
    ];
}
