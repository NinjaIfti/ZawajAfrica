<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'entertainment',
        'food',
        'music',
        'sports'
    ];
    
    /**
     * Get the user that owns the interests.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 