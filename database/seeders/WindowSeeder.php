<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Window;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WindowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Windows
        $window1 = Window::create(['name' => 'Window 1', 'slug' => 'window-1']);
        $window2 = Window::create(['name' => 'Window 2', 'slug' => 'window-2']);

        $window1Displays = [
            ['dept_code' => 'REG', 'position' => 0],
            ['dept_code' => 'MSS', 'position' => 1],
            ['dept_code' => 'PHIC', 'position' => 2],
        ];

        $syncDataWindow1 = [];  //result will be  // [ 1 => [ position: 0 ], 2 => [ position: 1 ]]

        foreach ($window1Displays as $display) {
            $department = Department::where('code', $display['dept_code'])->first();
            if ($department) {
                $syncDataWindow1[$department->id] = ['position' => $display['position']];
            }
        }

        $window1->departments()->sync($syncDataWindow1);

        $window2Displays = [
            ['dept_code' => 'C', 'position' => 0],
            ['dept_code' => 'D', 'position' => 1],
            ['dept_code' => 'A', 'position' => 2],
        ];

        $syncDataWindow2 = [];

        foreach ($window2Displays as $display) {
            $department = Department::where('code', $display['dept_code'])->first();
            if ($department) {
                $syncDataWindow2[$department->id] = ['position' => $display['position']];
            }
        }

        $window2->departments()->sync($syncDataWindow2);
    }
}
