<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

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
                <!-- Profile Header Component -->
                <ProfileHeader :user="user" activeTab="profile" />

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