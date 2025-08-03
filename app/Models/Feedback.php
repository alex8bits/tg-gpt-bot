<?php

namespace App\Models;

use App\Enums\BotTypes;
use App\Enums\FeedbackStates;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'customer_id',
        'status',
        'text',
        'bot_type'
    ];

    protected $casts = [
        'status' => FeedbackStates::class,
        'bot_type' => BotTypes::class
    ];
}
