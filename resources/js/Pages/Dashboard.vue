<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import DashboardSidebar from '@/Components/DashboardSidebar.vue';
import MatchCard from '@/Components/MatchCard.vue';

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

// Sample data for matches
const matches = ref([
    {
        id: 1,
        name: 'Salihu G',
        age: 31,
        location: 'Garki, Abuja, Nigeria',
        online: true,
        image: '/images/placeholder.jpg',
        compatibility: 85,
        timestamp: '10 min ago'
    },
    {
        id: 2,
        name: 'Abdullahi S',
        age: 31,
        location: 'Garki, Abuja, Nigeria',
        online: true,
        image: '/images/placeholder.jpg',
        compatibility: 85,
        timestamp: '15 min ago'
    }
]);

// Sample data for therapists
const therapists = ref([
    {
        id: 1,
        name: 'Dr. Maria Azad',
        specialty: 'Dermatologist',
        image: '/images/placeholder.jpg',
        online: true
    },
    {
        id: 2,
        name: 'Dr. Maria Azad',
        specialty: 'Dermatologist',
        image: '/images/placeholder.jpg',
        online: true
    },
    {
        id: 3,
        name: 'Dr. Maria Azad',
        specialty: 'Dermatologist',
        image: '/images/placeholder.jpg',
        online: true
    }
]);

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

// Sample data for recent messages
const recentMessages = ref([
    {
        id: 1,
        name: 'Fatenn Saeed',
        message: 'Hi, I am here to discuss some...',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 2,
        name: 'Salwa Al-Qwaiz',
        message: 'Assalamu alaikum.',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 3,
        name: 'Amima Kaleb',
        message: 'Okay we\'ll have a meetup.',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 4,
        name: 'Hanan Hablas',
        message: 'How many kids do you have?',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 5,
        name: 'Emma Wilson',
        message: 'How many kids do you have?',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    },
    {
        id: 6,
        name: 'John Alex',
        message: 'How many kids do you have?',
        image: '/images/placeholder.jpg',
        time: '12:25',
        unread: true
    }
]);

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
});

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
                        :src="$page.props.auth.user.profile_photo_url || '/images/placeholder.jpg'" 
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
                    
                    <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Logout
                    </Link>
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
                
                <!-- Search bar - visible on both mobile and desktop -->
                <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-4">
                    <div class="relative flex-1">
                        <div class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-3">
                            <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="Search" class="w-full border-none bg-transparent outline-none" />
                        </div>
                    </div>
                    
                    <button class="rounded-lg border border-gray-300 bg-white p-3 flex items-center justify-center">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Search bar - Only visible on mobile -->
            <div class="mb-6 md:hidden">
                <div class="flex flex-col gap-3">
                    <div class="relative flex-1">
                        <div class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-3">
                            <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="Search" class="w-full border-none bg-transparent outline-none" />
                        </div>
                    </div>
                    
                    <button class="rounded-lg border border-gray-300 bg-white p-3 flex items-center justify-center">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Match Cards Component -->
            <MatchCard :matches="matches" />
        </div>
        
        <!-- Right Sidebar Component -->
        <div class="hidden lg:block lg:sticky lg:top-0 lg:h-screen">
            <DashboardSidebar 
                :user="$page.props.auth.user"
                :therapists="therapists"
                :messages="recentMessages"
            />
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
