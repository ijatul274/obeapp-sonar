<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_mata_kuliah', function (Blueprint $table) {
            $table->enum('wajib_pilihan', ['W', 'P'])->default('W')->after('semester');
        });
    }

    public function down(): void
    {
        Schema::table('obe_mata_kuliah', function (Blueprint $table) {
            $table->dropColumn('wajib_pilihan');
        });
    }
};
