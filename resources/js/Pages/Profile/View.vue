<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

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
        <!-- Left Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <!-- Logo -->
            <div class="p-4 mb-6">
                <div class="text-purple-800 text-2xl font-bold">ZawajAfrica</div>
            </div>
            
            <!-- Navigation -->
            <nav class="space-y-2 px-3">
                <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span>Matches</span>
                </Link>
                
                <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Therapists</span>
                </Link>
                
                <Link :href="route('messages')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span>Messages</span>
                </Link>
                
                <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span>AI Chatbot</span>
                </Link>
                
                <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span>Notifications</span>
                </Link>
                
                <Link :href="route('profile.edit')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>My Profile</span>
                </Link>
                
                <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Subscription Plans</span>
                </Link>
            </nav>
            
            <!-- Upgrade Membership -->
            <div class="mt-auto p-4 fixed bottom-0 w-64">
                <div class="rounded-lg bg-purple-700 p-4 text-center text-white">
                    <div class="mb-2 flex justify-center">
                        <svg class="h-8 w-8 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold">Upgrade Membership</h3>
                    <p class="mt-1 text-xs">Your current membership is standard.</p>
                    <button class="mt-4 inline-flex items-center rounded-lg bg-purple-800 px-4 py-2 text-sm font-medium text-white hover:bg-purple-900">
                        Upgrade Now
                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
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
                    <!-- Profile Image -->
                    <div class="h-64 bg-gray-700 relative">
                        <img :src="profile.image" alt="Profile Image" class="w-full h-full object-cover opacity-0">
                        
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
                        
                        <!-- Match Percentage -->
                        <div class="absolute bottom-4 left-4 right-4">
                            <div class="flex items-center justify-between mb-1 text-white">
                                <span class="font-medium">{{ profile.compatibility }}%</span>
                                <span class="font-medium">Match</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-white bg-opacity-30">
                                <div class="h-full rounded-full bg-amber-400" :style="{ width: profile.compatibility + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Info -->
                    <div class="p-4 bg-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold flex items-center">
                                    {{ profile.name }}, {{ profile.age }}
                                    <span v-if="profile.verified" class="ml-2 text-amber-500">✓</span>
                                </h2>
                                <p class="text-gray-600">{{ profile.location }}</p>
                                <p class="text-rose-500 mt-2 flex items-center">
                                    <svg class="h-5 w-5 mr-1 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                    </svg>
                                    {{ profile.seeking }}
                                </p>
                            </div>
                            
                            <div class="flex space-x-3">
                                <button class="text-rose-500 border border-rose-500 rounded-full p-2">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button class="text-purple-800 border border-purple-800 rounded-full p-2">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Profile Actions -->
                        <div class="mt-6 flex justify-between">
                            <button class="flex-1 mx-1 bg-red-500 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Block
                            </button>
                            <button class="flex-1 mx-1 border border-red-500 text-red-500 py-2 px-4 rounded-lg flex items-center justify-center">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                                Block and Report
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Tabs -->
                <div class="border-t border-gray-200">
                    <div class="flex">
                        <button 
                            @click="setActiveTab('about')" 
                            class="flex-1 py-3 px-4 text-center font-medium"
                            :class="activeTab === 'about' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'"
                        >
                            About Me
                        </button>
                        <button 
                            @click="setActiveTab('searching')" 
                            class="flex-1 py-3 px-4 text-center font-medium"
                            :class="activeTab === 'searching' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'"
                        >
                            Searching For
                        </button>
                    </div>
                </div>
                
                <!-- Profile Content -->
                <div class="p-4" v-if="activeTab === 'about'">
                    <!-- About Me -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold mb-2">About Me</h3>
                        <p class="text-gray-700">{{ profile.aboutMe }}</p>
                    </div>
                    
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