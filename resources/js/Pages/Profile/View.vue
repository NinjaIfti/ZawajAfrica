<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

// Sample profile data (will be replaced with backend data later)
const profile = ref({
    id: 1,
    name: 'Salihu Gomal',
    age: 31,
    verified: true,
    location: 'Garki, Abuja, Nigeria',
    online: true,
    image: '/images/placeholder.jpg',
    compatibility: 85,
    seeking: 'Seeking a female 20-23',
    aboutMe: "I'm looking for a partner who is kind, ambitious, and shares similar values for building a happy, balanced life together.",
    education: {
        level: 'Masters'
    },
    employment: {
        status: 'Employed'
    },
    religion: {
        name: 'Islam-Sunni'
    },
    income: {
        range: '$20k-100k'
    },
    drink: {
        preference: "Don't Drink"
    },
    maritalStatus: {
        status: 'Single'
    },
    appearance: {
        hairColor: 'Blond',
        eyeColor: 'Unmarried',
        height: "5'5\" (167 cm)",
        weight: '54 (119 lb)',
        bodyStyle: 'Slim',
        ethnicity: 'Caucasian',
        style: 'N/A'
    },
    lifestyle: {
        smoke: "Don't Smoke",
        eatingHabits: 'Halal Foods Always',
        haveChildren: 'Yes',
        numberOfChildren: 3,
        wantMoreChildren: 'No',
        relocate: 'Open to Relocate',
        livingSituation: 'N/A'
    },
    hobbies: [
        'Library', 'Movies/Cinema', 'Pets', 'Photography', 
        'Traveling', 'Reading', 'Writing', 'Volunteering', 'Cooking/Food'
    ],
    sports: [
        'Cycling', 'Jogging/Running', 'Swimming', 'Walking', 'Yoga'
    ],
    favoriteMovies: ['Inception', 'Joker']
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
            <!-- Welcome and Search -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold">Welcome Ayofemi!</h1>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm">English</span>
                        <div class="h-10 w-10 overflow-hidden rounded-full bg-gray-300">
                            <img src="/images/placeholder.jpg" alt="Profile" class="h-full w-full object-cover" />
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative flex-1">
                        <div class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2">
                            <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="Search" class="w-full border-none bg-transparent outline-none" />
                        </div>
                    </div>
                    
                    <button class="rounded-lg border border-gray-300 bg-white p-2">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </button>
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