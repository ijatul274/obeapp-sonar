<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cpmks', function (Blueprint $table) {
            $table->foreignId('cpl_id')->nullable()->after('course_id')->constrained('cpls')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cpmks', function (Blueprint $table) {
            $table->dropForeign(['cpl_id']);
            $table->dropColumn('cpl_id');
        });
    }
};
