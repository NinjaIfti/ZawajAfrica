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
};

// State for right sidebar visibility
const isRightSidebarVisible = ref(true);

// Function to toggle right sidebar
const toggleRightSidebar = () => {
    isRightSidebarVisible.value = !isRightSidebarVisible.value;
};

// State for profile dropdown visibility
const profileDropdownOpen = ref(false);

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
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile Menu Toggle Button - Only visible on mobile -->
        <button 
            @click="toggleMobileMenu" 
            class="mobile-menu-toggle fixed top-4 left-4 z-50 p-2 bg-white rounded-md shadow-md md:hidden"
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

        <!-- Mobile Menu Overlay -->
        <div 
            v-if="isMobileMenuOpen" 
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="isMobileMenuOpen = false"
        ></div>

        <!-- Left Sidebar Component - Hidden on mobile, toggled with menu button -->
        <div 
            class="mobile-menu fixed inset-y-0 left-0 transform transition-all duration-300 z-40 md:relative md:translate-x-0"
            :class="{'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen}"
        >
            <Sidebar :user="$page.props.auth.user" />
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-12 md:mt-0">
            <!-- Welcome and Search -->
            <div class="mb-6 md:mb-8">
                <h1 class="text-2xl font-bold mb-4">Welcome {{ $page.props.auth.user.name }}!</h1>
                
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
</style>
