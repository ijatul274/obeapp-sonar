<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_cpmk_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_cpmk_id')
                  ->constrained('classroom_cpmks')
                  ->cascadeOnDelete();
            $table->text('description');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_cpmk_indicators');
    }
};
