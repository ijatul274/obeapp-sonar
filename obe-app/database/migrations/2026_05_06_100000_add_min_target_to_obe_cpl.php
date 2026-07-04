<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_cpl', function (Blueprint $table) {
            $table->decimal('min_target', 5, 2)->default(60)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('obe_cpl', function (Blueprint $table) {
            $table->dropColumn('min_target');
        });
    }
};
