<script setup>
    import { Link, router } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted } from 'vue';
    import NotificationBell from './NotificationBell.vue';

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
        { id: 'ig', name: 'Igbo' },
    ];

    const toggleLanguageModal = () => {
        showLanguageModal.value = !showLanguageModal.value;
        // Close profile dropdown when opening language modal from dropdown on mobile
        if (showLanguageModal.value) {
            profileDropdownOpen.value = false;
        }
    };

    const selectLanguage = lang => {
        selectedLanguage.value = lang;
        showLanguageModal.value = false;
    };

    // Custom logout function
    const logout = () => {
        router.post(
            route('logout'),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Refresh CSRF token after logout
                    window.refreshCSRFToken();
                    // Redirect to login page
                    window.location.href = route('login');
                },
            }
        );
    };

    // Close dropdown when clicking outside
    const closeDropdown = event => {
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
           

            <!-- Notifications -->
            <NotificationBell />

            <!-- Profile Dropdown -->
            <div class="relative profile-dropdown">
                <div
                    @click.stop="profileDropdownOpen = !profileDropdownOpen"
                    class="h-8 w-8 md:h-10 md:w-10 overflow-hidden rounded-full bg-gray-300 cursor-pointer"
                >
                    <img
                        :src="user.profile_photo || '/images/placeholder.jpg'"
                        :alt="user.name"
                        class="h-full w-full object-cover"
                    />
                </div>

                <!-- Dropdown Menu -->
                <div
                    v-if="profileDropdownOpen"
                    class="absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                >
                    <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-100">
                        {{ user.name }}
                    </div>

                   

                    <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Your Profile
                    </Link>
                    <Link :href="route('dashboard')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Settings
                    </Link>
                    <div class="border-t border-gray-100"></div>
                    <button
                        @click="logout"
                        class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100"
                    >
                        Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>

  
</template>
