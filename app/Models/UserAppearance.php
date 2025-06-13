<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAppearance extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'hair_color',
        'eye_color',
        'height',
        'weight',
    ];
    
    /**
     * Get the user that owns this appearance data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
