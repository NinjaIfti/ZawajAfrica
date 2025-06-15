<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    user: Object,
});

const profileDropdownOpen = ref(false);
const selectedLanguage = ref({ name: 'English' });
const showLanguageModal = ref(false);

// Language options
const languages = [
    { id: 'en', name: 'English' },
    { id: 'ha', name: 'Hausa' },
    { id: 'yo', name: 'Yoruba' },
    { id: 'ig', name: 'Igbo' }
];

const toggleLanguageModal = () => {
    showLanguageModal.value = !showLanguageModal.value;
    // Close profile dropdown when opening language modal from dropdown on mobile
    if (showLanguageModal.value) {
        profileDropdownOpen.value = false;
    }
};

const selectLanguage = (lang) => {
    selectedLanguage.value = lang;
    showLanguageModal.value = false;
};

// Custom logout function
const logout = () => {
    router.post(route('logout'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh CSRF token after logout
            window.refreshCSRFToken();
            // Redirect to login page
            window.location.href = route('login');
        },
    });
};

// Close dropdown when clicking outside
const closeDropdown = (event) => {
    if (!event.target.closest('.profile-dropdown')) {
        profileDropdownOpen.value = false;
    }
};

// Add event listener when component is mounted
onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

// Remove event listener when component is unmounted
onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div class="flex items-center justify-between mb-4 md:mb-6">
        <div class="flex-1">
            <slot name="title"></slot>
        </div>
        <div class="flex items-center space-x-2 md:space-x-3">
            <!-- Language Selector - Only visible on desktop -->
            <div class="hidden md:flex items-center rounded-lg border border-gray-300 bg-white px-3 md:px-4 py-2 text-sm font-medium cursor-pointer" @click="toggleLanguageModal">
                <span class="mr-2">{{ selectedLanguage.name }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <!-- Profile Dropdown -->
            <div class="relative profile-dropdown">
                <div @click.stop="profileDropdownOpen = !profileDropdownOpen" class="h-8 w-8 md:h-10 md:w-10 overflow-hidden rounded-full bg-gray-300 cursor-pointer">
                    <img :src="user.profile_photo || '/images/placeholder.jpg'" :alt="user.name" class="h-full w-full object-cover" />
                </div>
                
                <!-- Dropdown Menu -->
                <div v-if="profileDropdownOpen" class="absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-100">
                        {{ user.name }}
                    </div>
                    
                    <!-- Language Selector - Only visible on mobile/tablet within dropdown -->
                    <div class="md:hidden px-4 py-2 flex justify-between items-center text-sm text-gray-700 hover:bg-gray-100 cursor-pointer" @click="toggleLanguageModal">
                        <span>Change Language</span>
                        <span class="text-purple-600 font-medium">{{ selectedLanguage.name }}</span>
                    </div>
                    
                    <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Your Profile
                    </Link>
                    <Link :href="route('dashboard')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Settings
                    </Link>
                    <div class="border-t border-gray-100"></div>
                    <button @click="logout" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100">
                        Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Language Selection Modal -->
    <div v-if="showLanguageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative max-w-xs md:max-w-md mx-4 md:mx-auto rounded-lg bg-white p-4 md:p-6 shadow-xl">
            <!-- Modal Header -->
            <div class="mb-4 md:mb-6 flex items-center justify-between">
                <h3 class="text-lg md:text-xl font-bold text-gray-900">Change Language</h3>
                <button 
                    @click="toggleLanguageModal" 
                    class="text-gray-400 hover:text-gray-500"
                >
                    <svg class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Language Options -->
            <div class="space-y-3 md:space-y-4">
                <div 
                    v-for="lang in languages" 
                    :key="lang.id"
                    @click="selectLanguage(lang)"
                    class="flex cursor-pointer items-center justify-between rounded-lg py-2 md:py-3 hover:bg-gray-50"
                >
                    <span class="text-base md:text-lg font-medium text-gray-900">{{ lang.name }}</span>
                    <div 
                        class="flex h-5 w-5 md:h-6 md:w-6 items-center justify-center rounded-full border-2 border-gray-300"
                        :class="{ 'border-purple-600': selectedLanguage.id === lang.id }"
                    >
                        <div 
                            v-if="selectedLanguage.id === lang.id" 
                            class="h-2.5 w-2.5 md:h-3 md:w-3 rounded-full bg-purple-600"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template> 