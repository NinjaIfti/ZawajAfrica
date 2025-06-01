<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'interested_in',
        'dob_day',
        'dob_month',
        'dob_year',
        'country',
        'state',
        'city',
        'is_verified',
        'verification_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
    ];
    
    /**
     * Get user's age based on date of birth.
     */
    public function getAgeAttribute()
    {
        if (!$this->dob_day || !$this->dob_month || !$this->dob_year) {
            return null;
        }
        
        $monthMap = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 
            'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 
            'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
        ];
        
        $month = isset($monthMap[$this->dob_month]) ? $monthMap[$this->dob_month] : 1;
        $birthDate = \Carbon\Carbon::createFromDate($this->dob_year, $month, $this->dob_day);
        return $birthDate->age;
    }
    
    /**
     * Get the profile associated with the user.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
    
    /**
     * Create or update the user's profile.
     */
    public function createOrUpdateProfile(array $attributes)
    {
        if ($this->profile) {
            return $this->profile->update($attributes);
        }
        
        return $this->profile()->create($attributes);
    }

    /**
     * Get the user's document verification.
     */
    public function verification(): HasOne
    {
        return $this->hasOne(Verification::class);
    }
}
