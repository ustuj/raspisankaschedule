<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('number', 30)->nullable()->after('name');
            $table->string('floor', 20)->nullable()->after('number');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['number', 'floor']);
        });
    }
};
