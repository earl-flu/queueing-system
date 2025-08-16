<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('tags')->insert([
            [
                'name' => 'firstTag', 'created_at' => $now, 'updated_at' => $now
            ], [
                'name' => 'secondTag', 'created_at' => $now, 'updated_at' => $now
            ], [
                'name' => 'AOP2023', 'created_at' => $now, 'updated_at' => $now
            ], [
                'name' => 'AOP2024', 'created_at' => $now, 'updated_at' => $now
            ]
        ]);
    }
}
