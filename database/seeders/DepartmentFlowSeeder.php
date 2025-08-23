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
        //Get all the list of Department where code = 
        $departments = [
            ['code' => 'AB1'], // Animal Bite 1
            ['code' => 'AB2'], // Animal Bite 2
            ['code' => 'AA'], // TB DOTS
            ['code' => 'DEN'], // Dental
            ['code' => 'PS'], // Psych
            ['code' => 'NEU'], // Neuro
            ['code' => 'EN'], // Endo
            ['code' => 'INT'], // Int. Medicine
            ['code' => 'OB'], // Ob-Gyne
            ['code' => 'PED'], // Pedia
            ['code' => 'GEN'], // Gen. Consult.
            ['code' => 'SUR'], // Surgery
            ['code' => 'ENT'], // ENT
        ];

        //for each and get the id of the department
        $steps = [
            ['code' => 'REG', 'step_order' => 1],
            ['code' => 'PHIC', 'step_order' => 2],
            ['code' => 'MSS', 'step_order' => 3],
        ];

        // Fetch all departments once and map by code
        $departmentsMap = Department::pluck('id', 'code');

        foreach ($departments as $department) {
            $departmentId = $departmentsMap[$department['code']] ?? null;
            if (!$departmentId) continue; // skip if not found

            foreach ($steps as $step) {
                $stepDeptId = $departmentsMap[$step['code']] ?? null;
                DepartmentFlow::create([
                    'final_department_id' => $departmentId,
                    'step_department_id' => $stepDeptId,
                    'step_order' => $step['step_order'],
                    'is_required' => true
                ]);
            }
            DepartmentFlow::create([
                'final_department_id' => $departmentId,
                'step_department_id' => $departmentId,
                'step_order' => 4,
                'is_required' => true
            ]);
        }
    }
}
