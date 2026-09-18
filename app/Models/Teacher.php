<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'color',
    ];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subjects'
        );
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}