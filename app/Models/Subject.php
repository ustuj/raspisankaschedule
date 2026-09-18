<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'lesson_type',
    ];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'teacher_subjects'
        );
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}