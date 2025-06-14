<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
    if (user.photos && user.photos.length > 0) {
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
    
    // Extract lifestyle data
    const lifestyle = {
        smoke: user.lifestyle?.smoking || "Not specified",
        eatingHabits: user.lifestyle?.eating_habits || 'Not specified',
        haveChildren: user.lifestyle?.have_children ? 'Yes' : 'No',
        numberOfChildren: user.lifestyle?.number_of_children || 0,
        wantMoreChildren: user.lifestyle?.want_more_children || 'Not specified',
        relocate: user.lifestyle?.willing_to_relocate || 'Not specified',
        livingSituation: user.lifestyle?.living_situation || 'Not specified'
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
        compatibility: props.compatibility || 85,
        seeking: seeking || 'Seeking a partner',
        aboutMe: user.about?.about_me || "No information provided.",
        education: {
            level: user.background?.education_level || 'Not specified'
        },
        employment: {
            status: user.background?.employment_status || 'Not specified'
        },
        religion: {
            name: user.background?.religion || 'Not specified'
        },
        income: {
            range: user.background?.income_range || 'Not specified'
        },
        drink: {
            preference: user.lifestyle?.drinking || "Not specified"
        },
        maritalStatus: {
            status: user.background?.marital_status || 'Not specified'
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

// Toggle sections
const toggleSection = (sectionId) => {
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.toggle('open');
    }
};
</script>

<template>
    <Head title="Profile View" />

    <div class="flex min-h-screen bg-gray-100">
        
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
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
            
            <!-- Back Button -->
            <div class="mb-6">
                <Link :href="route('dashboard')" class="flex items-center text-gray-700">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="font-bold">Profile</span>
                </Link>
            </div>
            
            <!-- Profile View -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Profile Header -->
                <div class="relative">
                    <!-- Profile Image and Info Section - Restructured with image on left -->
                    <div class="flex">
                        <!-- Profile Image (Left Side) -->
                        <div class="relative w-1/2">
                            <div class="w-full h-full bg-gray-200 relative">
                                <img :src="profile.image" alt="Profile Image" class="w-full h-full object-cover">
                                
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
                            </div>
                            
                            <!-- Interaction Buttons -->
                            <div class="absolute left-4 bottom-4 flex space-x-3">
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
                        
                        <!-- Profile Information (Right Side) -->
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
                
                <!-- Profile Tabs -->
                <div class="border-t border-gray-200">
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
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" @click="toggleSection('overview')">
                            <h3 class="text-lg font-bold">Overview</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div id="overview" class="p-4 bg-white border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-y-4">
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
                                    <p class="text-gray-500">Drink</p>
                                    <p class="font-medium">{{ profile.drink.preference }}</p>
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
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" @click="toggleSection('appearance')">
                            <h3 class="text-lg font-bold">Appearance</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div id="appearance" class="p-4 bg-white border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-y-4">
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
                                    <p class="text-gray-500">Body Style</p>
                                    <p class="font-medium">{{ profile.appearance.bodyStyle }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Height</p>
                                    <p class="font-medium">{{ profile.appearance.height }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Ethnicity</p>
                                    <p class="font-medium">{{ profile.appearance.ethnicity }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Appearance</p>
                                    <p class="font-medium">{{ profile.appearance.style }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lifestyle Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" @click="toggleSection('lifestyle')">
                            <h3 class="text-lg font-bold">Life Style</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div id="lifestyle" class="p-4 bg-white border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-y-4">
                                <div>
                                    <p class="text-gray-500">Smoke</p>
                                    <p class="font-medium">{{ profile.lifestyle.smoke }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Eating Habits</p>
                                    <p class="font-medium">{{ profile.lifestyle.eatingHabits }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">No. of children</p>
                                    <p class="font-medium">{{ profile.lifestyle.numberOfChildren }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Want More Children</p>
                                    <p class="font-medium">{{ profile.lifestyle.wantMoreChildren }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Have Children</p>
                                    <p class="font-medium">{{ profile.lifestyle.haveChildren }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Ethnicity</p>
                                    <p class="font-medium">{{ profile.appearance.ethnicity }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Living Situation</p>
                                    <p class="font-medium">{{ profile.lifestyle.livingSituation }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Relocate</p>
                                    <p class="font-medium">{{ profile.lifestyle.relocate }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hobbies Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" @click="toggleSection('hobbies')">
                            <h3 class="text-lg font-bold">Hobbies & Interests</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div id="hobbies" class="p-4 bg-white border-t border-gray-200">
                            <div class="flex flex-wrap gap-2">
                                <span v-for="hobby in profile.hobbies" :key="hobby" class="px-3 py-1 bg-gray-100 rounded-full text-sm">
                                    {{ hobby }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sports Section -->
                    <div class="border border-gray-200 rounded-lg mb-4 overflow-hidden">
                        <div class="flex items-center justify-between p-4 bg-white cursor-pointer" @click="toggleSection('sports')">
                            <h3 class="text-lg font-bold">Sports</h3>
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div id="sports" class="p-4 bg-white border-t border-gray-200">
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
                    <p class="text-gray-700">Information about what {{ profile.name }} is looking for will be displayed here.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-rose-500 {
    color: #f43f5e;
}
</style> 