<?php

namespace App\Models;

use App\Enums\FeedbackStates;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'customer_id',
        'status',
        'text'
    ];

    protected $casts = [
        'status' => FeedbackStates::class
    ];
}
