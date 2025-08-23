<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Window;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            UserSeeder::class,
            DepartmentFlowSeeder::class,
            PriorityReasonsTableSeeder::class,
            WindowSeeder::class
        ]);
    }
}
