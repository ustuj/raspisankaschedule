<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = [
        'group_id', 'teacher_id', 'subject_id', 'classroom_id', 'day',
        'week1_lesson', 'week2_lesson', 'lesson_type', 'note',
    ];

    protected $casts = [
        'week1_lesson' => 'integer',
        'week2_lesson' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
