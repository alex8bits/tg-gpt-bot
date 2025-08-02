<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $main_bot_id
 */
class Dialog extends Model
{
    protected $fillable = [
        'main_bot_id'
    ];
}
