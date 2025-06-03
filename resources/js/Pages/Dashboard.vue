<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// State for right sidebar visibility
const isRightSidebarVisible = ref(true);

// Function to toggle right sidebar
const toggleRightSidebar = () => {
    isRightSidebarVisible.value = !isRightSidebarVisible.value;
};

// Sample data for matches
const matches = ref([
    {
        id: 1,
        name: 'Salihu G',
        age: 31,
        location: 'Garki, Abuja, Nigeria',
        online: true,
        image: '/images/placeholder.jpg',
        compatibility: 85,
        timestamp: '10 min ago'
    },
    {
        id: 2,
        name: 'Abdullahi S',
        age: 31,
        location: 'Garki, Abuja, Nigeria',
        online: true,
        image: '/images/placeholder.jpg',
        compatibility: 85,
        timestamp: '15 min ago'
    }
]);

// Sample data for therapists
const therapists = ref([
    {
        id: 1,
        name: 'Dr. Maria Azad',
        specialty: 'Dermatologist',
        image: '/images/placeholder.jpg',
        online: true
    },
    {
        id: 2,
        name: 'Dr. Maria Azad',
        specialty: 'Dermatologist',
        image: '/images/placeholder.jpg',
        online: true
    },
    {
        id: 3,
        name: 'Dr. Maria Azad',
        specialty: 'Dermatologist',
        image: '/images/placeholder.jpg',
        online: true
    }
]);

// Add state for therapists panel
const isTherapistsPanelExpanded = ref(true);

// Function to toggle therapists panel
const toggleTherapistsPanel = () => {
    isTherapistsPanelExpanded.value = !isTherapistsPanelExpanded.value;
};

// Add state for messages panel
const isMessagesPanelExpanded = ref(true);

// Function to toggle messages panel
const toggleMessagesPanel = () => {
    isMessagesPanelExpanded.value = !isMessagesPanelExpanded.value;
};

// Sample data for recent messages
const recentMessages = ref([
    {
        id: 1,
        name: 'Fatenn Saeed',
        message: 'Hi, I am here to discuss some...',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 2,
        name: 'Salwa Al-Qwaiz',
        message: 'Assalamu alaikum.',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 3,
        name: 'Amima Kaleb',
        message: 'Okay we\'ll have a meetup.',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 4,
        name: 'Hanan Hablas',
        message: 'How many kids do you have?',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 5,
        name: 'Emma Wilson',
        message: 'How many kids do you have?',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 6,
        name: 'John Alex',
        message: 'How many kids do you have?',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    }
]);

// Language selector
const languages = [
    { id: 'en', name: 'English' },
    { id: 'ha', name: 'Hausa' },
    { id: 'yo', name: 'Yoruba' },
    { id: 'ig', name: 'Igbo' }
];
const selectedLanguage = ref(languages[0]);
const showLanguageModal = ref(false);

// Function to toggle language modal
const toggleLanguageModal = () => {
    showLanguageModal.value = !showLanguageModal.value;
};

