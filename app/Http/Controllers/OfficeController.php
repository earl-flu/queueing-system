<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as FacadesRequest;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offices = Office::orderBy('name')
            ->when(FacadesRequest::input('name'), function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->paginate(5)
            ->withQueryString()
            ->onEachSide(0);

        return Inertia::render('Offices/Index', [
            'offices' => $offices,
            'filters' => FacadesRequest::only('name')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Offices/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:offices,name',
        ]);

        Office::create($validated);
        return redirect()->route('offices.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        return Inertia::render('Offices/Edit', ['office' => $office]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        $office->update($validated);

        return redirect()->route('offices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }
}
