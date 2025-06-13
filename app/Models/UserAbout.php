<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAbout extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'heading',
        'about_me',
        'looking_for',
    ];
    
    /**
     * Get the user that owns this about data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
