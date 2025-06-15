<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
    user: Object,
    photos: Array,
});

// Mobile menu state
const isMobileMenuOpen = ref(false);

// Toggle mobile menu
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    
    // Prevent body scrolling when menu is open
    if (isMobileMenuOpen.value) {
        document.body.classList.add('overflow-hidden');
    } else {
        document.body.classList.remove('overflow-hidden');
    }
};

// Close mobile menu when clicking outside
const closeMobileMenu = (e) => {
    if (isMobileMenuOpen.value && !e.target.closest('.mobile-menu') && 
        !e.target.closest('.mobile-menu-toggle')) {
        isMobileMenuOpen.value = false;
        document.body.classList.remove('overflow-hidden');
    }
};

// Add click event listener when component is mounted
onMounted(() => {
    document.addEventListener('click', closeMobileMenu);
    initializePhotos();
});

// Remove event listener when component is unmounted
onUnmounted(() => {
    document.removeEventListener('click', closeMobileMenu);
    document.body.classList.remove('overflow-hidden');
});

// State for photos
const photos = ref([]);
const isUploading = ref(false);
const uploadProgress = ref(0);
const successMessage = ref('');
const errorMessage = ref('');

// Initialize photos from user data or create empty slots
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
    
    // Refresh CSRF token before upload
    window.refreshCSRFToken();
    
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
    
    // Refresh CSRF token before delete
    window.refreshCSRFToken();
    
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
    
    // Refresh CSRF token before update
    window.refreshCSRFToken();
    
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

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-4 flex items-center md:hidden">
            <button 
                @click="toggleMobileMenu" 
                class="mobile-menu-toggle p-1 mr-3"
                aria-label="Toggle menu"
            >
                <svg 
                    class="h-6 w-6 text-gray-700" 
                    :class="{ 'hidden': isMobileMenuOpen }"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg 
                    class="h-6 w-6 text-gray-700" 
                    :class="{ 'hidden': !isMobileMenuOpen }"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <!-- Page title on mobile -->
            <h1 class="text-lg font-bold">My Photos</h1>
        </div>

        <!-- Mobile Menu Overlay -->
        <div 
            v-if="isMobileMenuOpen" 
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar Component - Slides in from left on mobile -->
        <aside 
            class="mobile-menu fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:relative md:z-0 md:translate-x-0"
            :class="{'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen}"
        >
        <Sidebar :user="$page.props.auth.user" />
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0">
            <div class="container mx-auto max-w-6xl">
                <!-- Profile Header Component - Only visible on desktop -->
                <ProfileHeader :user="props.user" activeTab="photos" class="hidden md:block" />

                <!-- Mobile Profile Navigation - Only visible on mobile -->
                <div class="md:hidden mb-4 overflow-x-auto">
                    <div class="flex  rounded-lg shadow-sm">
                        <Link 
                            :href="route('me.profile')" 
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </Link>
                        
                        <Link 
                            :href="route('me.photos')" 
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium bg-primary-dark text-white"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Photos
                        </Link>
                        
                        <Link 
                            :href="route('me.hobbies')" 
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Hobbies
                        </Link>
                        
                        <Link 
                            :href="route('me.personality')" 
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            Personality
                        </Link>
                        
                        <Link 
                            :href="route('me.faqs')" 
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            FAQs
                        </Link>
                    </div>
                </div>

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
                    
                    <!-- Photo Grid - Responsive for mobile -->
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
/* Ensure proper stacking on mobile */
@media (max-width: 768px) {
    .min-h-screen {
        padding-top: 1rem;
    }
}

/* Prevent scrolling when mobile menu is open */
:global(.overflow-hidden) {
    overflow: hidden;
}
</style>