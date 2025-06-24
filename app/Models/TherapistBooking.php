<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapistBooking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'therapist_id',
        'appointment_datetime',
        'booking_date',
        'booking_time',
        'notes',
        'status',
        'amount',
        'payment_reference',
        'payment_gateway',
        'payment_status',
        'user_message',
        'admin_notes',
        'session_type',
        'meeting_link',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
        'platform',
        'zoho_booking_id',
        'zoho_data',
        'zoho_last_sync',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'appointment_datetime' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2',
        'zoho_data' => 'array',
        'zoho_last_sync' => 'datetime',
    ];

    /**
     * Boot the model and add event listeners.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Automatically set booking_date and booking_time when appointment_datetime is set
        static::saving(function ($booking) {
            if ($booking->appointment_datetime && $booking->isDirty('appointment_datetime')) {
                $booking->booking_date = $booking->appointment_datetime->format('Y-m-d');
                $booking->booking_time = $booking->appointment_datetime->format('g:i A');
            }
        });
    }

    /**
     * Get the user who made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the therapist for this booking.
     */
    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    /**
     * Check if booking is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if booking is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if booking is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if booking is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
