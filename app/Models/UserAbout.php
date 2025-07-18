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
        'looking_for_age_min',
        'looking_for_age_max',
        'looking_for_education',
        'looking_for_religious_practice',
        'looking_for_marital_status',
        'looking_for_relocate',
        'looking_for_children',
        'looking_for_tribe',
    ];
    
    /**
     * Get the user that owns this about data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
