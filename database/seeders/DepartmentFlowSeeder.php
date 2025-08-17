<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\DepartmentFlow;

class DepartmentFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create departments
        $registration = Department::firstOrCreate(['name' => 'Registration'], [
            'code' => 'REG',
            'is_active' => true
        ]);

        $mss = Department::firstOrCreate(['name' => 'MSS'], [
            'code' => 'MSS',
            'is_active' => true
        ]);

        $billing = Department::firstOrCreate(['name' => 'Billing'], [
            'code' => 'BIL',
            'is_active' => true
        ]);

        $dental = Department::firstOrCreate(['name' => 'Dental'], [
            'code' => 'DEN',
            'is_active' => true
        ]);

        $obstetrics = Department::firstOrCreate(['name' => 'Obstetrics'], [
            'code' => 'OB',
            'is_active' => true
        ]);

        // Clear existing flows
        DepartmentFlow::truncate();

        // Dental Department Flow: Registration -> MSS -> Billing -> Dental
        DepartmentFlow::create([
            'final_department_id' => $dental->id,
            'step_department_id' => $registration->id,
            'step_order' => 1,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $dental->id,
            'step_department_id' => $mss->id,
            'step_order' => 2,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $dental->id,
            'step_department_id' => $billing->id,
            'step_order' => 3,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $dental->id,
            'step_department_id' => $dental->id,
            'step_order' => 4,
            'is_required' => true
        ]);

        // Obstetrics Department Flow: Registration -> MSS -> Billing -> Obstetrics
        DepartmentFlow::create([
            'final_department_id' => $obstetrics->id,
            'step_department_id' => $registration->id,
            'step_order' => 1,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $obstetrics->id,
            'step_department_id' => $mss->id,
            'step_order' => 2,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $obstetrics->id,
            'step_department_id' => $billing->id,
            'step_order' => 3,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $obstetrics->id,
            'step_department_id' => $obstetrics->id,
            'step_order' => 4,
            'is_required' => true
        ]);

        // Registration Department Flow: Direct (no intermediate steps)
        DepartmentFlow::create([
            'final_department_id' => $registration->id,
            'step_department_id' => $registration->id,
            'step_order' => 1,
            'is_required' => true
        ]);

        // MSS Department Flow: Registration -> MSS
        DepartmentFlow::create([
            'final_department_id' => $mss->id,
            'step_department_id' => $registration->id,
            'step_order' => 1,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $mss->id,
            'step_department_id' => $mss->id,
            'step_order' => 2,
            'is_required' => true
        ]);

        // Billing Department Flow: Registration -> MSS -> Billing
        DepartmentFlow::create([
            'final_department_id' => $billing->id,
            'step_department_id' => $registration->id,
            'step_order' => 1,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $billing->id,
            'step_department_id' => $mss->id,
            'step_order' => 2,
            'is_required' => true
        ]);

        DepartmentFlow::create([
            'final_department_id' => $billing->id,
            'step_department_id' => $billing->id,
            'step_order' => 3,
            'is_required' => true
        ]);
    }
}
