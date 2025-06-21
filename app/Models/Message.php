<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $customer_id
 * @property $role
 * @property $content
 */
class Message extends Model
{
    protected $fillable = [
        'customer_id',
        'role',
        'content'
    ];
}
