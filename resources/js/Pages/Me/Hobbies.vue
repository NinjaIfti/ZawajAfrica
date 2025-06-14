<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Load hobbies data from user object or initialize empty values
const interests = ref({
    entertainment: props.user?.interests?.entertainment || '',
    food: props.user?.interests?.food || '',
    music: props.user?.interests?.music || '',
    sports: props.user?.interests?.sports || '',
});

// For tracking editing state
const isEditing = ref(false);
const isSaving = ref(false);
const showSuccess = ref(false);

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

// Update interest field
const updateInterest = (field, value) => {
    interests.value[field] = value;
};

// Save changes
const saveChanges = () => {
    isSaving.value = true;
    
    // Use Inertia to submit the data
    router.patch(route('me.hobbies.update'), interests.value, {
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
    <Head title="My Hobbies" />

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
            <h1 class="text-lg font-bold">My Hobbies</h1>
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
                <ProfileHeader :user="props.user" activeTab="hobbies" class="hidden md:block" />

                <!-- Hobbies Content -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">My Hobbies & Interests</h2>
                    
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Hobbies & Interests</h2>
                        
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
                                Edit Interests
                            </button>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">Let others know what your interests are and help us connect you with users that may have similar interests.</p>
                    
                    <div class="space-y-6">
                        <!-- Entertainment -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="entertainment" class="block text-gray-700 font-medium">What do you do for fun/entertainment?</label>
                            </div>
                            <input 
                                id="entertainment" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="interests.entertainment"
                                @input="updateInterest('entertainment', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Antiques, Astrology, Bars / Pubs / Nightclubs, Collecting, etc."
                            >
                        </div>

                        <!-- Food -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="food" class="block text-gray-700 font-medium">What sort of food do you like?</label>
                            </div>
                            <input 
                                id="food" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="interests.food"
                                @input="updateInterest('food', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Italian, Indian, Chinese, Halal, Vegetarian, etc."
                            >
                        </div>

                        <!-- Music -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="music" class="block text-gray-700 font-medium">What sort of music do you like?</label>
                            </div>
                            <input 
                                id="music" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="interests.music"
                                @input="updateInterest('music', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Pop, Rock, Classical, Jazz, Hip Hop, etc."
                            >
                        </div>

                        <!-- Sports -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label for="sports" class="block text-gray-700 font-medium">What sports do you play or like to watch?</label>
                            </div>
                            <input 
                                id="sports" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                v-model="interests.sports"
                                @input="updateInterest('sports', $event.target.value)"
                                :disabled="!isEditing"
                                placeholder="Football, Basketball, Cricket, Swimming, etc."
                            >
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

/* Form styling */
input:disabled {
    background-color: #f9fafb;
    cursor: not-allowed;
}

button {
    transition: all 0.2s;
}
</style>