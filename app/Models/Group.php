<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'speciality',
        'course',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}