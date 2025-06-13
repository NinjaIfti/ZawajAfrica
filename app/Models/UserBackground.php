<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBackground extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nationality',
        'education',
        'language_spoken',
        'born_reverted',
        'read_quran',
    ];
    
    /**
     * Get the user that owns this background data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
