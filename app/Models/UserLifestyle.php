<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLifestyle extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'drinks',
        'smokes',
        'has_children',
        'number_of_children',
        'want_children',
        'occupation',
    ];
    
    /**
     * Get the user that owns this lifestyle data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
