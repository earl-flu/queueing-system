<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except(['index']);
    }

    public function index()
    {
        $departments = Department::with(['users', 'currentQueueItems', 'activeQueue' => function ($query) {
            $query->today()->whereIn('status', ['waiting', 'serving']);
        }])->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments
        ]);
    }

    public function create()
    {
        $users = User::where('role', 'staff')->get();

        return Inertia::render('Departments/Create', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'required|string|max:10|unique:departments',
            'slug' => 'required|string|max:50|unique:departments',
            'room' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'users' => 'array',
            'users.*' => 'exists:users,id'
        ]);

        $department = Department::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'slug' => $validated['slug'],
            'room' => $validated['room'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'] ?? true
        ]);

        if (isset($validated['users'])) {
            $department->users()->attach($validated['users']);
        }

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $users = User::orderBy('name')->get();
        $department->load('users');

        return Inertia::render('Departments/Edit', [
            'department' => $department,
            'users' => $users
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'slug' => 'required|string|max:50|unique:departments,slug,' . $department->id,
            'room' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'users' => 'array',
            'users.*' => 'exists:users,id'
        ]);

        $department->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'slug' => $validated['slug'],
            'room' => $validated['room'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'] ?? true
        ]);

        if (isset($validated['users'])) {
            $department->users()->sync($validated['users']);
        }

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
