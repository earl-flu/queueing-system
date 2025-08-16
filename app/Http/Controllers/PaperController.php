<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Paper;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Inertia\Inertia;

class PaperController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $papers = Paper::with(['tags' => function ($query) {
            $query->orderBy('name');
        }, 'office'])
            ->when(FacadesRequest::input('title'), function ($query, $title) {
                $query->where('title', 'like', "%{$title}%");
            })
            ->when(FacadesRequest::input('office'), function ($query, $office) {
                $query->whereHas('office', function ($query) use ($office) {
                    $query->where('name', 'like', "%{$office}%");
                });
            })
            ->when(FacadesRequest::input('date'), function ($query, $date) {
                $query->whereDate('created_at', $date);
            })
            ->when(FacadesRequest::input('selectedTags'), function ($query, $selectedTags) {
                $query->whereHas('tags', function ($query) use ($selectedTags) {
                    $query->whereIn('tags.name', $selectedTags);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString()
            ->onEachSide(0);

        $offices = Office::orderBy('name')->pluck('name');
        $tags = Tag::orderBy('name')->pluck('name');

        return Inertia::render('Papers/Index', [
            'papers' => $papers,
            'offices' => $offices,
            'tags' => $tags,
            'flash' => session('success'),
            'filters' => FacadesRequest::only('title', 'office', 'date', 'selectedTags')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tags = Tag::all();
        $offices = Office::all();
        return Inertia::render('Papers/Create', ['tags' => $tags, 'offices' => $offices]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'remarks' => 'nullable',
            'type' => 'required|in:incoming,outgoing',
            'tags' => 'array|exists:tags,id',
            'office_id' => 'required|exists:offices,id'
        ]);

        $paper = Paper::create($request->only('title', 'remarks', 'type', 'office_id'));
        $paper->tags()->sync($request->tags);

        return redirect()->route('papers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Paper $paper)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paper $paper)
    {
        $tags = Tag::all();
        $offices = Office::all();
        $paper->load('tags');

        return Inertia::render('Papers/Edit', [
            'paper' => $paper,
            'tags' => $tags,
            'offices' => $offices,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paper $paper)
    {
        $request->validate([
            'title' => 'required',
            'remarks' => 'required',
            'type' => 'required|in:incoming,outgoing',
            'tags' => 'array|exists:tags,id',
            'office_id' => 'required|exists:offices,id'
        ]);

        $paper->update($request->only('title', 'remarks', 'type', 'office_id'));
        $paper->tags()->sync($request->tags);

        return redirect()->route('papers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paper $paper)
    {
        try {
            $paper->delete();
            // Redirect back with a success message
            return redirect()->route('papers.index')->with('success', 'Paper deleted successfully.');
        } catch (\Exception $e) {
            // Handle errors and redirect with an error message
            return redirect()->route('papers.index')->with('error', 'An error occurred while deleting the paper.');
        }
    }

}
