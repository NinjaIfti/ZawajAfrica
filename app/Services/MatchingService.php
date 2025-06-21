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
        $query = User::select('id', 'name', 'gender', 'dob_year', 'dob_month', 'dob_day', 'city', 'state', 'country', 'profile_photo', 'subscription_plan', 'subscription_status')
            ->with([
                'photos' => function($q) { $q->select('id', 'user_id', 'photo_path', 'is_primary')->where('is_primary', true)->limit(1); },
                'about' => function($q) { $q->select('user_id', 'religion', 'bio', 'marital_status'); },
                'background' => function($q) { $q->select('user_id', 'education_level', 'occupation', 'income_range'); },
                'appearance' => function($q) { $q->select('user_id', 'height', 'ethnicity'); },
                'lifestyle' => function($q) { $q->select('user_id', 'smoking', 'drinking'); }
            ])
            ->where('id', '!=', $user->id)
            ->where('email', '!=', 'admin@zawagafrica.com')
            ->whereNotNull('profile_photo');

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
            $query->whereHas('about', function($q) use ($filters) {
                $q->where('religion', $filters['religion']);
            });
        }

        // Basic filters for all users
        if (!empty($filters['marital_status'])) {
            $query->whereHas('about', function($q) use ($filters) {
                $q->where('marital_status', $filters['marital_status']);
            });
        }

        // Advanced filters for Gold/Platinum
        if (in_array($userTier, [UserTierService::TIER_GOLD, UserTierService::TIER_PLATINUM])) {
            // Education level
            if (!empty($filters['education_level'])) {
                $query->whereHas('background', function($q) use ($filters) {
                    $q->where('education_level', $filters['education_level']);
                });
            }

            // Occupation
            if (!empty($filters['occupation'])) {
                $query->whereHas('background', function($q) use ($filters) {
                    $q->where('occupation', 'LIKE', '%' . $filters['occupation'] . '%');
                });
            }

            // Income range
            if (!empty($filters['income_range'])) {
                $query->whereHas('background', function($q) use ($filters) {
                    $q->where('income_range', $filters['income_range']);
                });
            }
        }

        // Platinum exclusive filters
        if ($userTier === UserTierService::TIER_PLATINUM) {
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
                $query->whereHas('appearance', function($q) use ($filters) {
                    $q->where('ethnicity', $filters['ethnicity']);
                });
            }

            // Smoking preference
            if (!empty($filters['smoking'])) {
                $query->whereHas('lifestyle', function($q) use ($filters) {
                    $q->where('smoking', $filters['smoking']);
                });
            }

            // Drinking preference
            if (!empty($filters['drinking'])) {
                $query->whereHas('lifestyle', function($q) use ($filters) {
                    $q->where('drinking', $filters['drinking']);
                });
            }

            // Elite only filter
            if (!empty($filters['elite_only']) && $filters['elite_only']) {
                $query->where('subscription_plan', 'platinum')
                      ->where('subscription_status', 'active');
            }
        }

        return $query;
    }

    private function scoreMatches(User $user, Collection $potentialMatches): Collection
    {
        return $potentialMatches->map(function ($match) use ($user) {
            $score = 0;

            // Age compatibility
            $score += $this->calculateAgeCompatibility($user, $match) * 0.3;

            // Location compatibility
            $score += $this->calculateLocationCompatibility($user, $match) * 0.3;

            // Religion compatibility
            $score += $this->calculateReligiousCompatibility($user, $match) * 0.4;

            $match->compatibility_score = round($score, 1);
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
        $userReligion = $user->about->religion ?? null;
        $matchReligion = $match->about->religion ?? null;
        
        if (!$userReligion || !$matchReligion) return 0; // No bonus for incomplete data
        if ($userReligion === $matchReligion) return 100;
        
        return 30;
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

            $match->age = $match->dob_year ? (now()->year - $match->dob_year) : null;

            return [
                'id' => $match->id,
                'name' => $match->name,
                'age' => $match->age,
                'location' => trim($match->city . ', ' . $match->state, ', '),
                'profile_photo' => $match->profile_photo,
                'compatibility_score' => $match->compatibility_score ?? 0,
                'bio' => $match->about->bio ?? null,
                'education' => $match->background->education_level ?? null,
                'occupation' => $match->background->occupation ?? null,
                'religion' => $match->about->religion ?? null,
                'tier_badge' => $this->tierService->getTierInfo($this->tierService->getUserTier($match))['badge'],
                'can_access_contact' => $canAccessContact
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
} 