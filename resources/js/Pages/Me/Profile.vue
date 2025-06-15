<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
    user: Object,
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
});

// Remove event listener when component is unmounted
onUnmounted(() => {
    document.removeEventListener('click', closeMobileMenu);
    document.body.classList.remove('overflow-hidden');
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
    country: props.user.country || '',
    state: props.user.state || '',
    city: props.user.city || '',
    profile_photo: props.user.profile_photo || '/images/placeholder.jpg',
    appearance: props.user.appearance || {},
    lifestyle: props.user.lifestyle || {},
    background: props.user.background || {},
    about: props.user.about || {},
} : {
    name: '',
    country: '',
    state: '',
    city: '',
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
            dataToUpdate.city = userData.value.city;
            dataToUpdate.state = userData.value.state;
            dataToUpdate.country = userData.value.country;
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
    
    // Immediately set edit mode to false
    editingSections.value[section] = false;
    
    // Show a saving message
    successMessage.value = 'Saving changes...';
    
    // Use Fetch API directly for more control
    fetch('/profile/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify(dataToUpdate)
    })
    .then(response => response.json())
    .then(data => {
        console.log("Save response:", data);
        
        // Set success message
        successMessage.value = 'Changes saved successfully!';
        
        // Give time for the user to see the success message, then reload
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    })
    .catch(error => {
        console.error("Save error:", error);
        errorMessage.value = 'Failed to save changes: ' + error.message;
        setTimeout(() => { errorMessage.value = ''; }, 3000);
        
        // If there's an error, allow editing again
        editingSections.value[section] = true;
    });
};

// Function to cancel editing and revert changes
const cancelEdit = (section) => {
    // Reset to original values from props
    switch(section) {
        case 'basicInfo':
            userData.value.name = props.user?.name || '';
            userData.value.city = props.user?.city || '';
            userData.value.state = props.user?.state || '';
            userData.value.country = props.user?.country || '';
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
        onSuccess: (response) => {
            // Update the profile photo in the UI immediately
            if (response.user && response.user.profile_photo) {
                userData.value.profile_photo = response.user.profile_photo;
            } else {
                // Create a temporary URL for the uploaded file to display immediately
                userData.value.profile_photo = URL.createObjectURL(file);
            }
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
            <h1 class="text-lg font-bold">My Profile</h1>
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
                <ProfileHeader :user="userData" activeTab="profile" class="hidden md:block" />

                <!-- Mobile Profile Navigation - Only visible on mobile -->
                <div class="md:hidden mb-4 overflow-x-auto">
                    <div class="flex  rounded-lg shadow-sm">
                        <Link 
                            :href="route('me.profile')" 
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium bg-primary-dark text-white"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </Link>
                        
                        <Link 
                            :href="route('me.photos')" 
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
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

                <!-- Success/Error Messages -->
                <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ successMessage }}
                </div>
                <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ errorMessage }}
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
                                        <p class="font-medium">
                                            {{ [userData.city, userData.state, userData.country].filter(Boolean).join(', ') }}
                                        </p>
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
                                        <label class="text-sm text-gray-500">City</label>
                                        <input type="text" v-model="userData.city" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                    </div>
                                    
                                    <div>
                                        <label class="text-sm text-gray-500">State/Province</label>
                                        <input type="text" v-model="userData.state" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                    </div>
                                    
                                    <div>
                                        <label class="text-sm text-gray-500">Country</label>
                                        <input type="text" v-model="userData.country" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
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
                                    <select v-model="userData.appearance.hair_color" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="">Select Hair Color</option>
                                        <option value="Black">Black</option>
                                        <option value="Dark Brown">Dark Brown</option>
                                        <option value="Brown">Brown</option>
                                        <option value="Light Brown">Light Brown</option>
                                        <option value="Blonde">Blonde</option>
                                        <option value="Red">Red</option>
                                        <option value="Gray">Gray</option>
                                        <option value="White">White</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Eye Color</label>
                                    <select v-model="userData.appearance.eye_color" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="">Select Eye Color</option>
                                        <option value="Brown">Brown</option>
                                        <option value="Dark Brown">Dark Brown</option>
                                        <option value="Black">Black</option>
                                        <option value="Blue">Blue</option>
                                        <option value="Green">Green</option>
                                        <option value="Gray">Gray</option>
                                        <option value="Hazel">Hazel</option>
                                        <option value="Amber">Amber</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Height (in cm)</label>
                                    <select v-model="userData.appearance.height" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                                        <option value="">Select Height</option>
                                        <option v-for="h in Array.from({length: 73}, (_, i) => (140 + i) + ' cm')" :key="h" :value="h">{{ h }}</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Weight (in kg)</label>
                                    <input type="text" v-model="userData.appearance.weight" placeholder="Enter weight in kg" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
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