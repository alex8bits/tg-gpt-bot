<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $name
 * @property $system_name
 * @property $starting_bot
 * @property $prompt
 * @property $rank
 */
class MainBot extends Model
{
    protected $fillable = [
        'name',
        'system_name',
        'starting_bot',
        'prompt',
        'rank',
    ];

    public function getBotsIds()
    {
        $owned = [];
        $bots = GPTBot::all();
        /** @var GPTBot $bot */
        foreach ($bots as $bot) {
            $mains = explode(',', $bot->main_bots);
            if (in_array($this->getAttribute('id'), $mains)) {
                $owned[] = $bot->id;
            }
        }

        return $owned;
    }
}
