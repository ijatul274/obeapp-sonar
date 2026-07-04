<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_program_studi', function (Blueprint $table) {
            if (!Schema::hasColumn('obe_program_studi', 'kode')) {
                $table->string('kode', 20)->nullable()->after('id');
            }
        });

        Schema::table('obe_jurusan', function (Blueprint $table) {
            if (!Schema::hasColumn('obe_jurusan', 'kode')) {
                $table->string('kode', 20)->nullable()->after('id');
            }
        });

        Schema::table('obe_pengguna', function (Blueprint $table) {
            if (!Schema::hasColumn('obe_pengguna', 'jurusan_id')) {
                $table->foreignId('jurusan_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('obe_jurusan')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('obe_pengguna', function (Blueprint $table) {
            if (Schema::hasColumn('obe_pengguna', 'jurusan_id')) {
                $table->dropConstrainedForeignId('jurusan_id');
            }
        });

        Schema::table('obe_jurusan', function (Blueprint $table) {
            if (Schema::hasColumn('obe_jurusan', 'kode')) {
                $table->dropColumn('kode');
            }
        });

        Schema::table('obe_program_studi', function (Blueprint $table) {
            if (Schema::hasColumn('obe_program_studi', 'kode')) {
                $table->dropColumn('kode');
            }
        });
    }
};
