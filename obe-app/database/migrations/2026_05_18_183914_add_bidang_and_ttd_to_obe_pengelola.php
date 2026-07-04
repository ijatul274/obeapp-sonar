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
    Schema::table('obe_pengelola', function (Blueprint $table) {
        // Tambah kolom hanya jika belum ada
        if (!Schema::hasColumn('obe_pengelola', 'bidang')) {
            $table->string('bidang')->nullable()->after('jabatan');
        }
        if (!Schema::hasColumn('obe_pengelola', 'tanda_tangan')) {
            $table->string('tanda_tangan')->nullable()->after('bidang');
        }
    });
}

public function down(): void
{
    Schema::table('obe_pengelola', function (Blueprint $table) {
        if (Schema::hasColumn('obe_pengelola', 'bidang')) {
            $table->dropColumn('bidang');
        }
        if (Schema::hasColumn('obe_pengelola', 'tanda_tangan')) {
            $table->dropColumn('tanda_tangan');
        }
    });
}
};
