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
            ['code' => 'A'], // Animal Bite 1
            ['code' => 'F'], // Animal Bite 2
            ['code' => 'Q'], // TB DOTS
            ['code' => 'D'], // Dental
            ['code' => 'C'], // Psych
            ['code' => 'N'], // Neuro
            ['code' => 'E'], // Endo
            ['code' => 'I'], // Int. Medicine
            ['code' => 'O'], // Ob-Gyne
            ['code' => 'P'], // Pedia
            ['code' => 'G'], // Gen. Consult.
            ['code' => 'S'], // Surgery
            ['code' => 'H'], // ENT
        ];

        //for each and get the id of the department
        $steps = [
            ['code' => 'VIT', 'step_order' => 1],
            ['code' => 'REG', 'step_order' => 2],
            ['code' => 'MSS', 'step_order' => 3],
            ['code' => 'PHIC', 'step_order' => 4],
            ['code' => 'BIL', 'step_order' => 6],
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
                'step_order' => 5,
                'is_required' => true
            ]);
        }
    }
}
