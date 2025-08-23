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
            ['name' => 'Registration', 'code' => 'REG', 'room' => 'Room 1'],
            ['name' => 'MSS', 'code' => 'MSS', 'room' => 'Room 1'],
            ['name' => 'Philhealth', 'code' => 'PHIC', 'room' => 'Room 1'],
            ['name' => 'Billing', 'code' => 'BILL', 'room' => 'Room 3'],
            ['name' => 'Animal Bite - 1', 'code' => 'AB1', 'room' => 'Room 8', 'description' => '(First Injection)'],
            ['name' => 'Animal Bite - 2', 'code' => 'AB2', 'room' => 'Room 8', 'description' => '(Follow-up Injection - 2nd or Later)'],
            ['name' => 'TB DOTS', 'code' => 'AA', 'room' => 'Room 1'],
            ['name' => 'Dental', 'code' => 'DEN', 'room' => 'Room 1'],
            ['name' => 'Psych', 'code' => 'PS', 'room' => 'Room 1', 'description' => '(Psychology/Mental Health Room)'],
            ['name' => 'Neurology', 'code' => 'NEU', 'room' => 'Room 1'],
            ['name' => 'Endocrinology', 'code' => 'EN', 'room' => 'Room 1'],
            ['name' => 'Int. Medicine', 'code' => 'INT', 'room' => 'Room 1'],
            ['name' => 'OB-Gyne', 'code' => 'OB', 'room' => 'Room 2'],
            ['name' => 'Pedia', 'code' => 'PED', 'room' => 'Room 2'],
            ['name' => 'General Consultation', 'code' => 'GEN', 'room' => 'Room 2'],
            ['name' => 'Surgery', 'code' => 'SUR', 'room' => 'Room 2'],
            ['name' => 'ENT', 'code' => 'ENT', 'room' => 'Room 2'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
