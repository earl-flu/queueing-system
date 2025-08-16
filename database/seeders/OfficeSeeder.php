<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('offices')->insert([
            [
                'name' => 'BMCH', 'created_at' => $now, 'updated_at' => $now
            ], [
                'name' => 'EBMC', 'created_at' => $now, 'updated_at' => $now
            ], [
                'name' => 'DOH', 'created_at' => $now, 'updated_at' => $now
            ], [
                'name' => 'PDOH', 'created_at' => $now, 'updated_at' => $now
            ]
        ]);
    }
}
