<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Therapist;
use App\Models\TherapistBooking;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TherapistsController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }
    /**
     * Display a listing of therapists.
     */
    public function index()
    {
        $therapists = Therapist::withCount(['bookings', 'pendingBookings', 'confirmedBookings'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Therapists/Index', [
            'therapists' => $therapists,
        ]);
    }

    /**
     * Show the form for creating a new therapist.
     */
    public function create()
    {
        $specializations = [
            'Depression', 'Anxiety', 'Relationships', 'Trauma', 'Addiction', 
            'Family Therapy', 'Couples Therapy', 'Child Psychology', 'ADHD',
            'Eating Disorders', 'Grief Counseling', 'Stress Management'
        ];

        return Inertia::render('Admin/Therapists/Create', [
            'specializations' => $specializations,
        ]);
    }

    /**
     * Store a newly created therapist.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'specializations' => 'required|array|min:1',
            'specializations.*' => 'string',
            'degree' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'availability' => 'required|array',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'languages' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'additional_info' => 'nullable|string',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('therapists', 'public');
        }

        Therapist::create($validated);

        // Clear therapist cache for AI chatbot
        $this->openAIService->clearTherapistCache();

        return redirect()->route('admin.therapists.index')->with('success', 'Therapist created successfully');
    }

    /**
     * Display the specified therapist.
     */
    public function show($id)
    {
        $therapist = Therapist::with(['bookings.user'])->findOrFail($id);

        return Inertia::render('Admin/Therapists/Show', [
            'therapist' => $therapist,
        ]);
    }

    /**
     * Show the form for editing the specified therapist.
     */
    public function edit($id)
    {
        $therapist = Therapist::findOrFail($id);
        
        $specializations = [
            'Depression', 'Anxiety', 'Relationships', 'Trauma', 'Addiction', 
            'Family Therapy', 'Couples Therapy', 'Child Psychology', 'ADHD',
            'Eating Disorders', 'Grief Counseling', 'Stress Management'
        ];

        return Inertia::render('Admin/Therapists/Edit', [
            'therapist' => $therapist,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Update the specified therapist.
     */
    public function update(Request $request, $id)
    {
        $therapist = Therapist::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'specializations' => 'required|array|min:1',
            'specializations.*' => 'string',
            'degree' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'availability' => 'required|array',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'languages' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'additional_info' => 'nullable|string',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($therapist->photo) {
                Storage::disk('public')->delete($therapist->photo);
            }
            $validated['photo'] = $request->file('photo')->store('therapists', 'public');
        }

        $therapist->update($validated);

        // Clear therapist cache for AI chatbot
        $this->openAIService->clearTherapistCache();

        return redirect()->route('admin.therapists.index')->with('success', 'Therapist updated successfully');
    }

    /**
     * Remove the specified therapist.
     */
    public function destroy($id)
    {
        $therapist = Therapist::findOrFail($id);

        // Delete photo
        if ($therapist->photo) {
            Storage::disk('public')->delete($therapist->photo);
        }

        $therapist->delete();

        // Clear therapist cache for AI chatbot
        $this->openAIService->clearTherapistCache();

        return redirect()->route('admin.therapists.index')->with('success', 'Therapist deleted successfully');
    }

    /**
     * Display therapist bookings.
     */
    public function bookings()
    {
        $bookings = TherapistBooking::with(['user', 'therapist'])
            ->orderBy('status', 'asc')
            ->orderBy('appointment_datetime', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Therapists/Bookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Update booking status.
     */
    public function updateBooking(Request $request, $bookingId)
    {
        $booking = TherapistBooking::findOrFail($bookingId);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'admin_notes' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'cancellation_reason' => 'nullable|string',
        ]);

        // Set timestamps based on status
        if ($validated['status'] === 'confirmed' && $booking->status !== 'confirmed') {
            $validated['confirmed_at'] = now();
        } elseif ($validated['status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $validated['cancelled_at'] = now();
        } elseif ($validated['status'] === 'completed' && $booking->status !== 'completed') {
            $validated['completed_at'] = now();
        }

        $booking->update($validated);

        return redirect()->back()->with('success', 'Booking updated successfully');
    }
}
