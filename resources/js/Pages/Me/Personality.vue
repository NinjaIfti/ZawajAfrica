<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Initialize personality questions from user data or with defaults
const questions = ref({
    favoriteMovie1: props.user?.personality?.favoriteMovie1 || '',
    favoriteMovie2: props.user?.personality?.favoriteMovie2 || '',
    foodPreference: props.user?.personality?.foodPreference || '',
    musicPreference: props.user?.personality?.musicPreference || '',
});

// For tracking editing state
const isEditing = ref(false);
const isSaving = ref(false);
const showSuccess = ref(false);

// Update question answer
const updateAnswer = (field, value) => {
    questions.value[field] = value;
};

// Save changes
const saveChanges = () => {
    isSaving.value = true;
    
    // Use Inertia to submit the data
    router.patch(route('me.personality.update'), { personality: questions.value }, {
        preserveScroll: true,
        onSuccess: () => {
            isSaving.value = false;
            isEditing.value = false;
            showSuccess.value = true;
            
            // Hide success message after 3 seconds
            setTimeout(() => {
                showSuccess.value = false;
            }, 3000);
        },
        onError: () => {
            isSaving.value = false;
        }
    });
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
                <ProfileHeader :user="props.user" activeTab="personality" />

                <!-- Personality Content -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Personality</h2>
                        
                        <div class="flex items-center space-x-3">
                            <!-- Success message when saved -->
                            <div v-if="showSuccess" class="text-green-600 text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Saved successfully
                            </div>
                            
                            <!-- Save button -->
                            <button 
                                v-if="isEditing"
                                @click="saveChanges" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                :disabled="isSaving"
                            >
                                <span v-if="isSaving">Saving...</span>
                                <span v-else>Save Changes</span>
                            </button>
                            
                            <!-- Edit button -->
                            <button 
                                v-else
                                @click="isEditing = true" 
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Edit Answers
                            </button>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">Let your personality shine. Express yourself in your own words to give other users a better understanding of who you are.</p>
                    
                    <div class="space-y-6">
                        <!-- Question 1 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="mb-3">
                                <label for="favoriteMovie1" class="block text-gray-700 font-medium">What is your favorite movie?</label>
                            </div>
                            <input 
                                id="favoriteMovie1" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="questions.favoriteMovie1"
                                @input="updateAnswer('favoriteMovie1', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Enter your favorite movie..."
                            >
                        </div>

                        <!-- Question 2 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="mb-3">
                                <label for="favoriteMovie2" class="block text-gray-700 font-medium">What is another movie that you like?</label>
                            </div>
                            <input 
                                id="favoriteMovie2" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="questions.favoriteMovie2"
                                @input="updateAnswer('favoriteMovie2', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Enter another movie you enjoy..."
                            >
                        </div>

                        <!-- Question 3 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="mb-3">
                                <label for="foodPreference" class="block text-gray-700 font-medium">What sort of food do you like?</label>
                            </div>
                            <input 
                                id="foodPreference" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="questions.foodPreference"
                                @input="updateAnswer('foodPreference', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Enter your food preferences..."
                            >
                        </div>

                        <!-- Question 4 -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="mb-3">
                                <label for="musicPreference" class="block text-gray-700 font-medium">What sort of music do you like?</label>
                            </div>
                            <input 
                                id="musicPreference" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="questions.musicPreference"
                                @input="updateAnswer('musicPreference', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Enter your music preferences..."
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Form styling */
input:disabled {
    background-color: #f9fafb;
    cursor: not-allowed;
}

button {
    transition: all 0.2s;
}
</style>