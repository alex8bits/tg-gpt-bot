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

    public function scopeWelcome($query)
    {
        return $query->whereType(BotTypes::WELCOME);
    }

    public function scopeCommon($query)
    {
        return $query->whereType(BotTypes::COMMON);
    }

    public function scopeSpreader($query)
    {
        return $query->whereType(BotTypes::SPREADER);
    }

    public function scopeModerator($query)
    {
        return $query->whereType(BotTypes::MODERATOR);
    }
}
