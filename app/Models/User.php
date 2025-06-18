<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserAppearance;
use App\Models\UserLifestyle;
use App\Models\UserBackground;
use App\Models\UserAbout;
use App\Models\UserPhoto;
use App\Models\Message;
use App\Models\UserOverview;
use App\Models\UserReport;

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
        'profile_photo',
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

    /**
     * Get the appearance data associated with the user.
     */
    public function appearance()
    {
        return $this->hasOne(UserAppearance::class);
    }
    
    /**
     * Get the lifestyle data associated with the user.
     */
    public function lifestyle()
    {
        return $this->hasOne(UserLifestyle::class);
    }
    
    /**
     * Get the background data associated with the user.
     */
    public function background()
    {
        return $this->hasOne(UserBackground::class);
    }
    
    /**
     * Get the about data associated with the user.
     */
    public function about()
    {
        return $this->hasOne(UserAbout::class);
    }
    
    /**
     * Get the overview data associated with the user.
     */
    public function overview()
    {
        return $this->hasOne(UserOverview::class);
    }
    
    /**
     * Get the photos associated with the user.
     */
    public function photos()
    {
        return $this->hasMany(UserPhoto::class)->orderBy('display_order');
    }
    
    /**
     * Get the primary photo associated with the user.
     */
    public function primaryPhoto()
    {
        return $this->hasOne(UserPhoto::class)->where('is_primary', true);
    }
    
    /**
     * Get the interests data associated with the user.
     */
    public function interests()
    {
        return $this->hasOne(\App\Models\UserInterest::class);
    }
    
    /**
     * Get the personality data associated with the user.
     */
    public function personality()
    {
        return $this->hasOne(\App\Models\UserPersonality::class);
    }
    
    /**
     * Get messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    
    /**
     * Get messages received by the user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    
    /**
     * Get unread messages received by the user.
     */
    public function unreadMessages()
    {
        return $this->receivedMessages()->where('is_read', false);
    }
    
    /**
     * Get all conversations for the user.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function conversations()
    {
        $sentMessages = $this->sentMessages()
            ->select('sender_id', 'receiver_id')
            ->distinct()
            ->get()
            ->map(function ($message) {
                return $message->receiver_id;
            });
            
        $receivedMessages = $this->receivedMessages()
            ->select('sender_id', 'receiver_id')
            ->distinct()
            ->get()
            ->map(function ($message) {
                return $message->sender_id;
            });
            
        $conversationUserIds = $sentMessages->merge($receivedMessages)->unique();
        
        return User::whereIn('id', $conversationUserIds)->get();
    }

    /**
     * Get reports made by this user.
     */
    public function reportsMade(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reporter_id');
    }

    /**
     * Get reports received against this user.
     */
    public function reportsReceived(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reported_user_id');
    }

    /**
     * Get reports reviewed by this user (admin).
     */
    public function reportsReviewed(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reviewed_by');
    }

    /**
     * Check if this user has blocked another user.
     */
    public function hasBlocked($userId): bool
    {
        return $this->reportsMade()
            ->where('reported_user_id', $userId)
            ->where('is_blocked', true)
            ->exists();
    }
}
