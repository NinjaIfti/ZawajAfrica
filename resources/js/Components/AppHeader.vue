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
    const showDeleteConfirmation = ref(false);
    const showPasswordModal = ref(false);
    const deletePassword = ref('');
    const deletePasswordError = ref('');
    const deletePasswordLoading = ref(false);
    const passwordForm = ref({
        current_password: '',
        password: '',
        password_confirmation: ''
    });
    const passwordErrors = ref({});
    const passwordLoading = ref(false);

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

    // Show password change modal
    const showPasswordChangeModal = () => {
        profileDropdownOpen.value = false;
        showPasswordModal.value = true;
        // Reset form and errors
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: ''
        };
        passwordErrors.value = {};
    };

    // Handle password change
    const changePassword = () => {
        passwordLoading.value = true;
        passwordErrors.value = {};

        router.put(
            route('password.update'),
            passwordForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    showPasswordModal.value = false;
                    passwordLoading.value = false;
                    // Show success message
                    alert('Password changed successfully!');
                },
                onError: (errors) => {
                    passwordErrors.value = errors;
                    passwordLoading.value = false;
                }
            }
        );
    };

    const cancelPasswordChange = () => {
        showPasswordModal.value = false;
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: ''
        };
        passwordErrors.value = {};
    };

    // Delete account function
    const confirmDeleteAccount = () => {
        profileDropdownOpen.value = false;
        showDeleteConfirmation.value = true;
    };

    const deleteAccount = () => {
        showDeleteConfirmation.value = false;
        showPasswordModal.value = true;
        deletePassword.value = '';
        deletePasswordError.value = '';
    };

    const confirmPasswordAndDelete = () => {
        if (!deletePassword.value.trim()) {
            deletePasswordError.value = 'Password is required';
            return;
        }

        deletePasswordLoading.value = true;
        deletePasswordError.value = '';

        router.delete(
            route('profile.destroy'),
            {
                data: {
                    password: deletePassword.value
                },
                preserveScroll: true,
                onSuccess: () => {
                    // Redirect to home page after account deletion
                    window.location.href = '/';
                },
                onError: (errors) => {
                    deletePasswordLoading.value = false;
                    console.error('Failed to delete account:', errors);
                    if (errors.password) {
                        deletePasswordError.value = 'Invalid password. Please try again.';
                    } else {
                        deletePasswordError.value = 'Failed to delete account. Please try again.';
                    }
                },
                onFinish: () => {
                    deletePasswordLoading.value = false;
                }
            }
        );
    };

    const cancelPasswordModal = () => {
        showPasswordModal.value = false;
        deletePassword.value = '';
        deletePasswordError.value = '';
        deletePasswordLoading.value = false;
    };

    const cancelDeleteAccount = () => {
        showDeleteConfirmation.value = false;
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

                    <Link :href="route('dashboard')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Settings
                    </Link>
                    
                    <button
                        @click="showPasswordChangeModal"
                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                    >
                        Change Password
                    </button>
                    
                    <button
                        @click="confirmDeleteAccount"
                        class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100"
                    >
                        Delete Account
                    </button>
                    
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

    <!-- Password Change Modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
            
            <form @submit.prevent="changePassword" class="space-y-4">
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Current Password
                    </label>
                    <input
                        id="current_password"
                        v-model="passwordForm.current_password"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': passwordErrors.current_password }"
                        required
                    />
                    <div v-if="passwordErrors.current_password" class="text-red-500 text-sm mt-1">
                        {{ passwordErrors.current_password }}
                    </div>
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        New Password
                    </label>
                    <input
                        id="password"
                        v-model="passwordForm.password"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': passwordErrors.password }"
                        required
                    />
                    <div v-if="passwordErrors.password" class="text-red-500 text-sm mt-1">
                        {{ passwordErrors.password }}
                    </div>
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm New Password
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': passwordErrors.password_confirmation }"
                        required
                    />
                    <div v-if="passwordErrors.password_confirmation" class="text-red-500 text-sm mt-1">
                        {{ passwordErrors.password_confirmation }}
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button
                        type="button"
                        @click="cancelPasswordChange"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                        :disabled="passwordLoading"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                        :disabled="passwordLoading"
                    >
                        {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div v-if="showDeleteConfirmation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Account</h3>
            <p class="text-gray-600 mb-6">
                Are you sure you want to delete your account? This action cannot be undone and will permanently remove all your data, matches, and messages.
            </p>
            <div class="flex justify-end space-x-3">
                <button
                    @click="cancelDeleteAccount"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                >
                    Cancel
                </button>
                <button
                    @click="deleteAccount"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                >
                    Delete Account
                </button>
            </div>
        </div>
    </div>

    <!-- Password Confirmation Modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Password</h3>
            
            <form @submit.prevent="confirmPasswordAndDelete" class="space-y-4">
                <!-- Password -->
                <div>
                    <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <input
                        id="delete_password"
                        v-model="deletePassword"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': deletePasswordError }"
                        required
                    />
                    <div v-if="deletePasswordError" class="text-red-500 text-sm mt-1">
                        {{ deletePasswordError }}
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button
                        type="button"
                        @click="cancelPasswordModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                        :disabled="deletePasswordLoading"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
                        :disabled="deletePasswordLoading"
                    >
                        {{ deletePasswordLoading ? 'Deleting...' : 'Delete Account' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
