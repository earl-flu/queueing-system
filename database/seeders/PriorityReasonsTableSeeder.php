<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriorityReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('priority_reasons')->insert(
            [
                [
                    'description' => 'Cough for more than 2 weeks',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'description' => 'PWD',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'description' => 'Senior Citizen',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'description' => 'Pregnant Women',
                    'created_at' => now(),
                    'updated_at' => now(),

                ],
                [
                    'description' => 'Fever',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]
        );
    }
}
