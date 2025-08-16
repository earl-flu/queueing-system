<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
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

        // $this->call([
        //     UserSeeder::class,
        //     OfficeSeeder::class,
        //     TagSeeder::class
        // ]);
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@opd.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create departments
        $departments = [
            ['name' => 'Dental', 'code' => 'DEN', 'room' => 'Room 1'],
            ['name' => 'Obstetrics', 'code' => 'OB', 'room' => 'Room 2'],
            ['name' => 'Billing', 'code' => 'BILL', 'room' => 'Room 3'],
            ['name' => 'Laboratory', 'code' => 'LAB', 'room' => 'Room 4'],
            ['name' => 'Radiology', 'code' => 'RAD', 'room' => 'Room 5'],
            ['name' => 'Pharmacy', 'code' => 'PHAR', 'room' => 'Room 6'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create staff users
        $staffUsers = [
            ['name' => 'Dental Staff', 'email' => 'dental@opd.com', 'role' => 'staff'],
            ['name' => 'OB Staff', 'email' => 'ob@opd.com', 'role' => 'staff'],
            ['name' => 'Billing Staff', 'email' => 'billing@opd.com', 'role' => 'staff'],
            ['name' => 'Lab Staff', 'email' => 'lab@opd.com', 'role' => 'staff'],
            ['name' => 'Reception Staff', 'email' => 'reception@opd.com', 'role' => 'reception'],
        ];

        foreach ($staffUsers as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'role' => $userData['role'],
                'email_verified_at' => now(),
            ]);
        }

        // Assign staff to departments
        $dentalDept = Department::where('code', 'DEN')->first();
        $obDept = Department::where('code', 'OB')->first();
        $billingDept = Department::where('code', 'BILL')->first();
        $labDept = Department::where('code', 'LAB')->first();

        $dentalStaff = User::where('email', 'dental@opd.com')->first();
        $obStaff = User::where('email', 'ob@opd.com')->first();
        $billingStaff = User::where('email', 'billing@opd.com')->first();
        $labStaff = User::where('email', 'lab@opd.com')->first();
        $reception = User::where('email', 'reception@opd.com')->first();

        // Assign staff to their respective departments
        $dentalDept->users()->attach($dentalStaff->id);
        $obDept->users()->attach($obStaff->id);
        $billingDept->users()->attach($billingStaff->id);
        $labDept->users()->attach($labStaff->id);

        // Reception can access all departments
        $reception->departments()->attach([
            $dentalDept->id,
            $obDept->id,
            $billingDept->id,
            $labDept->id
        ]);
    }
}
