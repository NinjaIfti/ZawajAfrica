<script setup>
    import { Link } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted } from 'vue';
    import LikeSuccessModal from '@/Components/LikeSuccessModal.vue';
    import TierBadge from '@/Components/TierBadge.vue';
    import PhotoBlurControl from '@/Components/PhotoBlurControl.vue';

    const props = defineProps({
        matches: {
            type: Array,
            default: () => [],
        },
        userTier: {
            type: String,
            default: 'free',
        },
    });

    // Modal state
    const showLikeModal = ref(false);
    const likeModalType = ref('like');
    const currentLikedUser = ref(null);
    
    // Loading states for each match
    const loadingStates = ref({});
    
    // Online status tracking
    const onlineStatusInterval = ref(null);
    const matchOnlineStatus = ref({});

    // Handle like button click
    const handleLike = async match => {

        
        // Frontend validation
        if (!match || !match.id) {
            alert('Invalid user selected. Please refresh the page and try again.');
            return;
        }

        // Set loading state for this specific match
        loadingStates.value[match.id] = true;
        currentLikedUser.value = match.id; // Store the current user context for modal
        

        try {
            // Get CSRF token with fallback
            const csrfToken =
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                window.Laravel?.csrfToken ||
                '';

            if (!csrfToken) {
                alert('Security token missing. Please refresh the page and try again.');
                return;
            }


            
            // Set up AbortController for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
            
            const response = await fetch(route('matches.like', { user: match.id }), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({}),
                signal: controller.signal // Add timeout signal
            });
            
            clearTimeout(timeoutId); // Clear timeout if request completes
                            const data = await response.json();

            if (!response.ok) {
                if (response.status === 429) {
                    alert(data.error + '\n\nWould you like to upgrade your plan?');
                    if (confirm('Go to subscription page?')) {
                        window.location.href = route('subscription.index');
                    }
                    return;
                } else if (response.status === 400) {
                    // Handle already liked case
                    if (data.already_liked) {
                        likeModalType.value = 'already_liked';
                        showLikeModal.value = true;
                        return;
                    }
                    // Handle already matched case
                    if (data.already_matched) {
                        likeModalType.value = 'match';
                        showLikeModal.value = true;
                        return;
                    }
                    alert(data.error || 'Invalid request. Please refresh the page and try again.');
                    return;
                } else if (response.status === 500) {
                    alert('Server error. Please try again later or contact support.');
                    return;
                }
            }

            if (data.success) {
                if (data.match_created) {
                    likeModalType.value = 'match';
                    showLikeModal.value = true;
                } else {
                    likeModalType.value = 'like';
                    showLikeModal.value = true;
                }
            } else if (data.upgrade_required) {
                alert(data.error + '\n\nWould you like to upgrade your plan?');
                if (confirm('Go to subscription page?')) {
                    window.location.href = route('subscription.index');
                }
            } else {
                alert(data.error || 'Something went wrong. Please try again.');
            }
        } catch (error) {
            console.error('Error liking user:', error);
            
            if (error.name === 'AbortError') {
                // Handle timeout - show success message since like likely went through
                likeModalType.value = 'like';
                showLikeModal.value = true;

            } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                alert('Network error. Please check your internet connection and try again.');
            } else {
                alert('An unexpected error occurred. Please refresh the page and try again.');
            }
        } finally {
            // Clear loading state
            loadingStates.value[match.id] = false;
        }
    };

    // Handle message button click
    const handleMessage = match => {
        // Go to specific conversation with this user
        window.location.href = route('messages.show', match.id);
    };

    // Close like modal
    const closeLikeModal = () => {
        showLikeModal.value = false;
    };

    // Handle message from modal
    const handleMessageFromModal = () => {
        closeLikeModal();
        // Get the user ID from the current like context - we'll store it when like is clicked
        if (currentLikedUser.value) {
            window.location.href = route('messages.show', currentLikedUser.value);
        } else {
            window.location.href = route('messages');
        }
    };

    // Get compatibility color based on score
    const getCompatibilityColor = score => {
        if (score >= 85) return 'bg-green-500'; // Excellent match
        if (score >= 70) return 'bg-green-400'; // Very good match
        if (score >= 55) return 'bg-yellow-400'; // Good match
        if (score >= 40) return 'bg-orange-400'; // Fair match
        if (score >= 25) return 'bg-red-400'; // Low match
        return 'bg-gray-400'; // Very low match
    };

    // Handle photo unblurred event
    const handlePhotoUnblurred = (userId) => {
        
        // Optionally track analytics or show success message
    };

    // Check if user can see message button for a specific match
    const canShowMessageButton = (match) => {
        // If users are matched, always show message button (for all users)
        if (match.is_matched || match.matched) {
            return true;
        }
        
        // If user is Platinum, show message button even if not matched
        const userTier = props.userTier || 'free';
        return userTier === 'platinum';
    };

    // Get real-time online status for a match
    const isMatchOnline = (match) => {
        // Use updated status if available, otherwise fall back to original
        return matchOnlineStatus.value[match.id] !== undefined 
            ? matchOnlineStatus.value[match.id] 
            : match.online;
    };

    // Update online status for all matches
    const updateOnlineStatus = async () => {
        if (!props.matches || props.matches.length === 0) return;
        
        try {
            const userIds = props.matches.map(match => match.id);
            const response = await fetch('/api/users/online-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ user_ids: userIds })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.online_status) {
                    matchOnlineStatus.value = { ...matchOnlineStatus.value, ...data.online_status };
                }
            }
        } catch (error) {
            console.error('Failed to update online status:', error);
        }
    };

    // Initialize online status tracking
    onMounted(() => {
        // Initialize match online status from props
        props.matches.forEach(match => {
            matchOnlineStatus.value[match.id] = match.online;
        });
        
        // Update immediately
        updateOnlineStatus();
        
        // Set up periodic updates every 30 seconds
        onlineStatusInterval.value = setInterval(updateOnlineStatus, 30000);
    });

    // Cleanup interval on unmount
    onUnmounted(() => {
        if (onlineStatusInterval.value) {
            clearInterval(onlineStatusInterval.value);
        }
    });
