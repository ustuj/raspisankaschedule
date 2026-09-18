<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = ['name', 'number', 'floor', 'capacity'];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
