<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_cpmk_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classroom_cpmk_assessment_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('score', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('classroom_cpmk_assessment_id', 'ccas_assessment_fk')
                  ->references('id')->on('classroom_cpmk_assessments')
                  ->cascadeOnDelete();
            $table->foreign('student_id', 'ccas_student_fk')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();

            $table->unique(['classroom_cpmk_assessment_id', 'student_id'], 'ccas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_cpmk_assessment_scores');
    }
};
