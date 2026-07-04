<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_program_studi', function (Blueprint $table) {
            if (!Schema::hasColumn('obe_program_studi', 'jurusan_id')) {
                $table->foreignId('jurusan_id')
                    ->nullable()
                    ->after('kode')
                    ->constrained('obe_jurusan')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('obe_program_studi', function (Blueprint $table) {
            if (Schema::hasColumn('obe_program_studi', 'jurusan_id')) {
                $table->dropConstrainedForeignId('jurusan_id');
            }
        });
    }
};
