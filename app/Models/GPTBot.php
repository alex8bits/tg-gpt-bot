<?php

namespace App\Models;

use App\Enums\BotTypes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $name
 * @property $theme
 * @property $prompt
 * @property $type
 */
class GPTBot extends Model
{
    protected $fillable = [
        'name',
        'theme',
        'prompt',
        'type',
    ];

    protected $casts = [
        'type' => BotTypes::class
    ];
}
