<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kolom is_auto sudah digabungkan ke create_assessments_table.
// Migration ini dikosongkan agar tidak error saat migrate ulang.
return new class extends Migration
{
    public function up(): void
    {
        // no-op: is_auto sudah ada di create_assessments_table
    }

    public function down(): void
    {
        // no-op
    }
};