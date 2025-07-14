<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $name
 */
class Category extends Model
{
    protected $fillable = [
        'name'
    ];
}
