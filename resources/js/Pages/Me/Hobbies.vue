<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Sample interests data
const interests = ref({
    entertainment: 'Antiques, Astrology, Bars / Pubs / Nightclubs, Collecting,',
    food: '',
    music: '',
    sports: '',
});

// Update interest field
const updateInterest = (field, value) => {
    interests.value[field] = value;
};

// Save changes
const saveChanges = () => {
    // This would be implemented with actual API call
    console.log('Saving interests:', interests.value);
};
</script>

<template>
    <Head title="My Hobbies & Interests" />

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
                            </svg>
                        </button>
                        <div class="h-10 w-10 rounded-full overflow-hidden">
                            <img src="/images/placeholder.jpg" alt="Profile" class="h-full w-full object-cover">
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
                            <img src="/images/placeholder.jpg" alt="Profile" class="h-full w-full object-cover">
                        </div>
                        
                        <h2 class="text-xl font-bold mb-1">Anila Anzah</h2>
                        <p class="text-gray-500 text-sm">ID: 34564</p>
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

                <!-- Hobbies & Interests Content -->
                <div>
                    <h2 class="text-2xl font-bold mb-2">Hobbies & Interests</h2>
                    <p class="text-gray-600 mb-6">Let other know what your interests are and help us connect you with users that may have similar interests.</p>
                    
                    <div class="space-y-6">
                        <!-- Entertainment -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="entertainment" class="block text-gray-700 font-medium">What do you do for fun/entertainment?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="entertainment" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="interests.entertainment"
                                @input="updateInterest('entertainment', $event.target.value)"
                            >
                        </div>

                        <!-- Food -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="food" class="block text-gray-700 font-medium">What sort of food do you like?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="food" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="interests.food"
                                @input="updateInterest('food', $event.target.value)"
                                placeholder="Enter food preferences..."
                            >
                        </div>

                        <!-- Music -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="music" class="block text-gray-700 font-medium">What sort of music do you like?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="music" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="interests.music"
                                @input="updateInterest('music', $event.target.value)"
                                placeholder="Enter music preferences..."
                            >
                        </div>

                        <!-- Sports -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="sports" class="block text-gray-700 font-medium">What sports do you play or like to watch?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="sports" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="interests.sports"
                                @input="updateInterest('sports', $event.target.value)"
                                placeholder="Enter sports preferences..."
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>