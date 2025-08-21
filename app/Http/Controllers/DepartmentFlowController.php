<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentFlow;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentFlowController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except(['index']);
    }

    public function index()
    {
        $flows = DepartmentFlow::with(['finalDepartment', 'stepDepartment'])
            ->orderBy('final_department_id')
            ->orderBy('step_order')
            ->get();

        return Inertia::render('DepartmentFlows/Index', [
            'flows' => $flows,
        ]);
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return Inertia::render('DepartmentFlows/Create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'final_department_id' => 'required|exists:departments,id',
            'step_department_id' => 'required|different:final_department_id|exists:departments,id',
            'step_order' => 'required|integer|min:1',
            'is_required' => 'boolean',
        ]);

        // Enforce unique per migration constraint at app level for a nicer message
        $request->validate([
            // Unique pair of final_department_id and step_department_id
            'step_department_id' => 'unique:department_flows,step_department_id,NULL,id,final_department_id,' . $validated['final_department_id'],
        ], [
            'step_department_id.unique' => 'This step department is already part of the selected final department\'s flow.',
        ]);

        DepartmentFlow::create([
            'final_department_id' => $validated['final_department_id'],
            'step_department_id' => $validated['step_department_id'],
            'step_order' => $validated['step_order'],
            'is_required' => $validated['is_required'] ?? true,
        ]);

        return redirect()->route('department-flows.index')
            ->with('success', 'Department flow created successfully.');
    }

    public function edit(DepartmentFlow $department_flow)
    {
        $department_flow->load(['finalDepartment', 'stepDepartment']);
        $departments = Department::orderBy('name')->get();

        return Inertia::render('DepartmentFlows/Edit', [
            'flow' => $department_flow,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, DepartmentFlow $department_flow)
    {
        $validated = $request->validate([
            'final_department_id' => 'required|exists:departments,id',
            // 'step_department_id' => 'required|different:final_department_id|exists:departments,id',
            'step_department_id' => 'required|exists:departments,id',
            'step_order' => 'required|integer|min:1',
            'is_required' => 'boolean',
        ]);

        // Unique composite validation ignoring current record
        $request->validate([
            'step_department_id' => 'unique:department_flows,step_department_id,' . $department_flow->id . ',id,final_department_id,' . $validated['final_department_id'],
        ], [
            'step_department_id.unique' => 'This step department is already part of the selected final department\'s flow.',
        ]);

        $department_flow->update([
            'final_department_id' => $validated['final_department_id'],
            'step_department_id' => $validated['step_department_id'],
            'step_order' => $validated['step_order'],
            'is_required' => $validated['is_required'] ?? true,
        ]);

        return redirect()->route('department-flows.index')
            ->with('success', 'Department flow updated successfully.');
    }

    public function destroy(DepartmentFlow $department_flow)
    {
        $department_flow->delete();

        return redirect()->route('department-flows.index')
            ->with('success', 'Department flow deleted successfully.');
    }
}
