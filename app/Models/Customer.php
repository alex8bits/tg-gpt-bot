<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $name
 * @property $telegram_id
 * @property $phone
 * @property $rating
 */
class Customer extends Model
{
    protected $fillable = [
        'name',
        'telegram_id',
        'phone',
        'rating',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->rating = (int)(config('open_ai.user_context_threshold'));
        });
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
