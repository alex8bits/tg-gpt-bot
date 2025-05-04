<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property $id
 * @property $name
 * @property $telegram_id
 * @property $rating
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'telegram_id',
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
