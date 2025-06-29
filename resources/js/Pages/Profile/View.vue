<script setup>
    import { Head, Link, router } from '@inertiajs/vue3';
    import { ref, computed, onMounted, onUnmounted } from 'vue';
    import Sidebar from '@/Components/Sidebar.vue';
    import ReportModal from '@/Components/ReportModal.vue';
    import LikeSuccessModal from '@/Components/LikeSuccessModal.vue';
    import TierBadge from '@/Components/TierBadge.vue';
    import PhotoBlurControl from '@/Components/PhotoBlurControl.vue';

    // Modal and state management
    const showReportModal = ref(false);
    const showLikeModal = ref(false);
    const likeModalType = ref('like'); // 'like' or 'match'
    const currentImageIndex = ref(0);
    const activeTab = ref('about');
    const isLiking = ref(false);

    // Gallery state
    const isGalleryOpen = ref(false);
    const galleryIndex = ref(0);

    // Expanded sections
    const expandedSections = ref(new Set());

    // Functions for block and report
    const blockUser = () => {
        if (confirm('Are you sure you want to block this user?')) {
            // Use the ReportController block method
            router.post(
                route('reports.block'),
                {
                    user_id: props.id,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        alert('User blocked successfully');
                    },
                    onError: errors => {
                        console.error(errors);
                        alert('Error blocking user');
                    },
                }
            );
        }
    };

    const closeModal = () => {
        showReportModal.value = false;
    };

    const openReportModal = () => {
        showReportModal.value = true;
    };

    // Handle like button click
    const handleLike = async () => {
        if (isLiking.value) return; // Prevent double-clicking
        isLiking.value = true;
        
        try {
            // Set up AbortController for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

            const response = await fetch(route('matches.like', { user: props.id }), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({}),
                signal: controller.signal // Add timeout signal
            });

            clearTimeout(timeoutId); // Clear timeout if request completes

            const data = await response.json();

            if (response.ok && data.success) {
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
            } else if (data.already_liked) {
                // Show modal for already liked case
                likeModalType.value = 'already_liked';
                showLikeModal.value = true;
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
            isLiking.value = false;
        }
    };

    // Handle message button click
    const handleMessage = () => {
        window.location.href = route('messages.show', props.id);
    };

    // Close like modal
    const closeLikeModal = () => {
        showLikeModal.value = false;
    };

    // Handle message from modal
    const handleMessageFromModal = () => {
        closeLikeModal();
        handleMessage();
    };

    // Check if user can see message button
    const canShowMessageButton = computed(() => {
        // If users are matched, always show message button
        if (props.isMatched) {
            return true;
        }
        
        // If user is Platinum or Gold, show message button
        const userTier = props.userTier || 'free';
        return userTier === 'platinum' || userTier === 'gold';
    });

    // Check if user can unblur photos based on the target user's blur settings
    const canUnblurPhotos = computed(() => {
        const user = props.userData;
        if (!user) return true;
        
        // If photos are not blurred, always allow viewing
        if (!user.photos_blurred) {
            return true;
        }
        
        // If blur mode is 'auto', allow unblurring
        if (user.photo_blur_mode === 'auto') {
            return true;
        }
        
        // If blur mode is 'manual' or not set, don't allow automatic unblurring
        return false;
    });

    const props = defineProps({
        id: [Number, String],
        userData: Object,
        compatibility: Number,
        auth: Object,
        userTier: String,
        isMatched: Boolean,
    });

    // Process user data to match the expected format in the template
    const profile = computed(() => {
        const user = props.userData;
        if (!user) return {};

        // Calculate age from date of birth if available
        let age = '';
        if (user.dob_day && user.dob_month && user.dob_year) {
            // Convert month name to month number
            const monthMap = {
                Jan: 0,
                Feb: 1,
                Mar: 2,
                Apr: 3,
                May: 4,
                Jun: 5,
                Jul: 6,
                Aug: 7,
                Sep: 8,
                Oct: 9,
                Nov: 10,
                Dec: 11,
            };

            const month = monthMap[user.dob_month] || 0;
            const birthDate = new Date(user.dob_year, month, user.dob_day);
            const today = new Date();
            age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
        }

        // Format location from city, state, country
        let location = '';
        if (user.city) location += user.city;
        if (user.state) {
            if (location) location += ', ';
            location += user.state;
        }
        if (user.country) {
            if (location) location += ', ';
            location += user.country;
        }

        // Get profile photo - check if user has photos and a primary photo
        let image = '/images/placeholder.jpg';
        let allPhotos = [];

        if (user.photos && user.photos.length > 0) {
            // Store all photos for the gallery
            allPhotos = user.photos.map(photo => photo.url);

            // Find primary photo if exists
            const primaryPhoto = user.photos.find(photo => photo.is_primary);
            if (primaryPhoto) {
                image = primaryPhoto.url;
            } else if (user.photos[0]) {
                // Otherwise use the first photo
                image = user.photos[0].url;
            }
        } else if (user.profile_photo) {
            image = user.profile_photo;
            allPhotos = [image];
        }



        // Extract appearance data
        const appearance = {
            hairColor: user.appearance?.hair_color || 'Not specified',
            eyeColor: user.appearance?.eye_color || 'Not specified',
            height: user.appearance?.height || 'Not specified',
            weight: user.appearance?.weight || 'Not specified',
            bodyStyle: user.appearance?.body_type || 'Not specified',
            ethnicity: user.appearance?.ethnicity || 'Not specified',
            style: user.appearance?.style || 'Not specified',
        };

        // Extract overview data
        const overview = {
            educationLevel: user.overview?.education_level || 'Not specified',
            employmentStatus: user.overview?.employment_status || 'Not specified',
            incomeRange: user.overview?.income_range || 'Not specified',
            religion: user.overview?.religion || 'Not specified',
            maritalStatus: user.overview?.marital_status || 'Not specified',
        };

        // Extract lifestyle data
        const lifestyle = {
            smoke: user.lifestyle?.smoking || user.lifestyle?.smokes || 'Not specified',
            drinks: user.lifestyle?.drinks || 'Not specified',
            haveChildren: user.lifestyle?.has_children || 'Not specified',
            numberOfChildren: user.lifestyle?.number_of_children || 0,
            occupation: user.lifestyle?.occupation || 'Not specified',
        };

        // Extract hobbies and interests
        const hobbies = [];
        const sports = [];

        if (user.interests && user.interests.entertainment) {
            hobbies.push(...user.interests.entertainment.split(',').map(item => item.trim()));
        }

        if (user.interests && user.interests.sports) {
            sports.push(...user.interests.sports.split(',').map(item => item.trim()));
        }

        // Extract favorite movies from personality
        const favoriteMovies = [];
        if (user.personality && user.personality.favoriteMovie1) {
            favoriteMovies.push(user.personality.favoriteMovie1);
        }
        if (user.personality && user.personality.favoriteMovie2) {
            favoriteMovies.push(user.personality.favoriteMovie2);
        }

        return {
            id: user.id,
            name: user.name || 'Anonymous',
            age: age || '?',
            verified: user.is_verified || false,
            location: location || 'Location not specified',
            online: true, // This would be dynamic in a real app
            image: image,
            photos: allPhotos,
            compatibility: props.compatibility || 85,

            profileHeading: user.about?.heading || '',
            aboutMe: user.about?.about_me || 'No information provided.',
            education: {
                level: overview.educationLevel,
            },
            employment: {
                status: overview.employmentStatus,
            },
            religion: {
                name: overview.religion,
            },
            income: {
                range: overview.incomeRange,
            },
            drink: {
                preference: user.lifestyle?.drinking || 'Not specified',
            },
            maritalStatus: {
                status: overview.maritalStatus,
            },
            appearance: appearance,
            lifestyle: lifestyle,
            hobbies: hobbies.length > 0 ? hobbies : ['No hobbies specified'],
            sports: sports.length > 0 ? sports : ['No sports specified'],
            favoriteMovies: favoriteMovies.length > 0 ? favoriteMovies : ['No favorite movies specified'],
        };
    });

    // State for active tab
    const setActiveTab = tab => {
        activeTab.value = tab;
    };

    // Photo gallery state
    const showPhotoGallery = ref(false);
    const currentPhotoIndex = ref(0);

    // Open photo gallery with specific photo
    const openPhotoGallery = index => {
        currentPhotoIndex.value = index;
        showPhotoGallery.value = true;
        document.body.classList.add('overflow-hidden'); // Prevent scrolling when gallery is open
    };

    // Close photo gallery
    const closePhotoGallery = () => {
        showPhotoGallery.value = false;
        document.body.classList.remove('overflow-hidden');
    };

    // Navigate to next photo
    const nextPhoto = () => {
        if (profile.value.photos && profile.value.photos.length > 0) {
            currentPhotoIndex.value = (currentPhotoIndex.value + 1) % profile.value.photos.length;
        }
    };

    // Navigate to previous photo
    const prevPhoto = () => {
        if (profile.value.photos && profile.value.photos.length > 0) {
            currentPhotoIndex.value =
                (currentPhotoIndex.value - 1 + profile.value.photos.length) % profile.value.photos.length;
        }
    };

    // Handle keyboard navigation in gallery
    const handleKeyDown = e => {
        if (!showPhotoGallery.value) return;

        if (e.key === 'ArrowRight') {
            nextPhoto();
        } else if (e.key === 'ArrowLeft') {
            prevPhoto();
        } else if (e.key === 'Escape') {
            closePhotoGallery();
        }
    };

    // Handle photo unblurred event
    const handlePhotoUnblurred = (userId) => {
        
        // You might want to update UI state or show a message
    };

    // Privacy modal state
    const showPrivacyModal = ref(false);

    // Handle photo click - check if should open gallery or show privacy message
    const handlePhotoClick = (index = 0) => {
        // Check if photos are blurred and mode is manual - show warning instead
        if (props.userData?.photos_blurred && props.userData?.photo_blur_mode === 'manual') {
            showPrivacyModal.value = true;
            return;
        }
        
        // If photos are not blurred or mode is auto, allow gallery viewing
        openPhotoGallery(index);
    };

    // Close privacy modal
    const closePrivacyModal = () => {
        showPrivacyModal.value = false;
    };

    // Toggle sections - only apply on mobile/tablet
    const toggleSection = sectionId => {
        // Only apply toggle behavior on mobile/tablet screens
        if (window.innerWidth < 1024) {
            // 1024px is the lg breakpoint in Tailwind
            const section = document.getElementById(sectionId);
            if (section) {
                // Toggle the open class for animation
                section.classList.toggle('open');

                // Toggle aria-expanded attribute for accessibility
                const button = section.previousElementSibling;
                if (button) {
                    const isExpanded = section.classList.contains('open');
                    button.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

                    // Rotate the arrow icon
                    const arrow = button.querySelector('svg');
                    if (arrow) {
                        arrow.style.transform = isExpanded ? 'rotate(180deg)' : '';
                        arrow.style.transition = 'transform 0.3s';
                    }
                }
            }
        }
    };

    // Mobile menu state
    const isMobileMenuOpen = ref(false);

    // Toggle mobile menu
    const toggleMobileMenu = () => {
        isMobileMenuOpen.value = !isMobileMenuOpen.value;
    };

    // Handle window resize to adjust section visibility based on screen size
    const handleResize = () => {
        // If resized to desktop, expand all sections
        if (window.innerWidth >= 1024) {
            const sections = ['overview', 'appearance', 'lifestyle', 'background', 'others', 'hobbies', 'sports'];
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.classList.add('open');
                    const button = section.previousElementSibling;
                    if (button) {
                        button.setAttribute('aria-expanded', 'true');
                    }
                }
            });
        }
    };

    // Clean up event listeners when component is unmounted
    onUnmounted(() => {
        window.removeEventListener('resize', handleResize);
    });

    // Initialize sections on page load
    onMounted(() => {
        // If on desktop, make sure all sections are expanded
        if (window.innerWidth >= 1024) {
            const sections = ['overview', 'appearance', 'lifestyle', 'background', 'others', 'hobbies', 'sports'];
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.classList.add('open');
                    const button = section.previousElementSibling;
                    if (button) {
                        button.setAttribute('aria-expanded', 'true');
                    }
                }
            });
        } else {
            // On mobile, ensure all sections start collapsed
            const sections = ['overview', 'appearance', 'lifestyle', 'background', 'others', 'hobbies', 'sports'];
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.classList.remove('open');
                    const button = section.previousElementSibling;
                    if (button) {
                        button.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        }

        // Add resize listener to handle desktop/mobile transitions
        window.addEventListener('resize', handleResize);

        // Add keyboard event listener for photo gallery navigation
        window.addEventListener('keydown', handleKeyDown);
    });
