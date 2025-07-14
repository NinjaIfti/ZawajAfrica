<script setup>
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
    import axios from 'axios';
    import Sidebar from '@/Components/Sidebar.vue';
    import DashboardSidebar from '@/Components/DashboardSidebar.vue';
    import MatchCard from '@/Components/MatchCard.vue';
    import TherapistWidget from '@/Components/TherapistWidget.vue';
    import PaymentSuccessModal from '@/Components/PaymentSuccessModal.vue';
    import MatchFiltersModal from '@/Components/MatchFiltersModal.vue';
    import TierBadge from '@/Components/TierBadge.vue';
    import AdsterraDisplayAd from '@/Components/AdsterraDisplayAd.vue';
    import ZohoOptinModal from '@/Components/ZohoOptinModal.vue';
    import Modal from '@/Components/Modal.vue';
    import NotificationBell from '@/Components/NotificationBell.vue';
   
    import MobileHeader from '@/Components/MobileHeader.vue';


    const page = usePage();
    const showPaymentSuccessModal = ref(false);
    const showZohoOptinModal = ref(false);
    const showPhoneModal = ref(false);
    const phoneForm = ref({ phone_number: '', error: '', loading: false });

    const props = defineProps({
        user: Object,
        profile: Object,
        profileCompletion: Number,
        auth: Object,
        potentialMatches: Array,
        matches: Array,
        therapists: Array,
        recentMessages: Array,
        userTier: String,
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

    // State for right sidebar visibility
    const isRightSidebarVisible = ref(true);

    // Function to toggle right sidebar
    const toggleRightSidebar = () => {
        isRightSidebarVisible.value = !isRightSidebarVisible.value;
    };

    // Profile dropdown functionality
    const profileDropdownOpen = ref(false);
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

    // Prepare user data for matches
    const processUserData = user => {

        // Calculate age from date of birth if available
        let age = '';
        if (user.dob_day && user.dob_month && user.dob_year) {
            // Convert month name to month number
            const monthMap = {
                Jan: 0,
                Feb: 1,
                Mar: 2,
                Apr: 3,
                May: 4,
                Jun: 5,
                Jul: 6,
                Aug: 7,
                Sep: 8,
                Oct: 9,
                Nov: 10,
                Dec: 11,
            };

            const month = monthMap[user.dob_month];
            if (month !== undefined) {
                const birthDate = new Date(user.dob_year, month, user.dob_day);
                const today = new Date();
                age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                // Ensure age is reasonable (between 18 and 100)
                if (age < 18 || age > 100) {
                    age = '';
                }

            }
        }
        
        // Fallback: Try to calculate from a single date field if available
        if (!age && user.date_of_birth) {
            const birthDate = new Date(user.date_of_birth);
            const today = new Date();
            age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            if (age >= 18 && age <= 100) {

            } else {
                age = '';
            }
        }

        // Format location from city, state, country
        let location = '';
        if (user.city) location += user.city;
        if (user.state) {
            if (location) location += ', ';
            location += user.state;
        }
        if (user.country) {
            if (location) location += ', ';
            location += user.country;
        }


        // Get profile photo - check if user has photos and a primary photo
        let image = '/images/placeholder.jpg';
        if (user.photos && user.photos.length > 0) {
            // Find primary photo if exists
            const primaryPhoto = user.photos.find(photo => photo.is_primary);
            if (primaryPhoto) {
                image = primaryPhoto.url;
            } else if (user.photos[0]) {
                // Otherwise use the first photo
                image = user.photos[0].url;
            }
        } else if (user.profile_photo) {
            // The profile_photo is already a full URL path from the backend
            image = user.profile_photo;
        }

        // Use real compatibility score from backend, fallback to random for demonstration
        const compatibility_score = user.compatibility_score || Math.floor(Math.random() * 30) + 70;

        return {
            id: user.id,
            name: user.name || 'Anonymous',
            age: age || '',
            location: location || '',
            online: user.is_online || false,
            image: image,
            compatibility_score: compatibility_score,
            compatibility: compatibility_score, // Keep for backwards compatibility
            timestamp: 'Active recently', // This would be dynamic in a real app
        };
    };

    // Process potential matches from the server
    const matches = computed(() => {
        if (props.potentialMatches && props.potentialMatches.length > 0) {
            // The MatchingService already formats the data, so we just need to add the missing fields
            return props.potentialMatches.map(match => ({
                id: match.id,
                name: match.name,
                age: match.age,
                location: match.location,
                online: match.is_online || false,
                image: match.profile_photo || '/images/placeholder.jpg',
                compatibility_score: match.compatibility_score || 0,
                compatibility: match.compatibility_score || 0,
                timestamp: 'Active recently',
                tier: match.tier || 'free' // Preserve tier information from backend
            }));
        }

        return [];
    });

    // Add state for therapists panel
    const isTherapistsPanelExpanded = ref(true);

    // Function to toggle therapists panel
    const toggleTherapistsPanel = () => {
        isTherapistsPanelExpanded.value = !isTherapistsPanelExpanded.value;
    };

    // Add state for messages panel
    const isMessagesPanelExpanded = ref(true);

    // Function to toggle messages panel
    const toggleMessagesPanel = () => {
        isMessagesPanelExpanded.value = !isMessagesPanelExpanded.value;
    };

    // Search and filter functionality
    const searchQuery = ref('');
    const showFiltersModal = ref(false);
    const appliedFilters = ref({});
    const isSearching = ref(false);
    const searchResults = ref([]);
    const isLoading = ref(false);

    // Function to handle search
    const handleSearch = async () => {
        if (!searchQuery.value.trim()) {
            searchResults.value = [];
            return;
        }

        isSearching.value = true;
        isLoading.value = true;

        try {
            const response = await axios.get('/api/matches/search', {
                params: {
                    q: searchQuery.value,
                    filters: appliedFilters.value,
                },
            });

            if (response.data.success) {
                searchResults.value = response.data.data.matches || [];
            }
        } catch (error) {
            console.error('Search failed:', error);
            searchResults.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    // Function to clear search
    const clearSearch = () => {
        searchQuery.value = '';
        searchResults.value = [];
        isSearching.value = false;
    };

    // Function to apply filters
    const applyFilters = async filters => {
        appliedFilters.value = { ...filters };
        isLoading.value = true;

        try {
            const response = await axios.post('/api/matches/filter', {
                filters: filters,
            });

            if (response.data.success) {
                // Update matches with filtered results
                const filteredMatches = response.data.data.matches || [];

                // Replace the matches computed property with filtered results
                if (filteredMatches.length > 0) {
                    // Update the matches directly
                    await nextTick();
                    // You might need to emit an event or use a different approach
                    // to update the MatchCard component with new data
                    window.location.reload(); // Temporary solution - reload page with filters
                }
            }
        } catch (error) {
            console.error('Filter failed:', error);
        } finally {
            isLoading.value = false;
        }
    };

    // Function to clear filters
    const clearFilters = () => {
        appliedFilters.value = {};
        window.location.reload(); // Reload to get unfiltered matches
    };

    // Function to show filters modal
    const showFilters = () => {
        showFiltersModal.value = true;
    };

    // Get user tier for filters
    const userTier = computed(() => {
        // Use the tier passed from the backend first (most reliable)
        if (props.userTier) {
            return props.userTier;
        }

        // Fallback to calculating from user data
        const user = props.user || page.props.auth?.user;

        if (!user) return 'free';

        // Check if user has an active subscription
        if (user.subscription_status === 'active' && user.subscription_plan) {
            return user.subscription_plan.toLowerCase();
        }

        return 'free';
    });

    // Get current user tier for display in welcome message
    const currentUserTier = computed(() => {
        const user = page.props.auth?.user;
        if (!user) return 'free';

        // Check if user has an active subscription
        if (user.subscription_status === 'active' && user.subscription_plan) {
            // Check if subscription hasn't expired
            if (!user.subscription_expires_at || new Date(user.subscription_expires_at) > new Date()) {
                return user.subscription_plan.toLowerCase();
            }
        }

        return 'free';
    });

    // Debounced search
    let searchTimeout;
    watch(searchQuery, newValue => {
        clearTimeout(searchTimeout);
        if (newValue.trim()) {
            searchTimeout = setTimeout(() => {
                handleSearch();
            }, 500);
        } else {
            clearSearch();
        }
    });

    // Display matches - either search results or regular matches
    const displayMatches = computed(() => {
        if (isSearching.value && searchResults.value.length > 0) {
            return searchResults.value.map(user => processUserData(user));
        }
        return matches.value;
    });



    // Close mobile menu when clicking outside
    const closeMobileMenu = e => {
        if (isMobileMenuOpen.value && !e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-toggle')) {
            isMobileMenuOpen.value = false;
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Add click event listener when component is mounted
    onMounted(() => {
        document.addEventListener('click', closeMobileMenu);
        document.addEventListener('click', closeDropdown);

        // Check for payment success on page load
        if (page.props.flash?.payment_success) {
            showPaymentSuccessModal.value = true;
        }

        // Show Zoho opt-in modal only if user hasn't skipped or signed up
        setTimeout(() => {
            const hasSkipped = localStorage.getItem('zoho_optin_skipped');
            const hasSignedUp = localStorage.getItem('zoho_optin_completed');
            
            // Only show modal if user hasn't skipped and hasn't signed up
            if (!hasSkipped && !hasSignedUp) {
                showZohoOptinModal.value = true;
            }
        }, 2000); // Show after 2 seconds

        // Check if user is missing phone_number
        const user = page.props.auth?.user || props.user;
        if (user && (!user.phone_number || user.phone_number.trim() === '')) {
            showPhoneModal.value = true;
            phoneForm.value.phone_number = '';
        }
    });

    const closePaymentSuccessModal = () => {
        showPaymentSuccessModal.value = false;
    };

    const closeZohoOptinModal = () => {
        showZohoOptinModal.value = false;
    };

    const submitPhoneNumber = async () => {
        phoneForm.value.loading = true;
        phoneForm.value.error = '';
        try {
            // Get current user details
            const user = page.props.auth?.user || props.user;
            await axios.patch('/profile', {
                name: user.name,
                email: user.email,
                phone_number: phoneForm.value.phone_number
            });
            showPhoneModal.value = false;
            window.location.reload();
        } catch (error) {
            phoneForm.value.error = error.response?.data?.errors?.phone_number?.[0] || 'Failed to update phone number.';
        } finally {
            phoneForm.value.loading = false;
        }
    };

    // Remove event listener when component is unmounted
    onUnmounted(() => {
        document.removeEventListener('click', closeMobileMenu);
        document.removeEventListener('click', closeDropdown);
        document.body.classList.remove('overflow-hidden');
    });

    const validatePhoneInput = (e) => {
        const input = e.target.value;
        const sanitizedInput = input.replace(/[^0-9+\-\(\)\s]/g, '');
        phoneForm.value.phone_number = sanitizedInput;
    };
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile Header Component -->
        <MobileHeader 
            :user="$page.props.auth.user" 
            :user-tier="currentUserTier" 
            :is-mobile-menu-open="isMobileMenuOpen"
            @toggle-mobile-menu="toggleMobileMenu"
        />

        <!-- Mobile Menu Overlay -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar Component - Fixed position -->
        <aside
            class="mobile-menu fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:translate-x-0"
            :class="{ 'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen }"
        >
            <Sidebar :user="$page.props.auth.user" />
        </aside>

        <!-- Main Content - Add left margin on desktop to account for fixed sidebar -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0 md:ml-64">
            <!-- Header for desktop -->
            <div class="mb-6 md:mb-8 hidden md:block">
                <div class="flex items-center justify-between mb-4 md:mb-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold">Welcome {{ $page.props.auth.user.name }}!</h1>
                            <TierBadge :tier="currentUserTier" size="sm" />
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-3">
                        <!-- Notifications -->
                        <NotificationBell />

                        
                    </div>
                </div>
            </div>

            <!-- Search section - Only visible on desktop -->
            <div class="mb-6 md:mb-8 hidden md:block">

                <!-- Search bar with integrated filter button - desktop -->
                <div class="flex items-center rounded-lg border border-gray-300 bg-white">
                    <div class="flex-1 flex items-center px-4 py-2">
                        <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by name..."
                            class="w-full border-none bg-transparent outline-none"
                            @keyup.enter="handleSearch"
                        />
                        <!-- Clear search button -->
                        <button v-if="searchQuery" @click="clearSearch" class="ml-2 p-1 hover:bg-gray-100 rounded">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                ></path>
                            </svg>
                        </button>
                    </div>
                    <button
                        @click="showFilters"
                        class="border-l border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-50 flex items-center"
                        :class="
                            Object.keys(appliedFilters).length > 0 ? 'bg-purple-50 text-purple-600' : 'text-gray-500'
                        "
                    >
                        <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                            />
                        </svg>
                        <span v-if="Object.keys(appliedFilters).length > 0" class="text-xs">
                            ({{ Object.keys(appliedFilters).length }})
                        </span>
                    </button>
                </div>

                <!-- Search status indicator -->
                <div v-if="isSearching && searchQuery" class="mt-2 text-sm text-gray-600">
                    <span v-if="isLoading">Searching...</span>
                    <span v-else-if="searchResults.length === 0">No users found for "{{ searchQuery }}"</span>
                    <span v-else>Found {{ searchResults.length }} user(s) for "{{ searchQuery }}"</span>
                </div>
            </div>

            <!-- Search bar - Only visible on mobile -->
            <div class="mb-6 md:hidden">
                <!-- Search bar with integrated filter button - mobile -->
                <div class="flex items-center rounded-lg border border-gray-300 bg-white">
                    <div class="flex-1 flex items-center px-4 py-2">
                        <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by name..."
                            class="w-full border-none bg-transparent outline-none"
                            @keyup.enter="handleSearch"
                        />
                        <!-- Clear search button -->
                        <button v-if="searchQuery" @click="clearSearch" class="ml-2 p-1 hover:bg-gray-100 rounded">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                ></path>
                            </svg>
                        </button>
                    </div>
                    <button
                        @click="showFilters"
                        class="border-l border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-50"
                        :class="
                            Object.keys(appliedFilters).length > 0 ? 'bg-purple-50 text-purple-600' : 'text-gray-500'
                        "
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Search status indicator - mobile -->
                <div v-if="isSearching && searchQuery" class="mt-2 text-sm text-gray-600">
                    <span v-if="isLoading">Searching...</span>
                    <span v-else-if="searchResults.length === 0">No users found for "{{ searchQuery }}"</span>
                    <span v-else>Found {{ searchResults.length }} user(s) for "{{ searchQuery }}"</span>
                </div>
            </div>

            <!-- Dashboard Feed Ad (between search and matches) -->
                                        <AdsterraDisplayAd zone-name="feed" />

            <!-- Match Cards Component -->
            <MatchCard :matches="displayMatches" :userTier="userTier" />

            <!-- Display Ad (after matches) -->
                                    <AdsterraDisplayAd zone-name="banner" />

            <!-- Loading indicator -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-lg">
                    <svg
                        class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    Loading...
                </div>
            </div>
        </div>

        <!-- Right Sidebar Component -->
        <div class="hidden lg:block lg:sticky lg:top-0 lg:h-screen">
            <DashboardSidebar :user="$page.props.auth.user" :therapists="therapists" :messages="recentMessages" />
        </div>

        <!-- Payment Success Modal -->
        <PaymentSuccessModal
            :show="showPaymentSuccessModal"
            :payment-type="page.props.flash?.payment_type || 'general'"
            @close="closePaymentSuccessModal"
        />

        <!-- Filter Modal -->
        <MatchFiltersModal
            :show="showFiltersModal"
            :user-tier="userTier"
            :current-filters="appliedFilters"
            @close="showFiltersModal = false"
            @apply-filters="applyFilters"
            @clear-filters="clearFilters"
        />

        <!-- Zoho Opt-in Modal -->
        <ZohoOptinModal
            :show="showZohoOptinModal"
            @close="closeZohoOptinModal"
        />

        <Modal :show="showPhoneModal" :closeable="false" maxWidth="sm">
            <div class="p-8 flex flex-col items-center">
                <h2 class="text-2xl font-bold mb-2 text-purple-700">Add Your Phone Number</h2>
                <p class="mb-4 text-gray-600 text-center">For your security and to continue using ZawajAfrica, please enter your phone number. You will not be able to use the app until you provide it.</p>
                <input
                    v-model="phoneForm.phone_number"
                    type="tel"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-2 focus:outline-none focus:ring-2 focus:ring-purple-500 text-lg"
                    placeholder="Enter your phone number"
                    :disabled="phoneForm.loading"
                    pattern="[0-9+\-\(\)\s]+"
                    @input="validatePhoneInput"
                />
                <div v-if="phoneForm.error" class="text-red-600 text-sm mb-2">{{ phoneForm.error }}</div>
                <button
                    @click="submitPhoneNumber"
                    :disabled="phoneForm.loading || !phoneForm.phone_number"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg transition duration-200 mt-2 disabled:opacity-50"
                >
                    <span v-if="phoneForm.loading">Saving...</span>
                    <span v-else>Save Phone Number</span>
                </button>
            </div>
        </Modal>

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
        <Modal :show="showPasswordModal" :closeable="false" maxWidth="sm">
            <div class="p-8 flex flex-col items-center">
                <h2 class="text-2xl font-bold mb-2 text-red-700">Confirm Account Deletion</h2>
                <p class="mb-4 text-gray-600 text-center">Please enter your password to confirm account deletion.</p>
                <input
                    v-model="deletePassword"
                    type="password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-2 focus:outline-none focus:ring-2 focus:ring-red-500 text-lg"
                    placeholder="Enter your password"
                    :disabled="deletePasswordLoading"
                    @keyup.enter="confirmPasswordAndDelete"
                />
                <div v-if="deletePasswordError" class="text-red-600 text-sm mb-2">{{ deletePasswordError }}</div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button
                        @click="cancelPasswordModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                        :disabled="deletePasswordLoading"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmPasswordAndDelete"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
                        :disabled="deletePasswordLoading || !deletePassword"
                    >
                        <span v-if="deletePasswordLoading">Deleting...</span>
                        <span v-else>Confirm Deletion</span>
                    </button>
                </div>
            </div>
        </Modal>
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
