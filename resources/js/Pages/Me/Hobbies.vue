<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

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
                <!-- Profile Header Component -->
                <ProfileHeader :user="user" activeTab="hobbies" />

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