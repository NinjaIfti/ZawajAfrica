<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    id: Number,
    userData: Object,
    compatibility: Number,
    auth: Object
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
            'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3,
            'May': 4, 'Jun': 5, 'Jul': 6, 'Aug': 7,
            'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
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
    
    // Format seeking text based on interested_in and profile data
    let seeking = '';
    if (user.interested_in) {
        seeking = `Seeking a ${user.interested_in === 'Male' ? 'male' : 'female'}`;
        
        // Add age range if available in profile
        if (user.profile && user.profile.age_preference_min && user.profile.age_preference_max) {
            seeking += ` ${user.profile.age_preference_min}-${user.profile.age_preference_max}`;
        }
    }
    
    // Extract appearance data
    const appearance = {
        hairColor: user.appearance?.hair_color || 'Not specified',
        eyeColor: user.appearance?.eye_color || 'Not specified',
        height: user.appearance?.height || 'Not specified',
        weight: user.appearance?.weight || 'Not specified',
        bodyStyle: user.appearance?.body_type || 'Not specified',
        ethnicity: user.appearance?.ethnicity || 'Not specified',
        style: user.appearance?.style || 'Not specified'
    };
    
    // Extract overview data
    const overview = {
        educationLevel: user.overview?.education_level || 'Not specified',
        employmentStatus: user.overview?.employment_status || 'Not specified',
        incomeRange: user.overview?.income_range || 'Not specified',
        religion: user.overview?.religion || 'Not specified',
        maritalStatus: user.overview?.marital_status || 'Not specified'
    };
    

    
    // Extract lifestyle data
    const lifestyle = {
        smoke: user.lifestyle?.smoking || user.lifestyle?.smokes || "Not specified",
        drinks: user.lifestyle?.drinks || "Not specified",
        haveChildren: user.lifestyle?.has_children || "Not specified",
        numberOfChildren: user.lifestyle?.number_of_children || 0,
        occupation: user.lifestyle?.occupation || "Not specified"
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
        seeking: seeking || 'Seeking a partner',
        profileHeading: user.about?.heading || "",
        aboutMe: user.about?.about_me || "No information provided.",
        education: {
            level: overview.educationLevel
        },
        employment: {
            status: overview.employmentStatus
        },
        religion: {
            name: overview.religion
        },
        income: {
            range: overview.incomeRange
        },
        drink: {
            preference: user.lifestyle?.drinking || "Not specified"
        },
        maritalStatus: {
            status: overview.maritalStatus
        },
        appearance: appearance,
        lifestyle: lifestyle,
        hobbies: hobbies.length > 0 ? hobbies : ['No hobbies specified'],
        sports: sports.length > 0 ? sports : ['No sports specified'],
        favoriteMovies: favoriteMovies.length > 0 ? favoriteMovies : ['No favorite movies specified']
    };
});

// State for active tab
const activeTab = ref('about');
const setActiveTab = (tab) => {
    activeTab.value = tab;
};

// Photo gallery state
const showPhotoGallery = ref(false);
const currentPhotoIndex = ref(0);

// Open photo gallery with specific photo
const openPhotoGallery = (index) => {
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
        currentPhotoIndex.value = (currentPhotoIndex.value - 1 + profile.value.photos.length) % profile.value.photos.length;
    }
};

// Handle keyboard navigation in gallery
const handleKeyDown = (e) => {
    if (!showPhotoGallery.value) return;
    
    if (e.key === 'ArrowRight') {
        nextPhoto();
    } else if (e.key === 'ArrowLeft') {
        prevPhoto();
    } else if (e.key === 'Escape') {
        closePhotoGallery();
    }
};

