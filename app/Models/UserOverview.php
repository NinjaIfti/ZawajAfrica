<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOverview extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'education_level',
        'employment_status',
        'income_range',
        'religion',
        'marital_status',
    ];
    
    /**
     * Get the user that owns this overview data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 