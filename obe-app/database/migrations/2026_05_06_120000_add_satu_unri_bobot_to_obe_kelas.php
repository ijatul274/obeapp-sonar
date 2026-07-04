<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_kelas', function (Blueprint $table) {
            $table->json('satu_unri_bobot')->nullable()->after('archived_at');
        });
    }

    public function down(): void
    {
        Schema::table('obe_kelas', function (Blueprint $table) {
            $table->dropColumn('satu_unri_bobot');
        });
    }
};
