<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $customer_id
 * @property $gpt_bot_id
 * @property $role
 * @property $content
 */
class Message extends Model
{
    protected $fillable = [
        'customer_id',
        'gpt_bot_id',
        'role',
        'content'
    ];
}
