<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@opd.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create staff users with department code mapping
        $staffUsers = [
            // Administrative Staff
            ['name' => 'Reception Staff', 'email' => 'reception@opd.com', 'role' => 'reception', 'dept_code' => 'REG'],
            ['name' => 'MSS Staff', 'email' => 'mss@opd.com', 'role' => 'staff', 'dept_code' => 'MSS'],
            ['name' => 'Philhealth Staff', 'email' => 'phic@opd.com', 'role' => 'staff', 'dept_code' => 'PHIC'],
            ['name' => 'Billing Staff', 'email' => 'billing@opd.com', 'role' => 'staff', 'dept_code' => 'BIL'],

            // Medical Staff
            ['name' => 'Animal Bite1 Staff', 'email' => 'abtc1@opd.com', 'role' => 'staff', 'dept_code' => 'A'],
            ['name' => 'Animal Bite2 Staff', 'email' => 'abtc2@opd.com', 'role' => 'staff', 'dept_code' => 'F'],
            ['name' => 'TB DOTS Staff', 'email' => 'tbdots@opd.com', 'role' => 'staff', 'dept_code' => 'Q'],
            ['name' => 'Dental Staff', 'email' => 'dental@opd.com', 'role' => 'staff', 'dept_code' => 'D'],
            ['name' => 'Psychology Staff', 'email' => 'psych@opd.com', 'role' => 'staff', 'dept_code' => 'C'],
            ['name' => 'Neurology Staff', 'email' => 'neuro@opd.com', 'role' => 'staff', 'dept_code' => 'N'],
            ['name' => 'Endocrinology Staff', 'email' => 'endo@opd.com', 'role' => 'staff', 'dept_code' => 'E'],
            ['name' => 'Internal Medicine Staff', 'email' => 'intmed@opd.com', 'role' => 'staff', 'dept_code' => 'I'],
            ['name' => 'OB-Gyne Staff', 'email' => 'ob@opd.com', 'role' => 'staff', 'dept_code' => 'O'],
            ['name' => 'Pediatric Staff', 'email' => 'pedia@opd.com', 'role' => 'staff', 'dept_code' => 'P'],
            ['name' => 'General Consultation Staff', 'email' => 'genconsultation@opd.com', 'role' => 'staff', 'dept_code' => 'G'],
            ['name' => 'Surgery Staff', 'email' => 'surgery@opd.com', 'role' => 'staff', 'dept_code' => 'S'],
            ['name' => 'ENT Staff', 'email' => 'ent@opd.com', 'role' => 'staff', 'dept_code' => 'H'],
            // // Administrative Staff
            // ['name' => 'Reception Staff', 'email' => 'reception@opd.com', 'role' => 'reception', 'dept_code' => 'REG'],
            // ['name' => 'MSS Staff', 'email' => 'mss@opd.com', 'role' => 'staff', 'dept_code' => 'MSS'],
            // ['name' => 'Philhealth Staff', 'email' => 'phic@opd.com', 'role' => 'staff', 'dept_code' => 'PHIC'],
            // ['name' => 'Billing Staff', 'email' => 'billing@opd.com', 'role' => 'staff', 'dept_code' => 'BILL'],

            // // Medical Staff
            // ['name' => 'Animal Bite1 Staff', 'email' => 'abtc1@opd.com', 'role' => 'staff', 'dept_code' => 'AB1'],
            // ['name' => 'Animal Bite2 Staff', 'email' => 'abtc2@opd.com', 'role' => 'staff', 'dept_code' => 'AB2'],
            // ['name' => 'TB DOTS Staff', 'email' => 'tbdots@opd.com', 'role' => 'staff', 'dept_code' => 'AA'],
            // ['name' => 'Dental Staff', 'email' => 'dental@opd.com', 'role' => 'staff', 'dept_code' => 'DEN'],
            // ['name' => 'Psychology Staff', 'email' => 'psych@opd.com', 'role' => 'staff', 'dept_code' => 'PS'],
            // ['name' => 'Neurology Staff', 'email' => 'neuro@opd.com', 'role' => 'staff', 'dept_code' => 'NEU'],
            // ['name' => 'Endocrinology Staff', 'email' => 'endo@opd.com', 'role' => 'staff', 'dept_code' => 'EN'],
            // ['name' => 'Internal Medicine Staff', 'email' => 'intmed@opd.com', 'role' => 'staff', 'dept_code' => 'INT'],
            // ['name' => 'OB-Gyne Staff', 'email' => 'ob@opd.com', 'role' => 'staff', 'dept_code' => 'OB'],
            // ['name' => 'Pediatric Staff', 'email' => 'pedia@opd.com', 'role' => 'staff', 'dept_code' => 'PED'],
            // ['name' => 'General Consultation Staff', 'email' => 'genconsultation@opd.com', 'role' => 'staff', 'dept_code' => 'GEN'],
            // ['name' => 'Surgery Staff', 'email' => 'surgery@opd.com', 'role' => 'staff', 'dept_code' => 'SUR'],
            // ['name' => 'ENT Staff', 'email' => 'ent@opd.com', 'role' => 'staff', 'dept_code' => 'ENT'],
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
