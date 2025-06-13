<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Sample personality questions and answers
const questions = ref({
    favoriteMovie1: '',
    favoriteMovie2: 'What is your favorite movie?',
    foodPreference: 'What sort of food you do like?',
    musicPreference: 'What sort of music do you like?',
});

// Update question answer
const updateAnswer = (field, value) => {
    questions.value[field] = value;
};

// Save changes
const saveChanges = () => {
    // This would be implemented with actual API call
    console.log('Saving personality answers:', questions.value);
};
</script>

<template>
    <Head title="My Personality" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Profile Header Component -->
                <ProfileHeader :user="user" activeTab="personality" />

                <!-- Personality Content -->
                <div>
                    <h2 class="text-2xl font-bold mb-2">Personality</h2>
                    <p class="text-gray-600 mb-6">Let your personality shine. Express yourself in your own words to give other users a better understanding of who you are.</p>
                    
                    <div class="space-y-6">
                        <!-- Question 1 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="favoriteMovie1" class="block text-gray-700 font-medium">What is favorite movie?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="favoriteMovie1" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="questions.favoriteMovie1"
                                @input="updateAnswer('favoriteMovie1', $event.target.value)"
                                placeholder="Enter your favorite movie..."
                            >
                        </div>

                        <!-- Question 2 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="favoriteMovie2" class="block text-gray-700 font-medium">What is your favorite movie?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="favoriteMovie2" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="questions.favoriteMovie2"
                                @input="updateAnswer('favoriteMovie2', $event.target.value)"
                            >
                        </div>

                        <!-- Question 3 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="foodPreference" class="block text-gray-700 font-medium">What sort of food you do like?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="foodPreference" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="questions.foodPreference"
                                @input="updateAnswer('foodPreference', $event.target.value)"
                            >
                        </div>

                        <!-- Question 4 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="musicPreference" class="block text-gray-700 font-medium">What sort of music do you like?</label>
                                <button class="text-primary-dark">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <input 
                                id="musicPreference" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                v-model="questions.musicPreference"
                                @input="updateAnswer('musicPreference', $event.target.value)"
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