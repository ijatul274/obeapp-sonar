<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obe_program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_prodi');
            $table->text('visi')->nullable();
            $table->timestamps();
        });

        Schema::create('obe_jurusan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jurusan');
            $table->foreignId('id_prodi')
                ->nullable()
                ->constrained('obe_program_studi')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obe_jurusan');
        Schema::dropIfExists('obe_program_studi');
    }
};
