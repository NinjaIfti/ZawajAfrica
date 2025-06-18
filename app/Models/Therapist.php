<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Therapist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'bio',
        'photo',
        'specializations',
        'degree',
        'years_of_experience',
        'hourly_rate',
        'availability',
        'phone',
        'email',
        'languages',
        'status',
        'additional_info',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'specializations' => 'array',
        'availability' => 'array',
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['photo_url'];

    /**
     * Get all bookings for this therapist.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(TherapistBooking::class);
    }

    /**
     * Get pending bookings for this therapist.
     */
    public function pendingBookings(): HasMany
    {
        return $this->hasMany(TherapistBooking::class)->where('status', 'pending');
    }

    /**
     * Get confirmed bookings for this therapist.
     */
    public function confirmedBookings(): HasMany
    {
        return $this->hasMany(TherapistBooking::class)->where('status', 'confirmed');
    }

    /**
     * Check if therapist is available.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get photo URL.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
