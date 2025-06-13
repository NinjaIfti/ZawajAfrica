<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    user: Object,
});

const profileDropdownOpen = ref(false);
const selectedLanguage = ref({ name: 'English' });
const showLanguageModal = ref(false);

const toggleLanguageModal = () => {
    showLanguageModal.value = !showLanguageModal.value;
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
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            <slot name="title"></slot>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Language Selector -->
            <div class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium cursor-pointer" @click="toggleLanguageModal">
                <span class="mr-2">{{ selectedLanguage.name }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <!-- Profile Dropdown -->
            <div class="relative profile-dropdown">
                <div @click.stop="profileDropdownOpen = !profileDropdownOpen" class="h-10 w-10 overflow-hidden rounded-full bg-gray-300 cursor-pointer">
                    <img src="/images/placeholder.jpg" alt="Profile" class="h-full w-full object-cover" />
                </div>
                
                <!-- Dropdown Menu -->
                <div v-if="profileDropdownOpen" class="absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Your Profile
                    </Link>
                    <Link :href="route('dashboard')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Settings
                    </Link>
                    <div class="border-t border-gray-100"></div>
                    <Link :href="route('logout')" method="post" as="button" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100">
                        Log Out
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template> 