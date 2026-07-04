<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obe_pengelola', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('obe_pengguna')
                ->cascadeOnDelete();
            $table->string('jabatan'); // mis. kaprodi, kajur, pj_lab, koordinator
            $table->string('keterangan')->nullable();
            $table->date('mulai_menjabat')->nullable();
            $table->date('selesai_menjabat')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'jabatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obe_pengelola');
    }
};
