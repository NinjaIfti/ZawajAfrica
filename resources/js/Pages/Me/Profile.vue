<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Active tab state
const activeTab = ref('profile');

// Sample user data (replace with actual data from backend)
const user = ref({
    id: '34564',
    name: 'Anila Anzah',
    location: 'Garki, Abuja, Niageria',
    profile_photo: '/images/placeholder.jpg',
    appearance: {
        hair_color: '---',
        eye_color: 'Blue',
        height: '---',
        weight: '---'
    },
    lifestyle: {
        drinks: '---',
        smokes: 'No',
        has_children: '---',
        number_of_children: '---',
        occupation: '---'
    },
    background: {
        nationality: '---',
        education: 'Blue',
        language_spoken: '---',
        born_reverted: '---',
        read_quran: '---'
    },
    about: {
        heading: '---',
        about_me: '---',
        looking_for: '---'
    }
});

// Function to change active tab
const setActiveTab = (tab) => {
    activeTab.value = tab;
};
</script>

<template>
    <Head title="My Profile" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">My Profile</h1>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-600">English</span>
                        <button class="rounded-full p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                        </button>
                        <div class="h-10 w-10 rounded-full overflow-hidden">
                            <img :src="user.profile_photo" alt="Profile" class="h-full w-full object-cover">
                        </div>
                    </div>
                </div>

                <!-- Profile Header Card -->
                <div class="bg-white rounded-lg shadow-sm mb-6 relative overflow-hidden">
                    <!-- Diagonal pattern background -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                    </div>
                    
                    <div class="relative p-8 flex flex-col items-center">
                        <div class="absolute top-4 right-4">
                            <button class="text-primary-dark hover:text-primary hover:underline text-sm font-medium">View Preview</button>
                        </div>
                        
                        <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-primary mb-3">
                            <img :src="user.profile_photo" alt="Profile" class="h-full w-full object-cover">
                        </div>
                        
                        <h2 class="text-xl font-bold mb-1">{{ user.name }}</h2>
                        <p class="text-gray-500 text-sm">ID: {{ user.id }}</p>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex mb-6 bg-white rounded-lg shadow-sm overflow-hidden">
                    <Link 
                        :href="route('me.profile')" 
                        class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                        :class="route().current('me.profile') ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profile
                    </Link>
                    
                    <Link 
                        :href="route('me.photos')" 
                        class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                        :class="route().current('me.photos') ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Photos
                    </Link>
                    
                    <Link 
                        :href="route('me.hobbies')" 
                        class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                        :class="route().current('me.hobbies') ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Hobbies & Interest
                    </Link>
                    
                    <Link 
                        :href="route('me.personality')" 
                        class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                        :class="route().current('me.personality') ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        Personality
                    </Link>
                    
                    <Link 
                        :href="route('me.faqs')" 
                        class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                        :class="route().current('me.faqs') ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        FAQs
                    </Link>
                </div>

                <!-- Profile Content Section -->
                <div v-if="activeTab === 'profile'">
                    <!-- Profile Picture Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="md:col-span-1">
                            <div class="bg-white rounded-lg shadow-sm p-2 relative">
                                <div class="aspect-square rounded-lg overflow-hidden">
                                    <img :src="user.profile_photo" alt="Profile" class="h-full w-full object-cover">
                                </div>
                                <button class="absolute top-4 right-4 bg-white rounded-full p-1 shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Basic Info Section -->
                        <div class="md:col-span-2">
                            <div class="bg-white rounded-lg shadow-sm p-6 relative">
                                <h3 class="text-lg font-semibold mb-4">Basic Info</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Full Name</p>
                                        <p class="font-medium">{{ user.name }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Location</p>
                                        <p class="font-medium">{{ user.location }}</p>
                                    </div>
                                </div>
                                
                                <button class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Three Column Info Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Appearance Section -->
                        <div class="bg-white rounded-lg shadow-sm p-6 relative">
                            <h3 class="text-lg font-semibold mb-4">Appearance</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Hair Color</p>
                                    <p class="font-medium">{{ user.appearance.hair_color }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Eye Color</p>
                                    <p class="font-medium">{{ user.appearance.eye_color }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Height</p>
                                    <p class="font-medium">{{ user.appearance.height }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Weight</p>
                                    <p class="font-medium">{{ user.appearance.weight }}</p>
                                </div>
                            </div>
                            
                            <button class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Life Style Section -->
                        <div class="bg-white rounded-lg shadow-sm p-6 relative">
                            <h3 class="text-lg font-semibold mb-4">Life Style</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Do you drink?</p>
                                    <p class="font-medium">{{ user.lifestyle.drinks }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Do you smoke?</p>
                                    <p class="font-medium">{{ user.lifestyle.smokes }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Do you have children?</p>
                                    <p class="font-medium">{{ user.lifestyle.has_children }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Number of children?</p>
                                    <p class="font-medium">{{ user.lifestyle.number_of_children }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Occupation?</p>
                                    <p class="font-medium">{{ user.lifestyle.occupation }}</p>
                                </div>
                            </div>
                            
                            <button class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Background / Culture Section -->
                        <div class="bg-white rounded-lg shadow-sm p-6 relative">
                            <h3 class="text-lg font-semibold mb-4">Background / Culture</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Nationality</p>
                                    <p class="font-medium">{{ user.background.nationality }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Education</p>
                                    <p class="font-medium">{{ user.background.education }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Language spoken</p>
                                    <p class="font-medium">{{ user.background.language_spoken }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Born / Reverted</p>
                                    <p class="font-medium">{{ user.background.born_reverted }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Read Quran</p>
                                    <p class="font-medium">{{ user.background.read_quran }}</p>
                                </div>
                            </div>
                            
                            <button class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- About Me Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 relative mb-6">
                        <h3 class="text-lg font-semibold mb-4">More About Me</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Your profile heading</p>
                                <p class="font-medium">{{ user.about.heading }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">A little about yourself</p>
                                <p class="font-medium">{{ user.about.about_me }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">What you're looking for in a partner</p>
                                <p class="font-medium">{{ user.about.looking_for }}</p>
                            </div>
                        </div>
                        
                        <button class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Placeholder for other tabs -->
                <div v-if="activeTab === 'photos'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Photos</h3>
                    <p class="text-gray-500">Upload your photos here.</p>
                </div>
                
                <div v-if="activeTab === 'hobbies'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Hobbies & Interests</h3>
                    <p class="text-gray-500">Add your hobbies and interests here.</p>
                </div>
                
                <div v-if="activeTab === 'personality'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Personality</h3>
                    <p class="text-gray-500">Describe your personality traits here.</p>
                </div>
                
                <div v-if="activeTab === 'faqs'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">FAQs</h3>
                    <p class="text-gray-500">Frequently asked questions about your profile.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>