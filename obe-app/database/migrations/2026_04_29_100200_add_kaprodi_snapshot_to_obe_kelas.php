<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_kelas', function (Blueprint $table) {
            $table->string('kaprodi_snapshot')->nullable()->after('is_archived');
            $table->timestamp('archived_at')->nullable()->after('kaprodi_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('obe_kelas', function (Blueprint $table) {
            $table->dropColumn(['kaprodi_snapshot', 'archived_at']);
        });
    }
};
