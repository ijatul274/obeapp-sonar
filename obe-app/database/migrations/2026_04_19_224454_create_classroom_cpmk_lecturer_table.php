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
        Schema::create('classroom_cpmk_lecturer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpmk_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
            $table->bigInteger('lecturer_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreign('lecturer_id')->references('id')->on('users')->nullOnDelete();
        });
        
        Schema::dropIfExists('classroom_cpmk_lecturer');
    }
};
