<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import DashboardSidebar from '@/Components/DashboardSidebar.vue';
import MatchCard from '@/Components/MatchCard.vue';

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

// Add click event listener when component is mounted
onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

// Remove event listener when component is unmounted
onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Welcome and Search -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold mb-4">Welcome {{ $page.props.auth.user.name }}!</h1>
                
                <div class="flex items-center space-x-4">
                    <div class="relative flex-1">
                        <div class="flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2">
                            <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="Search" class="w-full border-none bg-transparent outline-none" />
                        </div>
                    </div>
                    
                    <button class="rounded-lg border border-gray-300 bg-white p-2">
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
        <DashboardSidebar 
            :user="$page.props.auth.user"
            :therapists="therapists"
            :messages="recentMessages"
        />
    </div>
</template>

<style scoped>
/* Add custom styles if needed */
</style>
