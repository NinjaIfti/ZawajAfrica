<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

const props = defineProps({
    auth: Object,
    user: Object,
    photos: Array,
});

// State for photos
const photos = ref([]);
const isUploading = ref(false);
const uploadProgress = ref(0);
const successMessage = ref('');
const errorMessage = ref('');

// Initialize photos from user data or create empty slots
onMounted(() => {
    initializePhotos();
});

function initializePhotos() {
    const maxPhotos = 8;
    
    if (props.photos && Array.isArray(props.photos)) {
        photos.value = [...props.photos];
        
        // Add empty slots if needed to reach maxPhotos
        while (photos.value.length < maxPhotos) {
            photos.value.push({ id: null, url: null, is_primary: false });
        }
    } else {
        // Create empty photo slots
        for (let i = 0; i < maxPhotos; i++) {
            photos.value.push({ 
                id: null, 
                url: null, 
                is_primary: i === 0 // Make first photo primary by default
            });
        }
    }
}

// Function to handle file selection
function handleFileSelect(event, index) {
    const file = event.target.files[0];
    if (!file) return;
    
    isUploading.value = true;
    uploadProgress.value = 0;
    
    // Ensure we have the latest CSRF token
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    }
    
    const formData = new FormData();
    formData.append('photo', file);
    formData.append('index', index);
    
    axios.post(route('me.photos.upload'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            uploadProgress.value = percentCompleted;
        }
    }).then(response => {
        isUploading.value = false;
        successMessage.value = response.data.message || 'Photo uploaded successfully';
        setTimeout(() => { successMessage.value = ''; }, 3000);
        
        // Update photos from API response
        if (response.data.photos) {
            updatePhotoArray(response.data.photos);
        }
    }).catch(error => {
        isUploading.value = false;
        errorMessage.value = error.response?.data?.message || 'Failed to upload photo';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
    });
}

// Function to delete a photo
function deletePhoto(index) {
    if (!photos.value[index].id) return;
    
    axios.delete(route('me.photos.delete', { id: photos.value[index].id }))
        .then(response => {
        successMessage.value = response.data.message || 'Photo deleted successfully';
        setTimeout(() => { successMessage.value = ''; }, 3000);
        
        // Update photos from API response
        if (response.data.photos) {
            updatePhotoArray(response.data.photos);
        } else {
            // Reset this photo slot if no response
            photos.value[index] = { id: null, url: null, is_primary: false };
        }
    }).catch(error => {
        errorMessage.value = error.response?.data?.message || 'Failed to delete photo';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
    });
}

// Function to set a photo as primary
function setPrimaryPhoto(index) {
    if (!photos.value[index].id) return;
    
    axios.put(route('me.photos.primary', { id: photos.value[index].id }))
        .then(response => {
        successMessage.value = response.data.message || 'Primary photo updated';
        setTimeout(() => { successMessage.value = ''; }, 3000);
        
        // Update photos from API response
        if (response.data.photos) {
            updatePhotoArray(response.data.photos);
        } else {
            // Update locally
            photos.value.forEach((photo, i) => {
                photo.is_primary = i === index;
            });
        }
    }).catch(error => {
        errorMessage.value = error.response?.data?.message || 'Failed to set primary photo';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
    });
}

// Helper function to update photo array
function updatePhotoArray(newPhotos) {
    const maxPhotos = 8;
    
    // Update existing photos
    photos.value = [...newPhotos];
    
    // Add empty slots if needed
    while (photos.value.length < maxPhotos) {
        photos.value.push({ id: null, url: null, is_primary: false });
    }
}
</script>

<template>
    <Head title="My Photos" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Profile Header Component -->
                <ProfileHeader :user="props.user" activeTab="photos" />

                <!-- Photos Content -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Photos</h2>
                        
                        <!-- Messages area -->
                        <div class="flex items-center">
                            <div v-if="successMessage" class="text-green-600 text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                {{ successMessage }}
                        </div>
                        
                            <div v-if="errorMessage" class="text-red-600 text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                {{ errorMessage }}
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 mb-6">Adding photos is the best way to stand out from other profiles.</p>
                    
                    <!-- Upload Progress Bar -->
                    <div v-if="isUploading" class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                        <div 
                            class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" 
                            :style="{ width: uploadProgress + '%' }"
                        ></div>
                        <p class="text-sm text-gray-600 mt-1">Uploading: {{ Math.round(uploadProgress) }}%</p>
                    </div>
                    
                    <!-- Photo Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- For each photo slot -->
                        <div 
                            v-for="(photo, index) in photos" 
                            :key="index"
                            :class="[
                                'relative rounded-lg overflow-hidden shadow-sm',
                                index === 0 ? 'col-span-2 row-span-2' : 'col-span-1'
                            ]"
                        >
                            <!-- Photo display area -->
                            <div class="aspect-square bg-[#6543961A] flex items-center justify-center relative">
                                <!-- Actual photo if exists -->
                                <img 
                                    v-if="photo.url" 
                                    :src="photo.url" 
                                    class="absolute inset-0 w-full h-full object-cover"
                                    alt="User photo"
                                />
                                
                                <!-- Upload placeholder if no photo -->
                                <div v-else class="w-full h-full flex items-center justify-center">
                                <div class="absolute inset-0 opacity-5">
                                        <div class="absolute top-0 left-0 w-full h-full bg-indigo-200 transform -rotate-45"></div>
                            </div>
                        </div>
                        
                                <!-- Primary badge -->
                                <div 
                                    v-if="photo.is_primary && photo.url" 
                                    class="absolute top-2 left-2 bg-indigo-600 text-white text-xs px-2 py-1 rounded-full"
                                >
                                    Primary
                                </div>
                                
                                <!-- Upload button -->
                                <label class="absolute bottom-2 right-2 cursor-pointer">
                                    <input 
                                        type="file" 
                                        class="hidden" 
                                        accept="image/*" 
                                        @change="(e) => handleFileSelect(e, index)"
                                    >
                                    <div class="p-2 bg-white rounded-full shadow-sm hover:bg-gray-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                </label>
                                
                                <!-- Action buttons for existing photos -->
                                <div 
                                    v-if="photo.url" 
                                    class="absolute top-2 right-2 flex space-x-2"
                                >
                                    <!-- Set as primary button (not shown for primary photo) -->
                                    <button 
                                        v-if="!photo.is_primary"
                                        @click="setPrimaryPhoto(index)"
                                        class="p-1 bg-white rounded-full shadow-sm hover:bg-indigo-100 transition"
                                        title="Set as primary photo"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Delete button -->
                                    <button 
                                        @click="deletePhoto(index)"
                                        class="p-1 bg-white rounded-full shadow-sm hover:bg-red-100 transition"
                                        title="Delete photo"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
input[type="file"] {
    display: none;
}

button {
    transition: all 0.2s;
}
</style>