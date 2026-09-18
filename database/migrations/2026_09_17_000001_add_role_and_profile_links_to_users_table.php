<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('admin')->after('password');
            }
            if (! Schema::hasColumn('users', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('role')->constrained('teachers')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('teacher_id')->constrained('groups')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
            }
            if (Schema::hasColumn('users', 'group_id')) {
                $table->dropForeign(['group_id']);
            }
            $columns = array_values(array_filter(
                ['teacher_id', 'group_id', 'role'],
                fn (string $column): bool => Schema::hasColumn('users', $column)
            ));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
