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

// Track which sections are currently being edited
const editingSections = ref({
    basicInfo: false,
    appearance: false,
    lifestyle: false,
    background: false,
    about: false
});

// Add success/error message state
const successMessage = ref('');
const errorMessage = ref('');

// Initialize userData with user data from props (with fallbacks for missing data)
const userData = ref(props.user ? {
    name: props.user.name || '',
    location: props.user.location || '',
    profile_photo: props.user.profile_photo || '/images/placeholder.jpg',
    appearance: props.user.appearance || {},
    lifestyle: props.user.lifestyle || {},
    background: props.user.background || {},
    about: props.user.about || {},
} : {
    name: '',
    location: '',
    profile_photo: '/images/placeholder.jpg',
    appearance: {},
    lifestyle: {},
    background: {},
    about: {},
});

// Function to change active tab
const setActiveTab = (tab) => {
    activeTab.value = tab;
};

// Function to toggle edit mode for a section
const toggleEditMode = (section) => {
    // Close any other open edit sections
    Object.keys(editingSections.value).forEach(key => {
        if (key !== section) editingSections.value[key] = false;
    });
    
    // Toggle the requested section
    editingSections.value[section] = !editingSections.value[section];
};

// Function to save changes for a specific section
const saveSection = (section) => {
    const dataToUpdate = {};
    
    switch(section) {
        case 'basicInfo':
            dataToUpdate.name = userData.value.name;
            dataToUpdate.location = userData.value.location;
            console.log("Saving name:", userData.value.name); // Debug log
            break;
        case 'appearance':
            dataToUpdate.appearance = userData.value.appearance;
            break;
        case 'lifestyle':
            dataToUpdate.lifestyle = userData.value.lifestyle;
            break;
        case 'background':
            dataToUpdate.background = userData.value.background;
            break;
        case 'about':
            dataToUpdate.about = userData.value.about;
            break;
    }
    
    // Use Inertia's router for direct POST rather than using named routes
    router.post('/profile/update', dataToUpdate, {
        onSuccess: (page) => {
            // Close edit mode for this section
            editingSections.value[section] = false;
            
            // Show success message
            successMessage.value = 'Changes saved successfully';
            setTimeout(() => { successMessage.value = ''; }, 3000);
            
            // Update the user data from response if provided
            if (page.props.user) {
                Object.assign(userData.value, page.props.user);
            }
            
            console.log("Server response:", page.props); // Debug log
        },
        onError: (errors) => {
            // Show error message
            errorMessage.value = 'Failed to save changes: ' + (Object.values(errors)[0] || 'Unknown error');
            console.error("Save errors:", errors); // Debug log
            setTimeout(() => { errorMessage.value = ''; }, 3000);
        },
        preserveScroll: true
    });
};

// Function to cancel editing and revert changes
const cancelEdit = (section) => {
    // Reset to original values from props
    switch(section) {
        case 'basicInfo':
            userData.value.name = props.user?.name || '';
            userData.value.location = props.user?.location || '';
            break;
        case 'appearance':
            userData.value.appearance = { ...props.user?.appearance || {} };
            break;
        case 'lifestyle':
            userData.value.lifestyle = { ...props.user?.lifestyle || {} };
            break;
        case 'background':
            userData.value.background = { ...props.user?.background || {} };
            break;
        case 'about':
            userData.value.about = { ...props.user?.about || {} };
            break;
    }
    
    // Close edit mode
    editingSections.value[section] = false;
};

