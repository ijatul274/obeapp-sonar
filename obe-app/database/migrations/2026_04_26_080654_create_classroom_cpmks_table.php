<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_cpmks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('code');                          // e.g., CPMK-1
            $table->text('description');
            $table->decimal('percentage', 5, 2)->default(0);// bobot dalam MK (total harus 100)
            $table->decimal('meeting_start', 5, 0)->nullable();
            $table->decimal('meeting_end', 5, 0)->nullable();
            // Status: draft → pending → approved | rejected
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_cpmks');
    }
};
