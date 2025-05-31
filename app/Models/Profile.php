<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'profile_picture',
        'bio',
        'height',
        'body_type',
        'ethnicity',
        'religion',
        'religious_values',
        'marital_status',
        'children',
        'want_children',
        'education_level',
        'field_of_study',
        'occupation',
        'income_range',
        'smoking',
        'drinking',
        'prayer_frequency',
        'hijab_wearing',
        'beard_type',
        'age_preference_min',
        'age_preference_max',
        'about_match',
    ];
    
    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
