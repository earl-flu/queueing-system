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
            ['name' => 'Vitals', 'slug' => 'vitals', 'code' => 'VIT', 'room' => ''],
            ['name' => 'Registration', 'slug' => 'registration', 'code' => 'REG', 'room' => ''],
            ['name' => 'MSS', 'slug' => 'mss', 'code' => 'MSS', 'room' => ''],
            ['name' => 'Philhealth', 'slug' => 'philhealth', 'code' => 'PHIC', 'room' => ''],
            ['name' => 'Billing', 'slug' => 'billing', 'code' => 'BIL', 'room' => ''],
            ['name' => 'Animal Bite - 1', 'slug' => 'animalbite1', 'code' => 'A', 'room' => 'Room 8', 'description' => '(First Injection)'],
            ['name' => 'Animal Bite - 2', 'slug' => 'animalbite2', 'code' => 'F', 'room' => 'Room 8', 'description' => '(Follow-up Injection - 2nd or Later)'],
            ['name' => 'TB DOTS', 'slug' => 'tbdots', 'code' => 'Q', 'room' => 'Room 1'],
            ['name' => 'Dental', 'slug' => 'dental', 'code' => 'D', 'room' => 'Room 1'],
            ['name' => 'Psych', 'slug' => 'psych', 'code' => 'C', 'room' => 'Room 1', 'description' => '(Psychology/Mental Health Room)'],
            ['name' => 'Neurology', 'slug' => 'neurology', 'code' => 'N', 'room' => 'Room 1'],
            ['name' => 'Endocrinology', 'slug' => 'endocrinology', 'code' => 'E', 'room' => 'Room 1'],
            ['name' => 'Int. Medicine', 'slug' => 'internalmeds', 'code' => 'I', 'room' => 'Room 1'],
            ['name' => 'OB-Gyne', 'slug' => 'obgyne', 'code' => 'O', 'room' => 'Room 2'],
            ['name' => 'Pedia', 'slug' => 'pedia', 'code' => 'P', 'room' => 'Room 2'],
            ['name' => 'General Consultation', 'slug' => 'general', 'code' => 'G', 'room' => 'Room 2'],
            ['name' => 'Surgery', 'slug' => '', 'code' => 'surgery', 'room' => 'Room 2'],
            ['name' => 'ENT', 'slug' => 'ent', 'code' => 'H', 'room' => 'Room 2'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
