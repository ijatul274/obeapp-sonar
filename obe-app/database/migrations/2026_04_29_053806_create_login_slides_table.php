<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');             // path di storage/app/public, mis: login-slides/abc.jpg
            $table->string('title')->nullable();
            $table->string('caption', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_slides');
    }
};
