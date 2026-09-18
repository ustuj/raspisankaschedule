<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->restrictOnDelete();

            $table->unique(['teacher_id', 'subject_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};