</script>

<template>
    <Head title="Profile View" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-4 flex items-center justify-between md:hidden">
            <button @click="toggleMobileMenu" class="p-1" aria-label="Toggle menu">
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: !isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <h1 class="text-lg font-bold">Profile View</h1>

            <Link :href="route('dashboard')" class="flex items-center text-gray-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    />
                </svg>
            </Link>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar - Hidden on mobile until menu button is clicked -->
        <aside
            class="fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:translate-x-0"
            :class="{ 'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen }"
        >
            <Sidebar :user="$page.props.auth.user" />
        </aside>

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8 mt-16 md:mt-0 md:ml-64">
            <!-- Welcome and Search - Only visible on desktop -->
            <div class="mb-6 md:mb-8 hidden md:block">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold">Welcome {{ $page.props.auth.user.name }}!</h1>
                </div>

                <!-- Search bar with integrated filter button - desktop -->
                <div class="flex items-center rounded-lg border border-gray-300 bg-white">
                    <div class="flex-1 flex items-center px-4 py-2">
                        <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            type="text"
                            placeholder="Search"
                            class="w-full border-none bg-transparent outline-none"
                        />
                    </div>
                    <div class="border-l border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-50">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Back Button - Only visible on desktop -->
            <div class="mb-6 hidden md:block">
                <Link :href="route('dashboard')" class="flex items-center text-gray-700">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    <span class="font-bold">Back to Matches</span>
                </Link>
            </div>

            <!-- Profile View -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Profile Header -->
                <div class="relative">
                    <!-- Profile Image and Info Section - Responsive layout -->
                    <div class="flex flex-col md:flex-row">
                        <!-- Profile Image Section -->
                        <div class="relative w-full md:w-1/3">
                            <!-- Mobile: Image with overlay info -->
                            <div class="md:hidden relative">
                                <div class="w-full h-80 bg-gray-200 relative">
                                    <PhotoBlurControl
                                        :imageUrl="profile.image"
                                        :isBlurred="userData?.photos_blurred || false"
                                        :canUnblur="canUnblurPhotos"
                                        :userId="profile.id"
                                        size="full"
                                        @unblurred="handlePhotoUnblurred"
                                        @click="handlePhotoClick(0)"
                                    />

                                    <!-- Online Status -->
                                    <div
                                        v-if="profile.online"
                                        class="absolute left-4 top-4 flex items-center rounded-full bg-black bg-opacity-70 px-3 py-1 text-sm text-white"
                                    >
                                        <span class="mr-2 h-2 w-2 rounded-full bg-green-500"></span>
                                        Online
                                    </div>

                                    <!-- Verified Badge -->
                                    <div class="absolute top-4 right-4 bg-amber-500 rounded-full p-2">
                                        <svg
                                            class="h-6 w-6 text-white"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </div>

                                    <!-- Photo Gallery Button -->
                                    <div
                                        v-if="profile.photos && profile.photos.length > 1"
                                        class="absolute bottom-4 right-4 bg-white bg-opacity-80 rounded-full p-2 cursor-pointer"
                                        @click="handlePhotoClick(0)"
                                    >
                                        <svg
                                            class="h-5 w-5 text-gray-800"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>

                                    <!-- Mobile: Name and Info Overlay -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                        <div class="text-white">
                                            <div class="flex items-center gap-2">
                                                <h2 class="text-xl font-bold">{{ profile.name }}, {{ profile.age }}</h2>
                                                <TierBadge :tier="userData?.tier || targetUserTier?.name?.toLowerCase() || 'free'" size="xs" />
                                            </div>
                                            <p class="text-gray-200 text-sm">{{ profile.location }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile: Compatibility indicator -->
                                <div class="p-3 bg-gray-50 flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div
                                            class="bg-amber-500 h-2.5 rounded-full"
                                            :style="`width: ${profile.compatibility}%`"
                                        ></div>
                                    </div>
                                    <span class="ml-2 text-sm font-medium">{{ profile.compatibility }}%</span>
                                    <span class="ml-1 text-sm text-gray-500">Match</span>
                                </div>
                            </div>

                            <!-- Desktop: Original layout -->
                            <div class="hidden md:block">
                                <div class="w-full aspect-square bg-gray-200 relative">
                                    <PhotoBlurControl
                                        :imageUrl="profile.image"
                                        :isBlurred="userData?.photos_blurred || false"
                                        :canUnblur="canUnblurPhotos"
                                        :userId="profile.id"
                                        size="full"
                                        @unblurred="handlePhotoUnblurred"
                                        @click="handlePhotoClick(0)"
                                    />

                                    <!-- Online Status -->
                                    <div
                                        v-if="profile.online"
                                        class="absolute left-4 top-4 flex items-center rounded-full bg-black bg-opacity-70 px-3 py-1 text-sm text-white"
                                    >
                                        <span class="mr-2 h-2 w-2 rounded-full bg-green-500"></span>
                                        Online
                                    </div>

                                    <!-- Verified Badge -->
                                    <div class="absolute top-4 right-4 bg-amber-500 rounded-full p-2">
                                        <svg
                                            class="h-6 w-6 text-white"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </div>

                                    <!-- Photo Gallery Button -->
                                    <div
                                        v-if="profile.photos && profile.photos.length > 1"
                                        class="absolute bottom-4 right-4 bg-white bg-opacity-80 rounded-full p-2 cursor-pointer"
                                        @click="handlePhotoClick(0)"
                                    >
                                        <svg
                                            class="h-6 w-6 text-gray-800"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Desktop: Compatibility indicator -->
                                <div class="p-2 bg-gray-50 flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div
                                            class="bg-amber-500 h-2.5 rounded-full"
                                            :style="`width: ${profile.compatibility}%`"
                                        ></div>
                                    </div>
                                    <span class="ml-2 text-sm font-medium">{{ profile.compatibility }}%</span>
                                    <span class="ml-1 text-sm text-gray-500">Match</span>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Information Section -->
                        <div class="flex-1 p-4 md:p-6">
                            <!-- Mobile: Action buttons at top -->
                            <div class="md:hidden flex justify-center space-x-3 mb-4">
                                <button
                                    @click="handleLike"
                                    :disabled="isLiking"
                                    class="flex items-center justify-center px-6 py-3 bg-purple-600 text-white rounded-full hover:bg-purple-700 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
                                >
                                    <!-- Loading spinner -->
                                    <svg v-if="isLiking" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <!-- Heart icon -->
                                    <svg v-else class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    {{ isLiking ? 'Liking...' : 'Like' }}
                                </button>
                                <button
                                    v-if="canShowMessageButton"
                                    @click="handleMessage"
                                    class="flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors"
                                >
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                        />
                                    </svg>
                                    Message
                                </button>
                            </div>

                            <!-- Desktop: Header with action buttons -->
                            <div class="hidden md:flex items-center justify-between mb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-2xl font-bold">{{ profile.name }}, {{ profile.age }}</h2>
                                        <TierBadge :tier="userData?.tier || targetUserTier?.name?.toLowerCase() || 'free'" size="sm" />
                                    </div>
                                    <p class="text-gray-600">{{ profile.location }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        @click="handleLike"
                                        :disabled="isLiking"
                                        class="text-purple-800 border border-purple-800 rounded-full p-2 bg-white hover:bg-purple-50 disabled:opacity-70 disabled:cursor-not-allowed relative"
                                    >
                                        <!-- Loading spinner -->
                                        <svg v-if="isLiking" class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <!-- Heart icon -->
                                        <svg v-else class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-if="canShowMessageButton"
                                        @click="handleMessage"
                                        class="text-purple-800 border border-purple-800 rounded-full p-2 bg-white hover:bg-purple-50"
                                    >
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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



                            <!-- About Me section -->
                            <div class="mb-4">
                                <h3 class="text-lg font-bold mb-2">About Me</h3>
                                <h4 v-if="profile.profileHeading" class="text-md font-semibold mb-2 text-gray-800">
                                    {{ profile.profileHeading }}
                                </h4>
                                <p class="text-gray-700 leading-relaxed">{{ profile.aboutMe }}</p>
                            </div>

                            <!-- Block/Report buttons -->
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                                <button
                                    @click="blockUser"
                                    class="flex-1 flex items-center justify-center py-2 px-4 border border-red-500 text-red-500 rounded-md hover:bg-red-50 transition-colors"
                                >
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M18.364 17.364L5.636 4.636m0 12.728L18.364 4.636"
                                        />
                                    </svg>
                                    Block
                                </button>
                                <button
                                    @click="openReportModal"
                                    class="flex-1 flex items-center justify-center py-2 px-4 border border-red-500 text-red-500 rounded-md hover:bg-red-50 transition-colors"
                                >
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"
                                        />
                                    </svg>
                                    Block and Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Tabs - Fixed at the bottom on mobile -->
                <div class="border-t border-gray-200 sticky top-0 z-10 bg-white">
                    <div class="grid grid-cols-2">
                        <button
                            @click="setActiveTab('about')"
                            class="py-4 text-center font-medium transition duration-200"
                            :class="activeTab === 'about' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'"
                        >
                            About Me
                        </button>
                        <button
                            @click="setActiveTab('searching')"
                            class="py-4 text-center font-medium transition duration-200"
                            :class="activeTab === 'searching' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'"
                        >
                            Searching For
                        </button>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="p-4" v-if="activeTab === 'about'">
                    <!-- Overview Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-4 bg-white cursor-pointer"
                            @click="toggleSection('overview')"
                            role="button"
                            aria-expanded="false"
                            aria-controls="overview"
                        >
                            <h3 class="text-lg font-bold">Overview</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div id="overview" class="bg-white section-content">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4">
                                <div>
                                    <p class="text-gray-500">Education</p>
                                    <p class="font-medium">{{ profile.education.level }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Employment Status</p>
                                    <p class="font-medium">{{ profile.employment.status }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Annual Income</p>
                                    <p class="font-medium">{{ profile.income.range }}</p>
                                </div>

                                <div>
                                    <p class="text-gray-500">Religion</p>
                                    <p class="font-medium">{{ profile.religion.name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Marital Status</p>
                                    <p class="font-medium">{{ profile.maritalStatus.status }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-4 bg-white cursor-pointer"
                            @click="toggleSection('appearance')"
                            role="button"
                            aria-expanded="false"
                            aria-controls="appearance"
                        >
                            <h3 class="text-lg font-bold">Appearance</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div id="appearance" class="bg-white section-content">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4">
                                <div>
                                    <p class="text-gray-500">Hair Color</p>
                                    <p class="font-medium">{{ profile.appearance.hairColor }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Eye Color</p>
                                    <p class="font-medium">{{ profile.appearance.eyeColor }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Weight</p>
                                    <p class="font-medium">{{ profile.appearance.weight }}</p>
                                </div>

                                <div>
                                    <p class="text-gray-500">Height</p>
                                    <p class="font-medium">{{ profile.appearance.height }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lifestyle Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-4 bg-white cursor-pointer"
                            @click="toggleSection('lifestyle')"
                            role="button"
                            aria-expanded="false"
                            aria-controls="lifestyle"
                        >
                            <h3 class="text-lg font-bold">Life Style</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div id="lifestyle" class="bg-white section-content">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4">
                                <div>
                                    <p class="text-gray-500">Do you smoke?</p>
                                    <p class="font-medium">{{ profile.lifestyle.smoke }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Do you drink?</p>
                                    <p class="font-medium">{{ profile.lifestyle.drinks }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Do you have children?</p>
                                    <p class="font-medium">{{ profile.lifestyle.haveChildren }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Number of children</p>
                                    <p class="font-medium">{{ profile.lifestyle.numberOfChildren }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Do you want to have children?</p>
                                    <p class="font-medium">{{ props.userData.lifestyle?.want_children || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Occupation</p>
                                    <p class="font-medium">{{ profile.lifestyle.occupation }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hobbies Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-4 bg-white cursor-pointer"
                            @click="toggleSection('hobbies')"
                            role="button"
                            aria-expanded="false"
                            aria-controls="hobbies"
                        >
                            <h3 class="text-lg font-bold">Hobbies & Interests</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div id="hobbies" class="bg-white section-content">
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="hobby in profile.hobbies"
                                    :key="hobby"
                                    class="px-3 py-1 bg-gray-100 rounded-full text-sm"
                                >
                                    {{ hobby }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Sports Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-4 bg-white cursor-pointer"
                            @click="toggleSection('sports')"
                            role="button"
                            aria-expanded="false"
                            aria-controls="sports"
                        >
                            <h3 class="text-lg font-bold">Sports</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div id="sports" class="bg-white section-content">
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="sport in profile.sports"
                                    :key="sport"
                                    class="px-3 py-1 bg-gray-100 rounded-full text-sm"
                                >
                                    {{ sport }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Background & Culture Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-4 bg-white cursor-pointer"
                            @click="toggleSection('background')"
                            role="button"
                            aria-expanded="false"
                            aria-controls="background"
                        >
                            <h3 class="text-lg font-bold">Background & Culture</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div id="background" class="bg-white section-content">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4">
                                <div>
                                    <p class="text-gray-500">Nationality</p>
                                    <p class="font-medium">{{ props.userData.background?.nationality || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Ethnic Group / Tribe</p>
                                    <p class="font-medium">{{ props.userData.background?.ethnic_group || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Islamic Affiliation</p>
                                    <p class="font-medium">{{ props.userData.background?.islamic_affiliation || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Education</p>
                                    <p class="font-medium">{{ props.userData.background?.education || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Language spoken</p>
                                    <p class="font-medium">{{ props.userData.background?.language_spoken || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Born / Reverted</p>
                                    <p class="font-medium">{{ props.userData.background?.born_reverted || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Read Quran</p>
                                    <p class="font-medium">{{ props.userData.background?.read_quran || 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Others Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-4 bg-white cursor-pointer"
                            @click="toggleSection('others')"
                            role="button"
                            aria-expanded="false"
                            aria-controls="others"
                        >
                            <h3 class="text-lg font-bold">Others</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                        <div id="others" class="bg-white section-content">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4">
                                <div>
                                    <p class="text-gray-500">Are you open to polygamy?</p>
                                    <p class="font-medium">{{ props.userData.others?.open_to_polygamy || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Do you own your business or have financial stability?</p>
                                    <p class="font-medium">{{ props.userData.others?.financial_stability || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">When are you ready to get married?</p>
                                    <p class="font-medium">{{ props.userData.others?.ready_to_marry || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Genotype</p>
                                    <p class="font-medium">{{ props.userData.others?.genotype || 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- More About Me -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold mb-2">More About Me</h3>
                        <p class="text-gray-700">
                            <span class="font-medium">Favorite Movies:</span>
                            {{ profile.favoriteMovies.join(', ') }}
                        </p>
                    </div>
                </div>

                <!-- Searching For Content -->
                <div class="p-4" v-if="activeTab === 'searching'">
                    <!-- Partner Preferences Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
                        <h3 class="text-lg font-bold mb-4">Partner Preferences</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4">
                            <div>
                                <p class="text-gray-500">Age Range</p>
                                <p
                                    class="font-medium"
                                    v-if="
                                        props.userData.about?.looking_for_age_min ||
                                        props.userData.about?.looking_for_age_max
                                    "
                                >
                                    {{ props.userData.about?.looking_for_age_min || '?' }} to
                                    {{ props.userData.about?.looking_for_age_max || '?' }} years
                                </p>
                                <p class="font-medium text-gray-400" v-else>Not specified</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Education Level</p>
                                <p class="font-medium">{{ props.userData.about?.looking_for_education || 'Any' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Religious Practice</p>
                                <p class="font-medium">
                                    {{ props.userData.about?.looking_for_religious_practice || 'Any' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Marital Status</p>
                                <p class="font-medium">
                                    {{ props.userData.about?.looking_for_marital_status || 'Any' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Open to relocating for marriage?</p>
                                <p class="font-medium">{{ props.userData.about?.looking_for_relocate || 'Not specified' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Want to have children?</p>
                                <p class="font-medium">{{ props.userData.about?.looking_for_children || 'Not specified' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Preferred Tribe or Ethnic Group</p>
                                <p class="font-medium">{{ props.userData.about?.looking_for_tribe || 'Open to all' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- What you're looking for in a partner - Separate Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6" v-if="props.userData.about?.looking_for">
                        <h3 class="text-lg font-bold mb-4">What I'm Looking For in a Partner</h3>
                        <p class="font-medium">{{ props.userData.about?.looking_for }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <ReportModal :show="showReportModal" :userId="id" :userName="profile.name" @close="closeModal" />

    <!-- Like Success Modal -->
    <LikeSuccessModal :show="showLikeModal" :type="likeModalType" :userName="profile.name" :canShowMessageButton="canShowMessageButton" @close="closeLikeModal" @message="handleMessageFromModal" />

    <!-- Privacy Modal -->
    <div v-if="showPrivacyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <div class="flex items-center mb-4">
                <svg class="h-8 w-8 text-purple-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Photos are Private</h3>
            </div>
            
            <p class="text-gray-600 mb-6">
                {{ profile.name }} has chosen to keep their photos private. You cannot view their photo gallery in full screen mode.
            </p>
            
            <div class="flex justify-end">
                <button 
                    @click="closePrivacyModal"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors"
                >
                    Understood
                </button>
            </div>
        </div>
    </div>

    <!-- Photo Gallery Modal -->
    <div v-if="showPhotoGallery" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90">
        <!-- Close button -->
        <button @click="closePhotoGallery" class="absolute top-4 right-4 text-white p-2 rounded-full hover:bg-gray-800">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Previous button -->
        <button
            v-if="profile.photos && profile.photos.length > 1"
            @click="prevPhoto"
            class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white p-2 rounded-full hover:bg-gray-800"
        >
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Next button -->
        <button
            v-if="profile.photos && profile.photos.length > 1"
            @click="nextPhoto"
            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white p-2 rounded-full hover:bg-gray-800"
        >
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Photo display -->
        <div class="w-full max-w-4xl max-h-full p-4">
            <div v-if="profile.photos && profile.photos[currentPhotoIndex]" class="max-h-screen max-w-full mx-auto flex items-center justify-center">
                <div class="max-h-[80vh] max-w-[80vw]">
                    <PhotoBlurControl
                        :imageUrl="profile.photos[currentPhotoIndex]"
                        :isBlurred="userData?.photos_blurred || false"
                        :canUnblur="canUnblurPhotos"
                        :userId="profile.id"
                        size="full"
                        @unblurred="handlePhotoUnblurred"
                    />
                </div>
            </div>

            <!-- Photo counter -->
            <div
                v-if="profile.photos && profile.photos.length > 1"
                class="absolute bottom-4 left-0 right-0 text-center text-white"
            >
                {{ currentPhotoIndex + 1 }} / {{ profile.photos.length }}
            </div>
        </div>
    </div>
</template>

<style scoped>
    .text-rose-500 {
        color: #f43f5e;
    }

    /* Mobile-specific styles */
    @media (max-width: 1023px) {
        .min-h-screen {
            padding-top: 1rem;
        }

        /* Make section toggles more touch-friendly */
        .cursor-pointer {
            padding: 1rem;
        }

        /* Animation for section toggles - only on mobile/tablet */
        .section-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            padding: 0;
            border-top: 0;
            opacity: 0;
        }

        .section-content.open {
            max-height: 2000px; /* Large enough to show all content */
            padding: 1rem;
            border-top: 1px solid #e5e7eb; /* gray-200 */
            opacity: 1;
            transition:
                max-height 0.5s ease-in,
                opacity 0.3s ease-in;
        }

        /* Show toggle arrows only on mobile/tablet */
        .cursor-pointer svg {
            display: block;
        }
    }

    /* Desktop-specific styles */
    @media (min-width: 1024px) {
        /* Hide toggle arrows on desktop */
        .cursor-pointer svg {
            display: none;
        }

        /* Always show content on desktop */
        .section-content {
            max-height: none !important;
            overflow: visible !important;
            padding: 1rem !important;
            border-top: 1px solid #e5e7eb !important; /* gray-200 */
            opacity: 1 !important;
        }

        /* Remove pointer cursor on desktop since sections aren't toggleable */
        .cursor-pointer {
            cursor: default;
        }

        /* Style section headers as static headings on desktop */
        .cursor-pointer {
            background-color: #f9fafb !important; /* gray-50 */
            border-bottom: 1px solid #e5e7eb; /* gray-200 */
        }

        /* Add some spacing between sections on desktop */
        .border.border-gray-200.rounded-lg.mb-4 {
            margin-bottom: 2rem;
        }
    }

    /* Photo Gallery Styles */
    img.object-cover {
        cursor: pointer;
    }

    .fixed.inset-0.z-50 {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Make buttons more visible on hover */
    .fixed.inset-0.z-50 button {
        transition: all 0.2s ease;
        opacity: 0.7;
    }

    .fixed.inset-0.z-50 button:hover {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>
