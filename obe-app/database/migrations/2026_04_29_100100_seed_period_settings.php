<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $rows = [
        ['key' => 'period_ganjil_start', 'label' => 'Mulai Semester Ganjil (MM-DD)', 'value' => '08-01', 'type' => 'string'],
        ['key' => 'period_ganjil_end',   'label' => 'Selesai Semester Ganjil (MM-DD)', 'value' => '01-31', 'type' => 'string'],
        ['key' => 'period_genap_start',  'label' => 'Mulai Semester Genap (MM-DD)', 'value' => '02-01', 'type' => 'string'],
        ['key' => 'period_genap_end',    'label' => 'Selesai Semester Genap (MM-DD)', 'value' => '07-31', 'type' => 'string'],
    ];

    public function up(): void
    {
        $now = now();
        foreach ($this->rows as $row) {
            DB::table('obe_pengaturan')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down(): void
    {
        DB::table('obe_pengaturan')->whereIn('key', array_column($this->rows, 'key'))->delete();
    }
};
