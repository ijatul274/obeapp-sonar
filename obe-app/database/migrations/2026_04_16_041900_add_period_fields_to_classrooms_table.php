<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('academic_year', 9)->nullable()->after('semester');  // "2024/2025"
            $table->enum('period_type', ['ganjil', 'genap'])->nullable()->after('academic_year');
            $table->foreignId('lecturer_id')->nullable()->after('period_type')
                  ->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
            $table->dropColumn(['academic_year', 'period_type', 'lecturer_id']);
        });
    }
};
