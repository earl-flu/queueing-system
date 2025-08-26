<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  // Create departments
        $departments = [
            ['name' => 'Registration', 'code' => 'REG', 'room' => ''],
            ['name' => 'MSS', 'code' => 'MSS', 'room' => ''],
            ['name' => 'Philhealth', 'code' => 'PHIC', 'room' => ''],
            ['name' => 'Billing', 'code' => 'BIL', 'room' => ''],
            ['name' => 'Animal Bite - 1', 'code' => 'A', 'room' => 'Room 8', 'description' => '(First Injection)'],
            ['name' => 'Animal Bite - 2', 'code' => 'F', 'room' => 'Room 8', 'description' => '(Follow-up Injection - 2nd or Later)'],
            ['name' => 'TB DOTS', 'code' => 'Q', 'room' => 'Room 1'],
            ['name' => 'Dental', 'code' => 'D', 'room' => 'Room 1'],
            ['name' => 'Psych', 'code' => 'C', 'room' => 'Room 1', 'description' => '(Psychology/Mental Health Room)'],
            ['name' => 'Neurology', 'code' => 'N', 'room' => 'Room 1'],
            ['name' => 'Endocrinology', 'code' => 'E', 'room' => 'Room 1'],
            ['name' => 'Int. Medicine', 'code' => 'I', 'room' => 'Room 1'],
            ['name' => 'OB-Gyne', 'code' => 'O', 'room' => 'Room 2'],
            ['name' => 'Pedia', 'code' => 'P', 'room' => 'Room 2'],
            ['name' => 'General Consultation', 'code' => 'G', 'room' => 'Room 2'],
            ['name' => 'Surgery', 'code' => 'S', 'room' => 'Room 2'],
            ['name' => 'ENT', 'code' => 'H', 'room' => 'Room 2'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
