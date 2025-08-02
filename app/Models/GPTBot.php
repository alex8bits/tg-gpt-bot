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
 * @property $rank
 * @property $main_bots
 */
class GPTBot extends Model
{
    protected $fillable = [
        'name',
        'theme',
        'prompt',
        'system_request',
        'type',
        'rank',
        'main_bots'
    ];

    protected $casts = [
        'type' => BotTypes::class
    ];

    public function getPrompt($dialog = null)
    {
        if (!$dialog) {
            return  $this->prompt;
        }
        /** @var Dialog $dialog */
        $dialog = Dialog::find($dialog);
        if (!$dialog || !$dialog->main_bot_id) {
            return $this->prompt;
        }
        /** @var MainBot $main_bot */
        $main_bot = MainBot::find($dialog->main_bot_id);
        if (!$main_bot) {
            return $this->prompt;
        }

        return  $this->prompt . '. ' . $main_bot->prompt;
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
