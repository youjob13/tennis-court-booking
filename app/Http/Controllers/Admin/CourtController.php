<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourtController extends Controller
{
    /**
     * Display a listing of all courts.
     */
    public function index(): View
    {
        $courts = Court::withCount(['bookings' => function ($query) {
            $query->where('status', 'confirmed')
                ->where('start_datetime', '>', now());
        }])->orderBy('created_at', 'desc')->get();

        return view('admin.courts.index', compact('courts'));
    }

    /**
     * Show the form for creating a new court.
     */
    public function create(): View
    {
        return view('admin.courts.create');
    }

    /**
     * Store a newly created court in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photo_url' => ['nullable', 'url', 'max:500'],
            'hourly_price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'operating_hours_start' => ['required', 'date_format:H:i'],
            'operating_hours_end' => ['required', 'date_format:H:i', 'after:operating_hours_start'],
        ]);

        Court::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'photo_url' => $validated['photo_url'],
            'hourly_price' => $validated['hourly_price'],
            'status' => 'active',
            'operating_hours' => [
                'start' => $validated['operating_hours_start'],
                'end' => $validated['operating_hours_end'],
            ],
        ]);

        return redirect()->route('admin.courts.index')
            ->with('success', 'Court created successfully.');
    }

    /**
     * Disable a court.
     */
    public function disable(Court $court): RedirectResponse
    {
        $court->update(['status' => 'disabled']);

        return redirect()->route('admin.courts.index')
            ->with('success', 'Court disabled successfully.');
    }

    /**
     * Enable a court.
     */
    public function enable(Court $court): RedirectResponse
    {
        $court->update(['status' => 'active']);

        return redirect()->route('admin.courts.index')
            ->with('success', 'Court enabled successfully.');
    }

    /**
     * Remove the specified court from storage.
     */
    public function destroy(Court $court): RedirectResponse
    {
        // Prevent deletion if court has future confirmed bookings
        $futureBookings = $court->bookings()
            ->where('status', 'confirmed')
            ->where('start_datetime', '>', now())
            ->count();

        if ($futureBookings > 0) {
            return redirect()->route('admin.courts.index')
                ->withErrors(['error' => 'Cannot delete court with future bookings. Please cancel all future bookings first.']);
        }

        $court->delete();

        return redirect()->route('admin.courts.index')
            ->with('success', 'Court deleted successfully.');
    }
}
