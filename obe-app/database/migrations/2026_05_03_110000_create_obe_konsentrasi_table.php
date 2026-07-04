<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obe_konsentrasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 150);
            $table->timestamps();
        });

        $now = now();
        DB::table('obe_konsentrasi')->insert([
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak',  'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'KCV', 'nama' => 'Komputasi Cerdas Visual',   'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'KBJ', 'nama' => 'Komputasi Berbasis Jaringan','created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('obe_konsentrasi');
    }
};
