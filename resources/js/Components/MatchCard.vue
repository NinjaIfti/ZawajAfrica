<script setup>
    import { Link } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import LikeSuccessModal from '@/Components/LikeSuccessModal.vue';

    const props = defineProps({
        matches: {
            type: Array,
            default: () => [],
        },
    });

    // Modal state
    const showLikeModal = ref(false);
    const likeModalType = ref('like');

    // Handle like button click
    const handleLike = async match => {
        // Frontend validation
        if (!match || !match.id || typeof match.id !== 'number') {
            alert('Invalid user selected. Please refresh the page and try again.');
            return;
        }

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

            const response = await fetch(route('matches.like', { user: match.id }), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) {
                if (response.status === 429) {
                    const data = await response.json();
                    alert(data.error + '\n\nWould you like to upgrade your plan?');
                    if (confirm('Go to subscription page?')) {
                        window.location.href = route('subscription.index');
                    }
                    return;
                } else if (response.status === 500) {
                    alert('Server error. Please try again later or contact support.');
                    return;
                } else if (response.status === 400) {
                    const data = await response.json();
                    alert(data.error || 'Invalid request. Please refresh the page and try again.');
                    return;
                }
            }

            const data = await response.json();

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
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                alert('Network error. Please check your internet connection and try again.');
            } else if (error.name === 'AbortError') {
                alert('Request timed out. Please try again.');
            } else {
                alert('An unexpected error occurred. Please refresh the page and try again.');
            }
        }
    };

    // Handle message button click
    const handleMessage = match => {
        // Check if user can message (this will be handled by backend/middleware)
        window.location.href = route('messages');
    };

    // Close like modal
    const closeLikeModal = () => {
        showLikeModal.value = false;
    };

    // Handle message from modal
    const handleMessageFromModal = () => {
        closeLikeModal();
        window.location.href = route('messages');
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
                        <img
                            :src="match.image"
                            :alt="match.name"
                            class="h-full w-full object-cover object-top"
                            @error="$event.target.src = '/images/placeholder.jpg'"
                        />

                        <!-- Online Status -->
                        <div
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
                    <div class="flex items-center justify-between">
                        <Link :href="route('profile.view', { id: match.id })" class="block flex-1 min-w-0">
                            <div class="flex items-center">
                                <h3 class="text-base font-bold truncate">{{ match.name }}</h3>
                                <span class="ml-1 text-amber-500 text-base flex-shrink-0">✓</span>
                                <span class="ml-1 text-gray-500 text-sm flex-shrink-0">{{ match.age ? ', ' + match.age : '' }}</span>
                            </div>
                            <p v-if="match.location" class="text-gray-600 text-sm truncate">{{ match.location }}</p>
                            <p class="text-xs text-gray-500">{{ match.timestamp }}</p>
                        </Link>
                        <div class="flex space-x-3 items-center">
                            <button class="text-purple-800 p-1" @click.prevent="handleLike(match)" aria-label="Like">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                    />
                                </svg>
                            </button>
                            <button
                                class="text-purple-800 p-1"
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
                    </div>

                    <!-- Rainbow pattern - hidden on mobile -->
                    <div
                        class="absolute bottom-0 right-8 h-16 md:h-20 w-20 md:w-24 translate-x-1/3 translate-y-1/4 hidden sm:block"
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