// Function to select language and close modal
const selectLanguage = (lang) => {
    selectedLanguage.value = lang;
    showLanguageModal.value = false;
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <!-- Logo -->
            <div class="p-4 mb-6">
                <div class="text-purple-800 text-2xl font-bold">ZawajAfrica</div>
            </div>
            
            <!-- Navigation -->
            <nav class="space-y-2 px-3">
                <Link :href="route('dashboard')" class="flex items-center rounded-lg bg-purple-600 px-4 py-3 text-base font-medium text-white">
                    <svg class="mr-3 h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Welcome {{ $page.props.auth.user.name }}!</h1>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2">
                            <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="Search" class="w-64 border-none bg-transparent outline-none" />
                        </div>
                    </div>
                    
                    <button class="rounded-lg border border-gray-300 bg-white p-2">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- All Matches -->
            <div>
                <h2 class="mb-6 text-xl font-bold">All Matches</h2>
                
                <div class="space-y-6">
                    <div v-for="match in matches" :key="match.id" class="relative overflow-hidden rounded-lg bg-gray-800 shadow-lg">
                        <!-- Match Image -->
                        <div class="h-64 w-full bg-gray-700 relative">
                            <img :src="match.image" :alt="match.name" class="h-full w-full object-cover opacity-0" />

                            <!-- Online Status -->
                            <div class="absolute left-4 top-4 flex items-center rounded-full bg-black bg-opacity-70 px-3 py-1 text-sm text-white">
                                <span class="mr-2 h-2 w-2 rounded-full bg-green-500"></span>
                                Online
                            </div>
                            
                            <!-- Favorite Button -->
                            <div class="absolute right-4 top-4 rounded-full bg-amber-500 p-2">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>

                            <!-- Match percentage (on bottom of image) -->
                            <div class="absolute bottom-4 left-4 right-4">
                                <div class="flex items-center justify-between mb-1 text-white">
                                    <span class="font-medium">{{ match.compatibility }}%</span>
                                    <span class="font-medium">Match</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-white bg-opacity-30">
                                    <div class="h-full rounded-full bg-amber-400" :style="{ width: match.compatibility + '%' }"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Match Info (below image) -->
                        <div class="bg-white px-4 py-3 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <h3 class="text-lg font-bold">{{ match.name }}</h3>
                                    <span class="ml-2 text-amber-500 text-lg">✓</span>
                                    <span class="ml-2 text-gray-500">, {{ match.age }}</span>
                                </div>
                                <div class="flex space-x-2 items-center">
                                    <button class="text-purple-800">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                    <button class="text-purple-800">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-gray-600">{{ match.location }}</p>
                            <p class="text-sm text-gray-500">{{ match.timestamp }}</p>
                            <!-- Rainbow pattern image -->
                            <div class="absolute bottom-0 right-8 h-20 w-24 translate-x-1/3 translate-y-1/4">
                                <img src="/images/card.png" alt="Pattern" class="h-full w-full object-contain opacity-60" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar -->
        <div class="w-80 p-6">
            <!-- Profile and Language at the top -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex-1"></div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium">
                        <span class="mr-2">English</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                        </svg>
                    </div>
                    <div class="h-10 w-10 overflow-hidden rounded-full bg-gray-300">
                        <img src="/images/placeholder.jpg" alt="Profile" class="h-full w-full object-cover" />
                    </div>
                </div>
            </div>
            
            <!-- Therapists Section -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold">Therapists</h2>
                        <button class="text-gray-500 hover:text-gray-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div v-for="therapist in therapists" :key="therapist.id" class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="relative mr-3 h-16 w-16 overflow-hidden rounded-full bg-gray-200 border-2 border-amber-500">
                                    <img :src="therapist.image" :alt="therapist.name" class="h-full w-full object-cover opacity-0" />
                                    <div v-if="therapist.online" class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-green-500"></div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">{{ therapist.name }}</h3>
                                    <p class="text-gray-500">{{ therapist.specialty }}</p>
                                </div>
                            </div>
                            
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Messages -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold">Recent Messages</h2>
                        <button class="text-gray-500 hover:text-gray-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div v-for="message in recentMessages" :key="message.id" class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="relative mr-3 h-14 w-14 overflow-hidden rounded-full bg-gray-200">
                                    <img :src="message.image" :alt="message.name" class="h-full w-full object-cover opacity-0" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">{{ message.name }}</h3>
                                    <p class="text-gray-500">{{ message.message }}</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-end">
                                <span class="text-gray-500">{{ message.time }}</span>
                                <div v-if="message.unread" class="mt-1 h-6 w-6 rounded-full bg-purple-600 text-center text-sm text-white flex items-center justify-center">3</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Language Selection Modal -->
    <div v-if="showLanguageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative max-w-md rounded-lg bg-white p-6 shadow-xl">
            <!-- Modal Header -->
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Change Language</h3>
                <button 
                    @click="toggleLanguageModal" 
                    class="text-gray-400 hover:text-gray-500"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Language Options -->
            <div class="space-y-4">
                <div 
                    v-for="lang in languages" 
                    :key="lang.id"
                    @click="selectLanguage(lang)"
                    class="flex cursor-pointer items-center justify-between rounded-lg py-3 hover:bg-gray-50"
                >
                    <span class="text-lg font-medium text-gray-900">{{ lang.name }}</span>
                    <div 
                        class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-gray-300"
                        :class="{ 'border-purple-600': selectedLanguage.id === lang.id }"
                    >
                        <div 
                            v-if="selectedLanguage.id === lang.id" 
                            class="h-3 w-3 rounded-full bg-purple-600"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add custom styles if needed */
</style>