// Function to update profile photo
const updateProfilePhoto = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('profile_photo', file);
    
    // Use direct URL instead of named route
    router.post('/profile/photo-update', formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Photo updated successfully';
            setTimeout(() => { successMessage.value = ''; }, 3000);
        },
        onError: (errors) => {
            errorMessage.value = 'Failed to update photo: ' + (Object.values(errors)[0] || 'Unknown error');
            setTimeout(() => { errorMessage.value = ''; }, 3000);
        }
    });
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
                <ProfileHeader :user="userData" activeTab="profile" />

                <!-- Success/Error Messages -->
                <div class="mb-4">
                    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ successMessage }}
                        </div>
                        <button @click="successMessage = ''" class="text-green-700">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ errorMessage }}
                        </div>
                        <button @click="errorMessage = ''" class="text-red-700">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Profile Content Section -->
                <div v-if="activeTab === 'profile'">
                    <!-- Profile Picture Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="md:col-span-1">
                            <div class="bg-white rounded-lg shadow-sm p-2 relative">
                                <div class="aspect-square rounded-lg overflow-hidden">
                                    <img :src="userData.profile_photo" alt="Profile" class="h-full w-full object-cover">
                                </div>
                                <label class="absolute top-4 right-4 bg-white rounded-full p-1 shadow cursor-pointer">
                                    <input type="file" class="hidden" accept="image/*" @change="updateProfilePhoto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <!-- Basic Info Section -->
                        <div class="md:col-span-2">
                            <div class="bg-white rounded-lg shadow-sm p-6 relative">
                                <h3 class="text-lg font-semibold mb-4">Basic Info</h3>
                                
                                <div v-if="!editingSections.basicInfo" class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Full Name</p>
                                        <p class="font-medium">{{ userData.name }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Location</p>
                                        <p class="font-medium">{{ userData.location }}</p>
                                    </div>
                                </div>
                                
                                <div v-else class="space-y-4">
                                    <div>
                                        <label class="text-sm text-gray-500">Full Name</label>
                                        <input 
                                            type="text" 
                                            id="nameField"
                                            v-model="userData.name" 
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            @input="e => userData.name = e.target.value"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label class="text-sm text-gray-500">Location</label>
                                        <input type="text" v-model="userData.location" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                    </div>
                                    
                                    <div class="flex space-x-3">
                                        <button 
                                            @click="saveSection('basicInfo')" 
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                        >
                                            Save
                                        </button>
                                        <button @click="cancelEdit('basicInfo')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                                    </div>
                                    
                                    <!-- Debug info -->
                                    <div class="text-xs text-gray-500 mt-2">
                                        Current name value: "{{ userData.name }}"
                                    </div>
                                </div>
                                
                                <button v-if="!editingSections.basicInfo" @click="toggleEditMode('basicInfo')" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
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
                            
                            <div v-if="!editingSections.appearance" class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Hair Color</p>
                                    <p class="font-medium">{{ userData.appearance.hair_color }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Eye Color</p>
                                    <p class="font-medium">{{ userData.appearance.eye_color }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Height</p>
                                    <p class="font-medium">{{ userData.appearance.height }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Weight</p>
                                    <p class="font-medium">{{ userData.appearance.weight }}</p>
                                </div>
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div>
                                    <label class="text-sm text-gray-500">Hair Color</label>
                                    <input type="text" v-model="userData.appearance.hair_color" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Eye Color</label>
                                    <input type="text" v-model="userData.appearance.eye_color" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Height</label>
                                    <input type="text" v-model="userData.appearance.height" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Weight</label>
                                    <input type="text" v-model="userData.appearance.weight" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button @click="saveSection('appearance')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                                    <button @click="cancelEdit('appearance')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                                </div>
                            </div>
                            
                            <button v-if="!editingSections.appearance" @click="toggleEditMode('appearance')" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Life Style Section -->
                        <div class="bg-white rounded-lg shadow-sm p-6 relative">
                            <h3 class="text-lg font-semibold mb-4">Life Style</h3>
                            
                            <div v-if="!editingSections.lifestyle" class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Do you drink?</p>
                                    <p class="font-medium">{{ userData.lifestyle.drinks }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Do you smoke?</p>
                                    <p class="font-medium">{{ userData.lifestyle.smokes }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Do you have children?</p>
                                    <p class="font-medium">{{ userData.lifestyle.has_children }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Number of children?</p>
                                    <p class="font-medium">{{ userData.lifestyle.number_of_children }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Occupation?</p>
                                    <p class="font-medium">{{ userData.lifestyle.occupation }}</p>
                                </div>
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div>
                                    <label class="text-sm text-gray-500">Do you drink?</label>
                                    <select v-model="userData.lifestyle.drinks" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="Occasionally">Occasionally</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Do you smoke?</label>
                                    <select v-model="userData.lifestyle.smokes" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="Occasionally">Occasionally</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Do you have children?</label>
                                    <select v-model="userData.lifestyle.has_children" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Number of children?</label>
                                    <input type="number" v-model="userData.lifestyle.number_of_children" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" min="0">
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Occupation?</label>
                                    <input type="text" v-model="userData.lifestyle.occupation" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button @click="saveSection('lifestyle')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                                    <button @click="cancelEdit('lifestyle')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                                </div>
                            </div>
                            
                            <button v-if="!editingSections.lifestyle" @click="toggleEditMode('lifestyle')" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Background / Culture Section -->
                        <div class="bg-white rounded-lg shadow-sm p-6 relative">
                            <h3 class="text-lg font-semibold mb-4">Background / Culture</h3>
                            
                            <div v-if="!editingSections.background" class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Nationality</p>
                                    <p class="font-medium">{{ userData.background.nationality }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Education</p>
                                    <p class="font-medium">{{ userData.background.education }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Language spoken</p>
                                    <p class="font-medium">{{ userData.background.language_spoken }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Born / Reverted</p>
                                    <p class="font-medium">{{ userData.background.born_reverted }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Read Quran</p>
                                    <p class="font-medium">{{ userData.background.read_quran }}</p>
                                </div>
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div>
                                    <label class="text-sm text-gray-500">Nationality</label>
                                    <input type="text" v-model="userData.background.nationality" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Education</label>
                                    <input type="text" v-model="userData.background.education" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Language spoken</label>
                                    <input type="text" v-model="userData.background.language_spoken" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Born / Reverted</label>
                                    <select v-model="userData.background.born_reverted" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="Born Muslim">Born Muslim</option>
                                        <option value="Reverted">Reverted</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Read Quran</label>
                                    <select v-model="userData.background.read_quran" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="Learning">Learning</option>
                                    </select>
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button @click="saveSection('background')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                                    <button @click="cancelEdit('background')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                                </div>
                            </div>
                            
                            <button v-if="!editingSections.background" @click="toggleEditMode('background')" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- About Me Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 relative mb-6">
                        <h3 class="text-lg font-semibold mb-4">More About Me</h3>
                        
                        <div v-if="!editingSections.about" class="space-y-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Your profile heading</p>
                                <p class="font-medium">{{ userData.about.heading }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">A little about yourself</p>
                                <p class="font-medium">{{ userData.about.about_me }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">What you're looking for in a partner</p>
                                <p class="font-medium">{{ userData.about.looking_for }}</p>
                            </div>
                        </div>
                        
                        <div v-else class="space-y-6">
                            <div>
                                <label class="text-sm text-gray-500 mb-1">Your profile heading</label>
                                <input type="text" v-model="userData.about.heading" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                            </div>
                            
                            <div>
                                <label class="text-sm text-gray-500 mb-1">A little about yourself</label>
                                <textarea v-model="userData.about.about_me" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" rows="4"></textarea>
                            </div>
                            
                            <div>
                                <label class="text-sm text-gray-500 mb-1">What you're looking for in a partner</label>
                                <textarea v-model="userData.about.looking_for" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" rows="4"></textarea>
                            </div>
                            
                            <div class="flex space-x-3">
                                <button @click="saveSection('about')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                                <button @click="cancelEdit('about')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                            </div>
                        </div>
                        
                        <button v-if="!editingSections.about" @click="toggleEditMode('about')" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
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
/* Form element styling */
input[type="file"] {
    display: none;
}

select, input, textarea {
    font-size: 0.9rem;
}

button {
    transition: all 0.2s;
}

.edit-button:hover {
    transform: scale(1.1);
}
</style>