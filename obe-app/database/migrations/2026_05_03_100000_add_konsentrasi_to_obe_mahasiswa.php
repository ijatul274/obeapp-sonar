<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_mahasiswa', function (Blueprint $table) {
            $table->string('konsentrasi', 10)->nullable()->after('program_studi_id');
        });
    }

    public function down(): void
    {
        Schema::table('obe_mahasiswa', function (Blueprint $table) {
            $table->dropColumn('konsentrasi');
        });
    }
};
