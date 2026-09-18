<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')
                ->constrained('groups')
                ->restrictOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->restrictOnDelete();

            $table->foreignId('classroom_id')
                ->nullable()
                ->constrained('classrooms')
                ->nullOnDelete();

            $table->string('day', 20);

            $table->unsignedTinyInteger('week1_lesson')->nullable();
            $table->unsignedTinyInteger('week2_lesson')->nullable();

            $table->string('lesson_type', 50);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};