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
            $table->unsignedTinyInteger('meeting_start')->nullable()->after('percentage');
            $table->unsignedTinyInteger('meeting_end')->nullable()->after('meeting_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cpmks', function (Blueprint $table) {
            $table->dropColumn(['meeting_start', 'meeting_end']);
        });
    }
};
