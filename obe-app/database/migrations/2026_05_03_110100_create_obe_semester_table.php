<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obe_semester', function (Blueprint $table) {
            $table->id();
            $table->enum('periode', ['ganjil', 'genap']);
            $table->string('tahun_ajaran', 9); // mis. "2025/2026"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamps();

            $table->unique(['periode', 'tahun_ajaran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obe_semester');
    }
};