</script>

<template>
    <div>
        <h2 class="mb-5 text-xl font-bold">All Matches</h2>

        <!-- Mobile/tablet: grid layout, Desktop: vertical stack -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:block space-y-0 lg:space-y-6 gap-5 md:gap-6">
            <div
                v-for="match in matches"
                :key="match.id"
                class="relative overflow-hidden rounded-lg bg-gray-800 shadow-lg lg:mb-6"
            >
                <!-- Make the entire card clickable except for the action buttons -->
                <Link :href="route('profile.view', { id: match.id })" class="block">
                    <!-- Match Image -->
                    <div class="h-48 sm:h-56 md:h-64 w-full bg-gray-700 relative">
                        <PhotoBlurControl
                            :imageUrl="match.image || '/images/male.png'"
                            :isBlurred="match.photos_blurred || false"
                            :canUnblur="match.photo_blur_mode === 'auto_unlock' || !match.photos_blurred"
                            :userId="match.id"
                            size="full"
                            @unblurred="handlePhotoUnblurred"
                        />
                        


                        <!-- Online Status -->
                        <div
                            v-if="isMatchOnline(match)"
                            class="absolute left-3 top-3 flex items-center rounded-full bg-black bg-opacity-70 px-2.5 py-1 text-xs text-white"
                        >
                            <span class="mr-1 h-1.5 w-1.5 rounded-full bg-green-500 inline-block"></span>
                            <span class="inline-block">Online</span>
                        </div>

                        <!-- Favorite Button -->
                        <div class="absolute right-3 top-3 rounded-full bg-amber-500 p-1.5">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                />
                            </svg>
                        </div>

                        <!-- Match percentage -->
                        <div class="absolute bottom-3 left-3 right-3">
                            <div class="flex items-center justify-between mb-1 text-white">
                                <span class="text-sm font-medium">
                                    {{ match.compatibility_score || match.compatibility || 0 }}%
                                </span>
                                <span class="text-sm font-medium">Match</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-white bg-opacity-30">
                                <div
                                    class="h-full rounded-full"
                                    :class="
                                        getCompatibilityColor(match.compatibility_score || match.compatibility || 0)
                                    "
                                    :style="{ width: (match.compatibility_score || match.compatibility || 0) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Match Info -->
                <div class="bg-white px-4 py-3 relative overflow-hidden">
                    <!-- Action buttons positioned at top-right -->
                    <div class="absolute top-3 right-3 flex space-x-2 z-40">
                        <button 
                            class="text-purple-800 p-1 relative z-50 bg-white rounded-full shadow-sm hover:bg-gray-50" 
                            @click.prevent="handleLike(match)" 
                            :disabled="loadingStates[match.id]"
                            :class="{ 'opacity-50 cursor-not-allowed': loadingStates[match.id] }"
                            aria-label="Like"
                        >
                            <!-- Loading spinner -->
                            <div v-if="loadingStates[match.id]" class="absolute inset-0 flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 text-purple-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <!-- Heart icon -->
                            <svg 
                                class="h-5 w-5 transition-opacity duration-200" 
                                :class="{ 'opacity-0': loadingStates[match.id] }"
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                />
                            </svg>
                        </button>
                        <button
                            v-if="canShowMessageButton(match)"
                            class="text-purple-800 p-1 relative z-50 bg-white rounded-full shadow-sm hover:bg-gray-50"
                            @click.prevent="handleMessage(match)"
                            aria-label="Message"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Content without action buttons -->
                    <Link :href="route('profile.view', { id: match.id })" class="block">
                        <div class="flex items-center gap-1 pr-20">
                            <h3 class="text-base font-bold truncate">{{ match.name }}</h3>
                            <!-- Show verification tick only if user is verified -->
                            <span v-if="match.is_verified || match.verified" class="text-amber-500 flex-shrink-0 ml-1">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <!-- Show tier badge if user is paid (not free) -->
                            <TierBadge v-if="match.tier && match.tier !== 'free'" :tier="match.tier" size="xs" />
                            <span class="ml-1 text-gray-500 text-sm flex-shrink-0">{{ match.age ? ', ' + match.age : '' }}</span>
                        </div>
                        <p v-if="match.location" class="text-gray-600 text-sm truncate pr-20">{{ match.location }}</p>
                        <p class="text-xs text-gray-500">{{ match.timestamp }}</p>
                    </Link>

                    <!-- Rainbow pattern - hidden on mobile -->
                    <div
                        class="absolute bottom-0 right-8 h-16 md:h-20 w-20 md:w-24 translate-x-1/3 translate-y-1/4 hidden sm:block z-10"
                    >
                        <img src="/images/card.png" alt="Pattern" class="h-full w-full object-contain opacity-60" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Like Success Modal -->
        <LikeSuccessModal 
            :show="showLikeModal" 
            :type="likeModalType" 
            @close="closeLikeModal" 
            @message="handleMessageFromModal" 
        />
    </div>
</template>
