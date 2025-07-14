<?php

namespace App\Models;

use App\Enums\BotTypes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $name
 * @property $theme
 * @property $prompt
 * @property $system_request
 * @property $type
 */
class GPTBot extends Model
{
    protected $fillable = [
        'name',
        'theme',
        'prompt',
        'system_request',
        'type',
    ];

    protected $casts = [
        'type' => BotTypes::class
    ];

    public function getPrompt()
    {
        $path = public_path('abc/files/languages/1/dictionary/common.php');
        if (file_exists($path)) {
            require $path;
            $prompt = $lang['common']['prompt'] ?? '';
        } else {
            $prompt = '';
        }

        return $prompt . '. ' . $this->prompt;
    }

    public function scopeWelcome($query)
    {
        return $query->whereType(BotTypes::WELCOME);
    }

    public function scopeCommon($query)
    {
        return $query->whereIn('type', [BotTypes::COMMON, BotTypes::FEEDBACK]);
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
