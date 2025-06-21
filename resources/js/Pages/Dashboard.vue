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

const page = usePage();
const showPaymentSuccessModal = ref(false);

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

// State for profile dropdown visibility
const profileDropdownOpen = ref(false);

// Add state for language selection
const currentLanguage = ref('English');
const languages = ref(['English', 'Arabic', 'French', 'Swahili']);

// Function to change language
const changeLanguage = (language) => {
    currentLanguage.value = language;
    // Here you would add logic to actually change the app language
    profileDropdownOpen.value = false;
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

// Prepare user data for matches
const processUserData = (user) => {
    // Calculate age from date of birth if available
    let age = '';
    if (user.dob_day && user.dob_month && user.dob_year) {
        // Convert month name to month number
        const monthMap = {
            'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3,
            'May': 4, 'Jun': 5, 'Jul': 6, 'Aug': 7,
            'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
        };
        
        const month = monthMap[user.dob_month] || 0;
        const birthDate = new Date(user.dob_year, month, user.dob_day);
        const today = new Date();
        age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
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
    
    // Mock compatibility score (to be replaced with real algorithm later)
    const compatibility = Math.floor(Math.random() * 30) + 70; // Random between 70-99%
    
    return {
        id: user.id,
        name: user.name || 'Anonymous',
        age: age || '?',
        location: location || 'Location not specified',
        online: true, // This would be dynamic in a real app
        image: image,
        compatibility: compatibility,
        timestamp: 'Active recently' // This would be dynamic in a real app
    };
};

// Process potential matches from the server
const matches = computed(() => {
    if (props.potentialMatches && props.potentialMatches.length > 0) {
        return props.potentialMatches.map(user => processUserData(user));
    }
    // Fallback to sample data if no matches are available
    return [
    {
        id: 1,
            name: 'Sample User',
            age: '?',
            location: 'Location not specified',
        online: true,
        image: '/images/placeholder.jpg',
        compatibility: 85,
            timestamp: 'Active recently'
        }
    ];
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
                filters: appliedFilters.value
            }
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
const applyFilters = async (filters) => {
    appliedFilters.value = { ...filters };
    isLoading.value = true;
    
    try {
        const response = await axios.post('/api/matches/filter', {
            filters: filters
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
        return user.subscription_plan;
    }
    
    return 'free';
});

// Debounced search
let searchTimeout;
watch(searchQuery, (newValue) => {
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

// Close the profile dropdown when clicking outside
const closeDropdown = (e) => {
    // If the click is outside the dropdown and the dropdown is open, close it
    if (profileDropdownOpen.value && !e.target.closest('.profile-dropdown')) {
        profileDropdownOpen.value = false;
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
    document.addEventListener('click', closeDropdown);
    document.addEventListener('click', closeMobileMenu);
    
    // Check for payment success on page load
    if (page.props.flash?.payment_success) {
        showPaymentSuccessModal.value = true;
    }
});

const closePaymentSuccessModal = () => {
    showPaymentSuccessModal.value = false;
};

// Remove event listener when component is unmounted
onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    document.removeEventListener('click', closeMobileMenu);
    document.body.classList.remove('overflow-hidden');
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu and welcome text - Only visible on mobile -->
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
            
            <!-- Welcome text beside hamburger on mobile -->
            <h1 class="text-lg font-bold">Welcome {{ $page.props.auth.user.name }}!</h1>
            
            <!-- Profile dropdown on mobile - right aligned -->
            <div class="profile-dropdown relative ml-auto">
                <button 
                    @click="profileDropdownOpen = !profileDropdownOpen" 
                    class="flex items-center"
                >
                    <img 
                        :src="$page.props.auth.user.profile_photo || '/images/placeholder.jpg'" 
                        alt="Profile" 
                        class="h-8 w-8 rounded-full object-cover"
                    >
                </button>
                
                <!-- Dropdown Menu -->
                <div 
                    v-if="profileDropdownOpen" 
                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                >
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm text-gray-500">Signed in as</p>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $page.props.auth.user.email }}</p>
                    </div>
                    
                    <!-- Language Selector -->
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Language</p>
                        <div class="flex flex-wrap gap-1">
                            <button 
                                v-for="language in languages" 
                                :key="language"
                                @click="changeLanguage(language)"
                                class="text-xs px-2 py-1 rounded-full"
                                :class="language === currentLanguage ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                {{ language }}
                            </button>
                        </div>
                    </div>
                    
                    <Link :href="route('me.profile')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Settings
                    </Link>
                    
                    <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Logout
                    </button>
                </div>
            </div>
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
            <!-- Welcome and Search - Only visible on desktop -->
            <div class="mb-6 md:mb-8 hidden md:block">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold">Welcome {{ $page.props.auth.user.name }}!</h1>
                    
                    
                </div>
                
                <!-- Search bar with integrated filter button - desktop -->
                <div class="flex items-center rounded-lg border border-gray-300 bg-white">
                    <div class="flex-1 flex items-center px-4 py-2">
                        <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search by name..." 
                            class="w-full border-none bg-transparent outline-none"
                            @keyup.enter="handleSearch"
                        />
                        <!-- Clear search button -->
                        <button 
                            v-if="searchQuery" 
                            @click="clearSearch"
                            class="ml-2 p-1 hover:bg-gray-100 rounded"
                        >
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <button 
                        @click="showFilters"
                        class="border-l border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-50 flex items-center"
                        :class="Object.keys(appliedFilters).length > 0 ? 'bg-purple-50 text-purple-600' : 'text-gray-500'"
                    >
                        <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search by name..." 
                            class="w-full border-none bg-transparent outline-none"
                            @keyup.enter="handleSearch"
                        />
                        <!-- Clear search button -->
                        <button 
                            v-if="searchQuery" 
                            @click="clearSearch"
                            class="ml-2 p-1 hover:bg-gray-100 rounded"
                        >
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <button 
                        @click="showFilters"
                        class="border-l border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-50"
                        :class="Object.keys(appliedFilters).length > 0 ? 'bg-purple-50 text-purple-600' : 'text-gray-500'"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
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
            
            <!-- Match Cards Component -->
            <MatchCard :matches="displayMatches" />
            
            <!-- Loading indicator -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-lg">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar Component -->
        <div class="hidden lg:block lg:sticky lg:top-0 lg:h-screen">
        <DashboardSidebar 
            :user="$page.props.auth.user"
            :therapists="therapists"
            :messages="recentMessages"
        />
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
