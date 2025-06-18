<?php

namespace App\Http\Controllers;

use App\Models\Therapist;
use App\Models\TherapistBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TherapistBookingController extends Controller
{
    /**
     * Display available therapists.
     */
    public function index()
    {
        $therapists = Therapist::where('status', 'active')
            ->withCount(['bookings', 'confirmedBookings'])
            ->get()
            ->map(function ($therapist) {
                $therapist->photo_url = $therapist->photo_url;
                return $therapist;
            });

        return Inertia::render('Therapists/Index', [
            'therapists' => $therapists,
        ]);
    }

    /**
     * Show therapist details and booking form.
     */
    public function show($id)
    {
        $therapist = Therapist::where('status', 'active')->findOrFail($id);
        $therapist->photo_url = $therapist->photo_url;

        // Get user's existing bookings with this therapist
        $userBookings = TherapistBooking::where('user_id', Auth::id())
            ->where('therapist_id', $id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('therapist')
            ->get();

        return Inertia::render('Therapists/Show', [
            'therapist' => $therapist,
            'userBookings' => $userBookings,
        ]);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'therapist_id' => 'required|exists:therapists,id',
            'appointment_datetime' => 'required|date|after:now',
            'session_type' => 'required|in:online,in_person',
            'user_message' => 'nullable|string|max:1000',
        ]);

        // Check if therapist is active
        $therapist = Therapist::where('id', $validated['therapist_id'])
            ->where('status', 'active')
            ->firstOrFail();

        // Check for conflicting bookings
        $conflictingBooking = TherapistBooking::where('therapist_id', $validated['therapist_id'])
            ->where('appointment_datetime', $validated['appointment_datetime'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($conflictingBooking) {
            return redirect()->back()->with('error', 'This time slot is already booked. Please choose another time.');
        }

        // Create the booking
        TherapistBooking::create([
            'user_id' => Auth::id(),
            'therapist_id' => $validated['therapist_id'],
            'appointment_datetime' => $validated['appointment_datetime'],
            'session_type' => $validated['session_type'],
            'user_message' => $validated['user_message'],
            'status' => 'pending',
        ]);

        return redirect()->route('therapists.bookings')->with('success', 'Booking request submitted successfully! You will be notified once confirmed.');
    }

    /**
     * Display user's bookings.
     */
    public function userBookings()
    {
        $bookings = TherapistBooking::where('user_id', Auth::id())
            ->with('therapist')
            ->orderBy('appointment_datetime', 'desc')
            ->paginate(10);

        return Inertia::render('Therapists/UserBookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, $id)
    {
        $booking = TherapistBooking::where('user_id', Auth::id())
            ->where('id', $id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->firstOrFail();

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Booking cancelled successfully.');
    }
}