// Toggle sections - only apply on mobile/tablet
const toggleSection = (sectionId) => {
    // Only apply toggle behavior on mobile/tablet screens
    if (window.innerWidth < 1024) { // 1024px is the lg breakpoint in Tailwind
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
        const sections = ['overview', 'appearance', 'lifestyle', 'hobbies', 'sports'];
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
        const sections = ['overview', 'appearance', 'lifestyle', 'hobbies', 'sports'];
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
        const sections = ['overview', 'appearance', 'lifestyle', 'hobbies', 'sports'];
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
            <button 
                @click="toggleMobileMenu" 
                class="p-1"
                aria-label="Toggle menu"
            >
                <svg 
                    class="h-6 w-6 text-gray-700" 
                    :class="{ 'hidden': isMobileMenuOpen }"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg 
                    class="h-6 w-6 text-gray-700" 
                    :class="{ 'hidden': !isMobileMenuOpen }"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
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
            class="fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:relative md:z-0 md:translate-x-0"
            :class="{'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen}"
        >
        <Sidebar :user="$page.props.auth.user" />
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8 mt-16 md:mt-0">
            <!-- Welcome and Search - Only visible on desktop -->
            <div class="mb-6 md:mb-8 hidden md:block">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold">Welcome {{ $page.props.auth.user.name }}!</h1>
                </div>
                
                <!-- Search bar with integrated filter button - desktop -->
                <div class="flex items-center rounded-lg border border-gray-300 bg-white">
                    <div class="flex-1 flex items-center px-4 py-2">
                        <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" placeholder="Search" class="w-full border-none bg-transparent outline-none" />
                    </div>
                    <div class="border-l border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-50">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Back Button - Only visible on desktop -->
            <div class="mb-6 hidden md:block">
                <Link :href="route('dashboard')" class="flex items-center text-gray-700">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="font-bold">Back to Matches</span>
                </Link>
            </div>
            
            <!-- Profile View -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Profile Header -->
                <div class="relative">
                    <!-- Profile Image and Info Section - Restructured with image on left for desktop, stacked for mobile -->
                    <div class="flex flex-col md:flex-row">
                        <!-- Profile Image (Full width on mobile, left side on desktop) -->
                        <div class="relative w-full md:w-1/2">
                            <div class="w-full h-72 md:h-full bg-gray-200 relative">
                                <img :src="profile.image" alt="Profile Image" class="w-full h-full object-cover" @click="openPhotoGallery(0)">
                                
                                <!-- Online Status -->
                                <div v-if="profile.online" class="absolute left-4 top-4 flex items-center rounded-full bg-black bg-opacity-70 px-3 py-1 text-sm text-white">
                                    <span class="mr-2 h-2 w-2 rounded-full bg-green-500"></span>
                                    Online
                                </div>
                                
                                <!-- Verified Badge -->
                                <div class="absolute top-4 right-4 bg-amber-500 rounded-full p-2">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                
                                <!-- Photo Gallery Button - if user has multiple photos -->
                                <div v-if="profile.photos && profile.photos.length > 1" 
                                     class="absolute bottom-4 right-4 bg-white bg-opacity-80 rounded-full p-2 cursor-pointer"
                                     @click="openPhotoGallery(0)">
                                    <svg class="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Interaction Buttons - Fixed position on mobile, absolute on desktop -->
                            <div class="flex space-x-3 justify-center p-4 md:p-0 md:absolute md:left-4 md:bottom-4">
                                <button class="text-purple-800 border border-purple-800 rounded-full p-2 bg-white">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button class="text-purple-800 border border-purple-800 rounded-full p-2 bg-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Profile Information (Right Side on desktop, below image on mobile) -->
                        <div class="flex-1 p-6">
                            <div class="flex items-center mb-1">
                                <h2 class="text-2xl font-bold">{{ profile.name }}, {{ profile.age }}</h2>
                                <span v-if="profile.verified" class="ml-2 text-amber-500">✓</span>
                            </div>
                            <p class="text-gray-600 mb-4">{{ profile.location }}</p>
                            <p class="text-rose-500 flex items-center mb-6">
                                <svg class="h-5 w-5 mr-1 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                </svg>
                                {{ profile.seeking }}
                            </p>
                            
                            <h3 class="text-lg font-bold mb-2">About Me</h3>
                            <h4 v-if="profile.profileHeading" class="text-md font-semibold mb-2">{{ profile.profileHeading }}</h4>
                            <p class="text-gray-700 mb-6">{{ profile.aboutMe }}</p>
                            
                            <!-- Profile Actions -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg flex items-center justify-center py-3">
                                    <svg class="h-6 w-6 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    <span class="text-red-500 font-medium">Block</span>
                                </div>
                                <div class="bg-white rounded-lg flex items-center justify-center py-3">
                                    <svg class="h-6 w-6 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                    </svg>
                                    <span class="text-red-500 font-medium">Block and Report</span>
                                </div>
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
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" 
                             @click="toggleSection('overview')"
                             role="button"
                             aria-expanded="false"
                             aria-controls="overview">
                            <h3 class="text-lg font-bold">Overview</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" 
                             @click="toggleSection('appearance')"
                             role="button"
                             aria-expanded="false"
                             aria-controls="appearance">
                            <h3 class="text-lg font-bold">Appearance</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" 
                             @click="toggleSection('lifestyle')"
                             role="button"
                             aria-expanded="false"
                             aria-controls="lifestyle">
                            <h3 class="text-lg font-bold">Life Style</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
                                    <p class="text-gray-500">Occupation</p>
                                    <p class="font-medium">{{ profile.lifestyle.occupation }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hobbies Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer"
                             @click="toggleSection('hobbies')"
                             role="button"
                             aria-expanded="false"
                             aria-controls="hobbies">
                            <h3 class="text-lg font-bold">Hobbies & Interests</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div id="hobbies" class="bg-white section-content">
                            <div class="flex flex-wrap gap-2">
                                <span v-for="hobby in profile.hobbies" :key="hobby" class="px-3 py-1 bg-gray-100 rounded-full text-sm">
                                    {{ hobby }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sports Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer"
                             @click="toggleSection('sports')"
                             role="button"
                             aria-expanded="false"
                             aria-controls="sports">
                            <h3 class="text-lg font-bold">Sports</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div id="sports" class="bg-white section-content">
                            <div class="flex flex-wrap gap-2">
                                <span v-for="sport in profile.sports" :key="sport" class="px-3 py-1 bg-gray-100 rounded-full text-sm">
                                    {{ sport }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- More About Me -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold mb-2">More About Me</h3>
                        <p class="text-gray-700">
                            <span class="font-medium">Favorite Movies:</span> {{ profile.favoriteMovies.join(', ') }}
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
                                <p class="font-medium" v-if="props.userData.about?.looking_for_age_min || props.userData.about?.looking_for_age_max">
                                    {{ props.userData.about?.looking_for_age_min || '?' }} to {{ props.userData.about?.looking_for_age_max || '?' }} years
                                </p>
                                <p class="font-medium text-gray-400" v-else>Not specified</p>
                            </div>
                            
                            <div>
                                <p class="text-gray-500">Education Level</p>
                                <p class="font-medium">{{ props.userData.about?.looking_for_education || 'Any' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-gray-500">Religious Practice</p>
                                <p class="font-medium">{{ props.userData.about?.looking_for_religious_practice || 'Any' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-gray-500">Marital Status</p>
                                <p class="font-medium">{{ props.userData.about?.looking_for_marital_status || 'Any' }}</p>
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
    
    <!-- Photo Gallery Modal -->
    <div v-if="showPhotoGallery" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90">
        <!-- Close button -->
        <button 
            @click="closePhotoGallery" 
            class="absolute top-4 right-4 text-white p-2 rounded-full hover:bg-gray-800"
        >
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
            <img 
                v-if="profile.photos && profile.photos[currentPhotoIndex]" 
                :src="profile.photos[currentPhotoIndex]" 
                alt="Profile Photo" 
                class="max-h-full max-w-full mx-auto object-contain"
            >
            
            <!-- Photo counter -->
            <div v-if="profile.photos && profile.photos.length > 1" class="absolute bottom-4 left-0 right-0 text-center text-white">
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
        transition: max-height 0.5s ease-in, opacity 0.3s ease-in;
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
    from { opacity: 0; }
    to { opacity: 1; }
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