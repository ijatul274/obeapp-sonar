<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_cpmk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_cpmk_indicator_id')
                  ->constrained('classroom_cpmk_indicators')
                  ->cascadeOnDelete();
            $table->string('name');           // e.g., UTS, UAS, Tugas
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_auto')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_cpmk_assessments');
    }
};
