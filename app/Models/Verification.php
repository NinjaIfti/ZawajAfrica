<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'front_image',
        'back_image',
        'storage_disk',
        'front_mime_type',
        'front_size',
        'back_mime_type',
        'back_size',
        'status',
        'rejection_reason',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified_at' => 'datetime',
        'front_size' => 'integer',
        'back_size' => 'integer',
    ];

    protected $hidden = [
        'front_image',
        'back_image',
        'storage_disk',
    ];

    /**
     * Get the user that owns the verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
