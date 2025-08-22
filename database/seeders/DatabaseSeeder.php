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
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@opd.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create departments
        $departments = [
            ['name' => 'Registration', 'code' => 'REG', 'room' => 'Room 1'],
            ['name' => 'MSS', 'code' => 'MSS', 'room' => 'Room 1'],
            ['name' => 'Philhealth', 'code' => 'PHIC', 'room' => 'Room 1'],
            ['name' => 'Billing', 'code' => 'BILL', 'room' => 'Room 3'],
            ['name' => 'Animal Bite - 1', 'code' => 'AB1', 'room' => 'Room 8', 'description' => 'Animal Bite First Injection'],
            ['name' => 'Animal Bite - 2', 'code' => 'AB2', 'room' => 'Room 8', 'description' => 'Animal Bite – Follow-up Injection (2nd or Later)'],
            ['name' => 'TB DOTS', 'code' => 'AA', 'room' => 'Room 1'],
            ['name' => 'Dental', 'code' => 'DEN', 'room' => 'Room 1'],
            ['name' => 'Psych', 'code' => 'PS', 'room' => 'Room 1', 'description' => 'Psychology/Mental Health Room'],
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

        // Create staff users with department code mapping
        $staffUsers = [
            // Administrative Staff
            ['name' => 'Reception Staff', 'email' => 'reception@opd.com', 'role' => 'reception', 'dept_code' => 'REG'],
            ['name' => 'MSS Staff', 'email' => 'mss@opd.com', 'role' => 'staff', 'dept_code' => 'MSS'],
            ['name' => 'Philhealth Staff', 'email' => 'phic@opd.com', 'role' => 'staff', 'dept_code' => 'PHIC'],
            ['name' => 'Billing Staff', 'email' => 'billing@opd.com', 'role' => 'staff', 'dept_code' => 'BILL'],

            // Medical Staff
            ['name' => 'Animal Bite1 Staff', 'email' => 'abtc1@opd.com', 'role' => 'staff', 'dept_code' => 'AB1'],
            ['name' => 'Animal Bite2 Staff', 'email' => 'abtc2@opd.com', 'role' => 'staff', 'dept_code' => 'AB2'],
            ['name' => 'TB DOTS Staff', 'email' => 'tbdots@opd.com', 'role' => 'staff', 'dept_code' => 'AA'],
            ['name' => 'Dental Staff', 'email' => 'dental@opd.com', 'role' => 'staff', 'dept_code' => 'DEN'],
            ['name' => 'Psychology Staff', 'email' => 'psych@opd.com', 'role' => 'staff', 'dept_code' => 'PS'],
            ['name' => 'Neurology Staff', 'email' => 'neuro@opd.com', 'role' => 'staff', 'dept_code' => 'NEU'],
            ['name' => 'Endocrinology Staff', 'email' => 'endo@opd.com', 'role' => 'staff', 'dept_code' => 'EN'],
            ['name' => 'Internal Medicine Staff', 'email' => 'intmed@opd.com', 'role' => 'staff', 'dept_code' => 'INT'],
            ['name' => 'OB-Gyne Staff', 'email' => 'ob@opd.com', 'role' => 'staff', 'dept_code' => 'OB'],
            ['name' => 'Pediatric Staff', 'email' => 'pedia@opd.com', 'role' => 'staff', 'dept_code' => 'PED'],
            ['name' => 'General Consultation Staff', 'email' => 'genconsultation@opd.com', 'role' => 'staff', 'dept_code' => 'GEN'],
            ['name' => 'Surgery Staff', 'email' => 'surgery@opd.com', 'role' => 'staff', 'dept_code' => 'SUR'],
            ['name' => 'ENT Staff', 'email' => 'ent@opd.com', 'role' => 'staff', 'dept_code' => 'ENT'],
        ];

        // Create users and store their department assignments
        $staffDepartmentAssignments = [];

        foreach ($staffUsers as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'role' => $userData['role'],
                'email_verified_at' => now(),
            ]);

            // Store assignment for later processing
            if (isset($userData['dept_code'])) {
                $staffDepartmentAssignments[] = [
                    'user_id' => $user->id,
                    'dept_code' => $userData['dept_code']
                ];
            }
        }
        // dd($staffDepartmentAssignments);
        /**
         * [[user_id => 1, dep_code = REG], [user_id => 2, dep_code = OB]]
         */
        // Call the DepartmentFlowSeeder
        $this->call([
            // DepartmentFlowSeeder::class,
            PriorityReasonsTableSeeder::class,
        ]);

        // Assign all staff to their respective departments
        foreach ($staffDepartmentAssignments as $assignment) {
            $department = Department::where('code', $assignment['dept_code'])->first();

            if ($department) {
                $department->users()->attach($assignment['user_id']);

                // Optional: Log the assignment for debugging
                // \Log::info("Assigned user {$assignment['user_id']} to department {$assignment['dept_code']}");
            } else {
                // Log warning if department not found
                \Log::warning("Department with code {$assignment['dept_code']} not found for user assignment");
            }
        }
    }
}
