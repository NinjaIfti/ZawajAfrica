<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    user: Object
});

const showTherapistDropdown = ref(false);

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

const toggleTherapistDropdown = () => {
    showTherapistDropdown.value = !showTherapistDropdown.value;
};
</script>

<template>
    <!-- Left Sidebar -->
    <div class="w-64 bg-white shadow-md h-full overflow-y-auto flex flex-col">
        <!-- Logo -->
        <div class="p-4 mb-6">
            <div>
                <img src="/images/dash.png" alt="ZawajAfrica" class="h-10">
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="space-y-2 px-3 flex-grow">
            <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('dashboard') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('dashboard') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </Link>
            
            <!-- Therapist Section with Dropdown -->
            <div class="relative">
                <button @click="toggleTherapistDropdown" class="w-full flex items-center justify-between rounded-lg px-4 py-3 text-base font-medium" :class="route().current('therapists.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                    <div class="flex items-center">
                        <svg class="mr-3 h-6 w-6" :class="route().current('therapists.*') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Therapists</span>
                    </div>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="showTherapistDropdown ? 'rotate-180' : ''" :style="route().current('therapists.*') ? 'color: white' : 'color: #6b7280'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div v-show="showTherapistDropdown" class="mt-1 ml-6 space-y-1">
                    <Link :href="route('therapists.index')" class="flex items-center rounded-lg px-4 py-2 text-sm font-medium" :class="route().current('therapists.index') ? 'bg-purple-100 text-purple-600' : 'text-gray-600 hover:bg-gray-50'">
                        <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Find Therapist</span>
                    </Link>
                    
                    <Link :href="route('therapists.bookings')" class="flex items-center rounded-lg px-4 py-2 text-sm font-medium" :class="route().current('therapists.bookings') ? 'bg-purple-100 text-purple-600' : 'text-gray-600 hover:bg-gray-50'">
                        <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Booking Details</span>
                    </Link>
                </div>
            </div>
            
            <Link :href="route('messages')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('messages') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('messages') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span>Messages</span>
            </Link>
            
            <Link :href="route('chatbot.index')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('chatbot.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('chatbot.*') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span>AI Chatbot</span>
            </Link>
            
            <Link :href="route('notifications.index')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('notifications.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('notifications.*') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>Notifications</span>
            </Link>
            <br>
            <hr class="border-gray-400">
            <Link :href="route('me.profile')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('me.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('me.*') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>My Profile</span>
            </Link>
            
            <Link :href="route('subscription.index')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('subscription.index') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('subscription.index') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Subscription Plans</span>
            </Link>
            
            <button @click="logout" class="w-full flex items-center rounded-lg px-4 py-3 text-base font-medium text-red-600 hover:bg-gray-50">
                <svg class="mr-3 h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
            </button>
        </nav>
        
        <!-- Upgrade Membership - Only show if user doesn't have active subscription -->
        <div v-if="!user.subscription_status || user.subscription_status !== 'active'" class="p-4 w-full">
            <Link :href="route('subscription.index')" class="block">
                <div class="rounded-lg bg-purple-700 p-4 text-center text-white relative overflow-hidden cursor-pointer hover:bg-purple-800 transition-colors">
                <!-- Diagonal gradient strips -->
                <div class="absolute -top-8 right-10 w-6 h-48 bg-gradient-to-b from-purple-700 via-purple-500 to-purple-700 transform rotate-45"></div>
                <div class="absolute -top-16 -right-2 w-6 h-48 bg-gradient-to-b from-purple-700 via-purple-500 to-purple-700 transform rotate-45"></div>
                <div class="absolute -top-12 right-24 w-6 h-48 bg-gradient-to-b from-purple-700 via-purple-500 to-purple-700 transform rotate-45"></div>
                
                <!-- Side strip images -->
                <img src="/images/member/side1.png" alt="Side Decoration" class="absolute left-0 top-0 h-full">
                <img src="/images/member/side2.png" alt="Side Decoration" class="absolute right-0 top-0 h-full">
                
                <!-- Circle element -->
                <div class="absolute bottom-4 right-8 w-5 h-5 border border-green-800 rounded-full"></div>
                
                <!-- Content -->
                <div class="relative z-10 flex flex-col items-center">
                    <!-- Icon in orange circle -->
                    <div class="mb-3 w-9 h-9 rounded-xl flex items-center justify-center">
                        <img src="/images/member/lock.png" alt="Membership Lock" class="w-8 h-8">
                    </div>
                    
                    <p class="mt-1 text-s font-bold text-gray-200">Upgrade Membership</p>
                    
                    <!-- Button -->
                    <div class="mt-6 flex items-center justify-center">
                        <span class="font-medium">Upgrade Now</span>
                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
                </div>
            </Link>
        </div>
    </div>
</template>

<style scoped>
/* Add some transitions for smooth opening/closing */
.translate-x-0 {
    transform: translateX(0);
}

.-translate-x-full {
    transform: translateX(-100%);
}

@media (min-width: 768px) {
    .md\:translate-x-0 {
        transform: translateX(0) !important;
    }
}
</style> 