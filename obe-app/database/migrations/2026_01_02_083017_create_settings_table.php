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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label'); // Nama yang mudah dibaca, misal: "Judul Halaman Login"
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, image, dll
            $table->timestamps();
        });

        // Insert default data
        DB::table('settings')->insert([
            [
                'key' => 'app_name', 
                'label' => 'Nama Aplikasi', 
                'value' => 'Sistem OBE', 
                'type' => 'text',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'login_title', 
                'label' => 'Judul Halaman Login', 
                'value' => 'Aplikasi Pengelolaan Nilai Kurikulum OBE', 
                'type' => 'text',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'login_description', 
                'label' => 'Deskripsi Halaman Login', 
                'value' => 'Silakan masuk untuk melanjutkan ke sistem.', 
                'type' => 'textarea',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'footer_text', 
                'label' => 'Teks Footer', 
                'value' => 'Program Studi Teknik Informatika UNRI 2025', 
                'type' => 'text',
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
