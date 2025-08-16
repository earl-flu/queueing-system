<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Paper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaperDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Papers/Dashboard', []);
    }

    public function getData(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Validate date inputs
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $papers = Paper::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->when($startDate, function ($query, $startDate) {
                    return $query->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($query, $endDate) {
                    return $query->whereDate('created_at', '<=', $endDate);
                })
                ->get();

            return response()->json($papers);
        } catch (\Exception $e) {
            // Log the exception message
            \Log::error('Error in getData method: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getOfficeData(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $data = Office::withCount(['papers' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        // Filter, map, and sort the data
        $result = $data->filter(function ($office) {
            return $office->papers_count > 0;
        })->map(function ($office) {
            return [
                'name' => $office->name,
                'count' => $office->papers_count,
            ];
        })->sortByDesc('count')->values()->all();

        return response()->json($result);
    }
}
