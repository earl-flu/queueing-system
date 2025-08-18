<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\QueueItem;
use App\Models\Window;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WindowController extends Controller
{
    public function index()
    {
        $windows = Window::with(['departments'])->orderBy('id')->get();
        return Inertia::render('Queue/Windows/Index', [
            'windows' => $windows,
        ]);
    }

    public function show(Window $window)
    {
        $window->load('departments');
        $departments = $window->departments;

        $queuesByDepartment = [];
        foreach ($departments as $department) {
            $items = QueueItem::with(['patient'])
                ->today()
                ->where('current_department_id', $department->id)
                ->whereIn('status', ['waiting', 'serving'])
                ->orderBy('status', 'desc')
                ->orderBy('queue_position')
                ->get();

            $queuesByDepartment[$department->id] = $items;
        }

        return Inertia::render('Queue/Windows/Show', [
            'window' => $window,
            'departments' => $departments,
            'queuesByDepartment' => $queuesByDepartment,
        ]);
    }

    public function data(Window $window)
    {
        $window->load('departments');
        $departments = $window->departments;
        $payload = [];
        foreach ($departments as $department) {
            $payload[] = [
                'department' => $department,
                'items' => QueueItem::with(['patient'])
                    ->today()
                    ->where('current_department_id', $department->id)
                    ->whereIn('status', ['waiting', 'serving'])
                    ->orderBy('status', 'desc')
                    ->orderBy('queue_position')
                    ->get(),
            ];
        }
        return response()->json($payload);
    }

    public function edit(Window $window)
    {
        $window->load('departments');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('Queue/Windows/Edit', [
            'window' => $window,
            'allDepartments' => $departments,
            'assigned' => $window->departments->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'position' => $d->pivot->position,
                ];
            }),
        ]);
    }

    public function update(Request $request, Window $window)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'departments' => 'array',
            'departments.*.id' => 'required|exists:departments,id',
            'departments.*.position' => 'required|integer|min:0',
        ]);

        $window->update([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $syncData = [];
        foreach ($validated['departments'] ?? [] as $dept) {
            $syncData[$dept['id']] = ['position' => $dept['position']];
        }
        $window->departments()->sync($syncData);

        return redirect()->route('windows.edit', $window)->with('success', 'Window updated.');
    }
}
