<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('indicators')->onDelete('cascade');
            $table->unsignedBigInteger('classroom_id')->nullable(); // FK ditambah setelah classrooms ada
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_auto')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};