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
            $table->decimal('percentage', 5, 2)->default(0)->after('description');
        });

        Schema::table('indicators', function (Blueprint $table) {
            $table->decimal('percentage', 5, 2)->default(0)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cpmks', function (Blueprint $table) {
            $table->dropColumn('percentage');
        });

        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn('percentage');
        });
    }
};
