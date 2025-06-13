<script setup>
import { Link } from '@inertiajs/vue3';
import AppHeader from '@/Components/AppHeader.vue';

const props = defineProps({
    user: Object,
    activeTab: {
        type: String,
        default: 'profile',
        validator: (value) => ['profile', 'photos', 'hobbies', 'personality', 'faqs'].includes(value)
    }
});
</script>

<template>
    <div>
        <!-- Header -->
        <AppHeader :user="user">
            <template #title>
                <h1 class="text-3xl font-bold">My Profile</h1>
            </template>
        </AppHeader>

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
                    <img :src="user.profile_photo || '/images/placeholder.jpg'" :alt="user.name" class="h-full w-full object-cover">
                </div>
                
                <h2 class="text-xl font-bold mb-1">{{ user.name }}</h2>
                <p class="text-gray-500 text-sm">{{ user.location || 'Location not set' }}</p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex mb-6 bg-white rounded-lg shadow-sm overflow-hidden">
            <Link 
                :href="route('me.profile')" 
                class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                :class="activeTab === 'profile' ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
            </Link>
            
            <Link 
                :href="route('me.photos')" 
                class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                :class="activeTab === 'photos' ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Photos
            </Link>
            
            <Link 
                :href="route('me.hobbies')" 
                class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                :class="activeTab === 'hobbies' ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Hobbies & Interest
            </Link>
            
            <Link 
                :href="route('me.personality')" 
                class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                :class="activeTab === 'personality' ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                Personality
            </Link>
            
            <Link 
                :href="route('me.faqs')" 
                class="flex-1 py-3 px-4 flex justify-center items-center gap-2 font-medium"
                :class="activeTab === 'faqs' ? 'bg-primary-dark text-white' : 'text-gray-700 hover:bg-gray-50'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                FAQs
            </Link>
        </div>
    </div>
</template> 