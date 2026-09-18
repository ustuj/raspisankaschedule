<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            if (! Schema::hasColumn('lessons', 'note')) {
                $table->text('note')->nullable()->after('lesson_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            if (Schema::hasColumn('lessons', 'note')) {
                $table->dropColumn('note');
            }
        });
    }
};
