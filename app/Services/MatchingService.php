<?php

namespace App\Services;

use App\Models\User;
use App\Services\UserTierService;
use App\Services\OpenAIService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MatchingService
{
    protected UserTierService $tierService;
    protected OpenAIService $openAIService;

    // Matching weights for different criteria
    private array $matchingWeights = [
        'age_compatibility' => 25,
        'location_proximity' => 20,
        'religious_values' => 20,
        'education_level' => 15,
        'interests_overlap' => 10,
        'lifestyle_compatibility' => 10
    ];

    public function __construct(UserTierService $tierService, OpenAIService $openAIService)
    {
        $this->tierService = $tierService;
        $this->openAIService = $openAIService;
    }

    /**
     * Get matches for a user based on their tier and preferences
     */
    public function getMatches(User $user, array $filters = [], int $limit = 20): array
    {
        $userTier = $this->tierService->getUserTier($user);

        // Rate limiting - max 10 profile view requests per minute
        $rateLimitKey = "user_profile_views_rate_limit:{$user->id}";
        $viewsInLastMinute = Cache::get($rateLimitKey, 0);
        
        if ($viewsInLastMinute >= 10) {
            return [
                'matches' => [],
                'error' => 'Too many profile view requests. Please wait a moment before browsing again.',
                'rate_limited' => true,
                'tier' => $userTier
            ];
        }

        // Check if user can view profiles
        $canView = $this->tierService->canViewProfile($user);
        if (!$canView['allowed']) {
            return [
                'matches' => [],
                'error' => 'Daily profile view limit reached',
                'upgrade_required' => true,
                'tier' => $userTier
            ];
        }

        // Increment rate limiting counter
        Cache::put($rateLimitKey, $viewsInLastMinute + 1, now()->addMinute());

        // Get potential matches
        $query = $this->buildMatchQuery($user, $filters, $userTier);
        $potentialMatches = $query->limit($limit * 2)->get();

        // Calculate compatibility scores
        $scoredMatches = $this->scoreMatches($user, $potentialMatches);

        // Sort and limit results
        $finalMatches = $scoredMatches
            ->sortByDesc('compatibility_score')
            ->take($limit)
            ->values();

        return [
            'matches' => $this->formatMatches($finalMatches, $user),
            'total_potential' => $potentialMatches->count(),
            'can_message' => $this->tierService->canSendMessage($user),
            'tier_info' => $this->tierService->getTierInfo($userTier),
            'applied_filters' => $filters
        ];
    }

    /**
     * Search users by name
     */
    public function searchByName(User $user, string $search, array $filters = [], int $limit = 20): array
    {
        $userTier = $this->tierService->getUserTier($user);

        // Build query with search and filters
        $query = $this->buildMatchQuery($user, $filters, $userTier);
        
        // Add name search
        $query->where('name', 'LIKE', '%' . $search . '%');
        
        $results = $query->limit($limit)->get();

        // Calculate compatibility scores
        $scoredResults = $this->scoreMatches($user, $results);

        // Sort by relevance (name match first, then compatibility)
        $finalResults = $scoredResults->sortBy(function($match) use ($search) {
            $nameScore = 0;
            if (stripos($match->name, $search) === 0) {
                $nameScore = 100; // Starts with search term
            } elseif (stripos($match->name, $search) !== false) {
                $nameScore = 50; // Contains search term
            }
            
            return -($nameScore + ($match->compatibility_score ?? 0) * 0.1);
        })->values();

        return [
            'matches' => $this->formatMatches($finalResults, $user),
            'search_term' => $search,
            'total_found' => $results->count(),
            'can_message' => $this->tierService->canSendMessage($user),
            'tier_info' => $this->tierService->getTierInfo($userTier)
        ];
    }

    private function buildMatchQuery(User $user, array $filters, string $userTier)
    {
        $query = User::select('id', 'name', 'gender', 'dob_year', 'dob_month', 'dob_day', 'city', 'state', 'country', 'profile_photo', 'photos_blurred', 'photo_blur_mode', 'is_verified', 'subscription_plan', 'subscription_status')
            ->with([
                'photos' => function($q) { $q->select('id', 'user_id', 'photo_path', 'is_primary')->where('is_primary', true)->limit(1); },
                'about' => function($q) { $q->select('user_id', 'heading', 'about_me', 'looking_for'); },
                'overview' => function($q) { $q->select('user_id', 'religion', 'marital_status', 'education_level', 'employment_status', 'income_range'); },
                'background' => function($q) { $q->select('user_id', 'education', 'nationality', 'language_spoken', 'ethnic_group'); },
                'appearance' => function($q) { $q->select('user_id', 'height'); },
                'lifestyle' => function($q) { $q->select('user_id', 'smokes', 'drinks'); },
                'interests' => function($q) { $q->select('user_id', 'entertainment', 'food', 'music', 'sports'); }
            ])
            ->where('id', '!=', $user->id)
            ->where('id', '!=', 4) // Exclude admin user
            ->where('email', '!=', 'admin@zawagafrica.com')
            ->whereNotNull('profile_photo');

        // Restrict elite member visibility to Platinum users only
        // COMMENTED OUT: Allow all users to see all users regardless of tier
        /*
        if ($userTier !== UserTierService::TIER_PLATINUM) {
            $query->where(function($q) {
                $q->where(function($subQ) {
                    // NOT an active platinum user
                    $subQ->where('subscription_plan', '!=', 'platinum')
                         ->orWhere('subscription_status', '!=', 'active')
                         ->orWhere(function($expiredQ) {
                             $expiredQ->whereNotNull('subscription_expires_at')
                                     ->where('subscription_expires_at', '<=', now());
                         });
                });
            });
        }
        */

        // Gender filtering
        if ($user->gender === 'male') {
            $query->where('gender', 'female');
        } elseif ($user->gender === 'female') {
            $query->where('gender', 'male');
        }

        // Age range filter
        if (!empty($filters['age_min']) || !empty($filters['age_max'])) {
            $currentYear = now()->year;
            
            if (!empty($filters['age_max'])) {
                $minBirthYear = $currentYear - (int)$filters['age_max'];
                $query->where('dob_year', '>=', $minBirthYear);
            }
            
            if (!empty($filters['age_min'])) {
                $maxBirthYear = $currentYear - (int)$filters['age_min'];
                $query->where('dob_year', '<=', $maxBirthYear);
            }
        }

        // Location filter
        if (!empty($filters['location'])) {
            $location = $filters['location'];
            $query->where(function($q) use ($location) {
                $q->where('city', 'LIKE', '%' . $location . '%')
                  ->orWhere('state', 'LIKE', '%' . $location . '%')
                  ->orWhere('country', 'LIKE', '%' . $location . '%');
            });
        }

        // Religion filter
        if (!empty($filters['religion'])) {
            $query->whereHas('overview', function($q) use ($filters) {
                $q->where('religion', $filters['religion']);
            });
        }

        // Basic filters for all users
        if (!empty($filters['marital_status'])) {
            $query->whereHas('overview', function($q) use ($filters) {
                $q->where('marital_status', $filters['marital_status']);
            });
        }

        // Advanced filters for Gold/Platinum
        if (in_array($userTier, [UserTierService::TIER_GOLD, UserTierService::TIER_PLATINUM])) {
            // Education level
            if (!empty($filters['education_level'])) {
                $query->whereHas('overview', function($q) use ($filters) {
                    $q->where('education_level', $filters['education_level']);
                });
            }

            // Employment status
            if (!empty($filters['employment_status'])) {
                $query->whereHas('overview', function($q) use ($filters) {
                    $q->where('employment_status', 'LIKE', '%' . $filters['employment_status'] . '%');
                });
            }

            // Income range
            if (!empty($filters['income_range'])) {
                $query->whereHas('overview', function($q) use ($filters) {
                    $q->where('income_range', $filters['income_range']);
                });
            }
        }

        // Platinum exclusive filters
        if ($userTier === UserTierService::TIER_PLATINUM) {
            // Elite member access - only show other Platinum users (with expiry validation)
            if (!empty($filters['elite_only']) && $filters['elite_only']) {
                $query->where('subscription_plan', 'platinum')
                      ->where('subscription_status', 'active')
                      ->where(function($q) {
                          $q->whereNull('subscription_expires_at')
                            ->orWhere('subscription_expires_at', '>', now());
                      });
            }
            
            // Height range
            if (!empty($filters['height_min']) || !empty($filters['height_max'])) {
                $query->whereHas('appearance', function($q) use ($filters) {
                    if (!empty($filters['height_min'])) {
                        $q->where('height', '>=', (int)$filters['height_min']);
                    }
                    if (!empty($filters['height_max'])) {
                        $q->where('height', '<=', (int)$filters['height_max']);
                    }
                });
            }

            // Ethnicity
            if (!empty($filters['ethnicity'])) {
                $query->whereHas('background', function($q) use ($filters) {
                    $q->where('ethnic_group', $filters['ethnicity']);
                });
            }

            // Smoking preference
            if (!empty($filters['smoking'])) {
                $query->whereHas('lifestyle', function($q) use ($filters) {
                    $q->where('smokes', $filters['smoking']);
                });
            }

            // Drinking preference
            if (!empty($filters['drinking'])) {
                $query->whereHas('lifestyle', function($q) use ($filters) {
                    $q->where('drinks', $filters['drinking']);
                });
            }
        }

        return $query;
    }

    private function scoreMatches(User $user, Collection $potentialMatches): Collection
    {
        return $potentialMatches->map(function ($match) use ($user) {
            $totalScore = 0;
            $weights = $this->matchingWeights;

            // Age compatibility (25%)
            $ageScore = $this->calculateAgeCompatibility($user, $match);
            $totalScore += ($ageScore * $weights['age_compatibility']) / 100;

            // Location proximity (20%)
            $locationScore = $this->calculateLocationCompatibility($user, $match);
            $totalScore += ($locationScore * $weights['location_proximity']) / 100;

            // Religious values (20%)
            $religionScore = $this->calculateReligiousCompatibility($user, $match);
            $totalScore += ($religionScore * $weights['religious_values']) / 100;

            // Education level (15%)
            $educationScore = $this->calculateEducationCompatibility($user, $match);
            $totalScore += ($educationScore * $weights['education_level']) / 100;

            // Interests overlap (10%)
            $interestsScore = $this->calculateInterestsCompatibility($user, $match);
            $totalScore += ($interestsScore * $weights['interests_overlap']) / 100;

            // Lifestyle compatibility (10%)
            $lifestyleScore = $this->calculateLifestyleCompatibility($user, $match);
            $totalScore += ($lifestyleScore * $weights['lifestyle_compatibility']) / 100;

            // Add preference matching bonus
            $preferenceBonus = $this->calculatePreferenceMatching($user, $match);
            $totalScore += $preferenceBonus;

            // Ensure score is between 0 and 100
            $finalScore = max(0, min(100, round($totalScore, 1)));
            
            $match->compatibility_score = $finalScore;
            return $match;
        });
    }

    private function calculateAgeCompatibility(User $user, User $match): int
    {
        if (!$user->dob_year || !$match->dob_year) return 0; // No bonus for incomplete data

        $userAge = now()->year - $user->dob_year;
        $matchAge = now()->year - $match->dob_year;
        $ageDifference = abs($userAge - $matchAge);

        if ($ageDifference <= 3) return 100;
        if ($ageDifference <= 7) return 80;
        if ($ageDifference <= 12) return 60;
        
        return max(20, 60 - ($ageDifference - 12) * 5);
    }

    private function calculateLocationCompatibility(User $user, User $match): int
    {
        if ($user->city === $match->city && $user->city) return 100;
        if ($user->state === $match->state && $user->state) return 80;
        if ($user->country === $match->country && $user->country) return 60;
        
        return 20;
    }

    private function calculateReligiousCompatibility(User $user, User $match): int
    {
        $userReligion = $user->overview->religion ?? null;
        $matchReligion = $match->overview->religion ?? null;
        
        if (!$userReligion || !$matchReligion) return 50; // Neutral score for incomplete data
        
        // Exact match
        if ($userReligion === $matchReligion) return 100;
        
        // Similar branches (e.g., Islam variants)
        $similarReligions = [
            ['Islam', 'Islam - Sunni', 'Islam - Shia', 'Islam - Sufi', 'Islam - Other'],
        ];
        
        foreach ($similarReligions as $group) {
            if (in_array($userReligion, $group) && in_array($matchReligion, $group)) {
                return 85; // High compatibility for similar branches
            }
        }
        
        return 20; // Low compatibility for different religions
    }

    /**
     * Format matches for frontend consumption
     */
    private function formatMatches(Collection $matches, User $currentUser): array
    {
        $userLimits = $this->tierService->getUserLimits($currentUser);
        $canAccessContact = $userLimits['contact_details'] ?? false;

        return $matches->map(function ($match) use ($canAccessContact) {
            if ($match->profile_photo) {
                $match->profile_photo = asset('storage/' . $match->profile_photo);
            }

            if (!$canAccessContact && $match->about) {
                $match->about->phone = null;
                $match->about->email = null;
                $match->about->whatsapp = null;
            }

            // Calculate age with month and day precision
            $age = null;
            if ($match->dob_year && $match->dob_month && $match->dob_day) {
                // Convert month name to month number
                $monthMap = [
                    'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4,
                    'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8,
                    'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
                ];
                
                $month = $monthMap[$match->dob_month] ?? 1;
                $birthDate = \Carbon\Carbon::createFromDate($match->dob_year, $month, $match->dob_day);
                $age = $birthDate->age;
            } elseif ($match->dob_year) {
                $age = now()->year - $match->dob_year;
            }

            // Format location from available fields
            $locationParts = array_filter([$match->city, $match->state, $match->country]);
            $location = implode(', ', $locationParts);

            $userTier = $this->tierService->getUserTier($match);
            
            return [
                'id' => $match->id,
                'name' => $match->name,
                'age' => $age,
                'location' => $location,
                'image' => $match->profile_photo,  // For MatchCard compatibility
                'profile_photo' => $match->profile_photo,
                'photos_blurred' => $match->photos_blurred ?? false,
                'photo_blur_mode' => $match->photo_blur_mode ?? 'manual',
                'is_verified' => $match->is_verified ?? false,
                'compatibility_score' => $match->compatibility_score ?? 0,
                'compatibility' => $match->compatibility_score ?? 0, // For backwards compatibility
                'bio' => $match->about->about_me ?? null,
                'education' => $match->overview->education_level ?? null,
                'occupation' => $match->overview->employment_status ?? null,
                'religion' => $match->overview->religion ?? null,
                'tier' => $userTier,
                'tier_badge' => $this->tierService->getTierInfo($userTier)['badge'],
                'can_access_contact' => $canAccessContact,
                'timestamp' => 'Active now'  // Default timestamp for match cards
            ];
        })->toArray();
    }

    /**
     * Get advanced search filters for Platinum users
     */
    public function getAdvancedFilters(): array
    {
        return [
            'basic_filters' => [
                'age_min' => 'Minimum Age',
                'age_max' => 'Maximum Age',
                'location' => 'Location',
                'marital_status' => 'Marital Status',
                'religion' => 'Religion'
            ],
            'gold_filters' => [
                'education_level' => 'Education Level',
                'occupation' => 'Occupation',
                'income_range' => 'Income Range'
            ],
            'platinum_filters' => [
                'height_min' => 'Minimum Height',
                'height_max' => 'Maximum Height',
                'ethnicity' => 'Ethnicity',
                'smoking' => 'Smoking Preference',
                'drinking' => 'Drinking Preference',
                'elite_only' => 'Platinum Members Only'
            ]
        ];
    }

    private function calculateEducationCompatibility(User $user, User $match): int
    {
        $userEducation = $user->overview->education_level ?? null;
        $matchEducation = $match->overview->education_level ?? null;
        
        if (!$userEducation || !$matchEducation) return 50; // Neutral score
        
        // Education level hierarchy
        $educationLevels = [
            'High School' => 1,
            'Some College' => 2,
            'Associate\'s Degree' => 3,
            'Bachelor\'s Degree' => 4,
            'Master\'s Degree' => 5,
            'Professional Degree' => 6,
            'Doctorate' => 7
        ];
        
        $userLevel = $educationLevels[$userEducation] ?? 3;
        $matchLevel = $educationLevels[$matchEducation] ?? 3;
        $difference = abs($userLevel - $matchLevel);
        
        if ($difference === 0) return 100;
        if ($difference === 1) return 85;
        if ($difference === 2) return 70;
        if ($difference === 3) return 55;
        
        return 40;
    }

    private function calculateInterestsCompatibility(User $user, User $match): int
    {
        $userInterests = $user->interests ?? null;
        $matchInterests = $match->interests ?? null;
        
        if (!$userInterests || !$matchInterests) return 50;
        
        $interestFields = ['entertainment', 'food', 'music', 'sports'];
        $commonInterests = 0;
        $totalFields = 0;
        
        foreach ($interestFields as $field) {
            $userValue = $userInterests->$field ?? null;
            $matchValue = $matchInterests->$field ?? null;
            
            if ($userValue && $matchValue) {
                $totalFields++;
                
                // For text fields, check for similarity
                if (is_string($userValue) && is_string($matchValue)) {
                    $similarity = $this->calculateTextSimilarity($userValue, $matchValue);
                    $commonInterests += $similarity / 100; // Convert percentage to decimal
                }
            }
        }
        
        if ($totalFields === 0) return 50;
        
        $compatibilityScore = ($commonInterests / $totalFields) * 100;
        
        // Scale the score
        if ($compatibilityScore >= 70) return 100;
        if ($compatibilityScore >= 50) return 85;
        if ($compatibilityScore >= 30) return 70;
        if ($compatibilityScore >= 15) return 55;
        
        return 40;
    }
    
    private function calculateTextSimilarity(string $text1, string $text2): int
    {
        // Convert to lowercase for comparison
        $text1 = strtolower(trim($text1));
        $text2 = strtolower(trim($text2));
        
        if ($text1 === $text2) return 100;
        
        // Check if one contains the other
        if (strpos($text1, $text2) !== false || strpos($text2, $text1) !== false) {
            return 80;
        }
        
        // Split into words and find common words
        $words1 = array_filter(explode(' ', $text1));
        $words2 = array_filter(explode(' ', $text2));
        
        if (empty($words1) || empty($words2)) return 0;
        
        $commonWords = array_intersect($words1, $words2);
        $totalWords = array_unique(array_merge($words1, $words2));
        
        if (empty($totalWords)) return 0;
        
        return round((count($commonWords) / count($totalWords)) * 100);
    }

    private function calculateLifestyleCompatibility(User $user, User $match): int
    {
        $userLifestyle = $user->lifestyle ?? null;
        $matchLifestyle = $match->lifestyle ?? null;
        
        if (!$userLifestyle || !$matchLifestyle) return 50;
        
        $score = 0;
        $totalFactors = 0;
        
        // Smoking compatibility
        if ($userLifestyle->smokes && $matchLifestyle->smokes) {
            $smokingCompatibility = $this->compareLifestyleChoice(
                $userLifestyle->smokes, 
                $matchLifestyle->smokes
            );
            $score += $smokingCompatibility;
            $totalFactors++;
        }
        
        // Drinking compatibility
        if ($userLifestyle->drinks && $matchLifestyle->drinks) {
            $drinkingCompatibility = $this->compareLifestyleChoice(
                $userLifestyle->drinks, 
                $matchLifestyle->drinks
            );
            $score += $drinkingCompatibility;
            $totalFactors++;
        }
        
        // Children compatibility
        if ($userLifestyle->has_children && $matchLifestyle->has_children) {
            $childrenCompatibility = $this->compareChildrenCompatibility(
                $userLifestyle->has_children, 
                $matchLifestyle->has_children
            );
            $score += $childrenCompatibility;
            $totalFactors++;
        }
        
        return $totalFactors > 0 ? round($score / $totalFactors) : 50;
    }

    private function compareLifestyleChoice(string $userChoice, string $matchChoice): int
    {
        if ($userChoice === $matchChoice) return 100;
        
        // Define compatibility matrices
        $compatibilityMatrix = [
            'No' => ['No' => 100, 'Occasionally' => 70, 'Yes' => 30],
            'Occasionally' => ['No' => 70, 'Occasionally' => 100, 'Yes' => 80],
            'Yes' => ['No' => 30, 'Occasionally' => 80, 'Yes' => 100]
        ];
        
        return $compatibilityMatrix[$userChoice][$matchChoice] ?? 50;
    }

    private function compareChildrenCompatibility(string $userChoice, string $matchChoice): int
    {
        if ($userChoice === $matchChoice) return 100;
        
        // Define compatibility matrix for children preferences
        $compatibilityMatrix = [
            'No' => [
                'No' => 100,
                'Yes – living with me' => 40,
                'Yes – not living with me' => 60
            ],
            'Yes – living with me' => [
                'No' => 40,
                'Yes – living with me' => 100,
                'Yes – not living with me' => 80
            ],
            'Yes – not living with me' => [
                'No' => 60,
                'Yes – living with me' => 80,
                'Yes – not living with me' => 100
            ]
        ];
        
        return $compatibilityMatrix[$userChoice][$matchChoice] ?? 50;
    }

    private function calculatePreferenceMatching(User $user, User $match): int
    {
        $userAbout = $user->about ?? null;
        if (!$userAbout) return 0;
        
        $bonus = 0;
        $matchAge = $match->dob_year ? (now()->year - $match->dob_year) : null;
        
        // Age preference matching
        if ($userAbout->looking_for_age_min && $userAbout->looking_for_age_max && $matchAge) {
            if ($matchAge >= $userAbout->looking_for_age_min && $matchAge <= $userAbout->looking_for_age_max) {
                $bonus += 10; // Age preference match bonus
            }
        }
        
        // Education preference matching
        if ($userAbout->looking_for_education) {
            $matchEducation = $match->overview->education_level ?? null;
            if ($userAbout->looking_for_education === $matchEducation) {
                $bonus += 8;
            }
        }
        
        // Religious practice preference matching
        if ($userAbout->looking_for_religious_practice && $match->about) {
            // This would need a religious_practice field in the about table
            // For now, we'll use a simplified check
            $bonus += 5;
        }
        
        // Marital status preference matching
        if ($userAbout->looking_for_marital_status && $match->overview) {
            if ($userAbout->looking_for_marital_status === $match->overview->marital_status) {
                $bonus += 7;
            }
        }
        
        return min($bonus, 25); // Cap bonus at 25 points
    }
} 