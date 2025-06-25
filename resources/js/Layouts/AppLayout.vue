<template>
    <div class="min-h-screen bg-gray-50">
        <!-- AdSense Manager Component -->
        <AdSenseManager 
            :adsense-config="$page.props.adsense.config"
            :show-on-page="$page.props.adsense.show_on_page"
            :consent-data="$page.props.adsense.consent"
        />
        
        <!-- AdSense Notice for Free Users -->
        <AdSenseNotice 
            :user-tier="getUserTier()"
            :current-page="getCurrentPageName()"
        />
        
        <!-- Header -->
        <AppHeader />
        
        <Head :title="title" />
        
        <!-- Top Navigation Bar -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <Link :href="route('dashboard')" class="flex-shrink-0">
                            <img src="/images/dash.png" alt="ZawajAfrica" class="h-8 w-auto" />
                        </Link>
                        
                        <!-- Breadcrumb -->
                        <nav class="ml-8 flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-4">
                                <li>
                                    <Link :href="route('dashboard')" class="text-gray-500 hover:text-gray-700">
                                        Dashboard
                                    </Link>
                                </li>
                                <li>
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </li>
                                <li>
                                    <span class="text-gray-900 font-medium">{{ title }}</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <Link :href="route('notifications.index')" class="text-gray-500 hover:text-gray-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 1l8 8-8 8V1z" />
                            </svg>
                        </Link>
                        
                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button @click="showProfileDropdown = !showProfileDropdown" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <img 
                                    class="h-8 w-8 rounded-full object-cover" 
                                    :src="$page.props.auth?.user?.profile_photo || '/images/placeholder.jpg'" 
                                    :alt="$page.props.auth?.user?.name || 'User'"
                                />
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div v-show="showProfileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <Link :href="route('me.profile')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Profile
                                    </Link>
                                    <Link :href="route('subscription.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Subscription
                                    </Link>
                                    <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AdSenseManager from '@/Components/AdSenseManager.vue'
import AdSenseNotice from '@/Components/AdSenseNotice.vue'

defineProps({
    title: {
        type: String,
        default: 'ZawajAfrica'
    }
})

const showProfileDropdown = ref(false)

// Custom logout function
const logout = () => {
    router.post(route('logout'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh CSRF token after logout
            if (window.refreshCSRFToken) {
                window.refreshCSRFToken();
            }
            // Redirect to login page
            window.location.href = route('login');
        },
    });
};

// Close dropdown when clicking outside
document.addEventListener('click', (event) => {
    if (!event.target.closest('.relative')) {
        showProfileDropdown.value = false;
    }
});

// Helper methods for AdSense components
const getUserTier = () => {
    const user = usePage().props.auth.user
    if (!user?.subscription_plan || user?.subscription_status !== 'active') {
        return 'free'
    }
    return user.subscription_plan.toLowerCase()
}

const getCurrentPageName = () => {
    const component = usePage().component
    if (component.includes('Dashboard')) return 'dashboard'
    if (component.includes('Messages')) return 'messages'
    if (component.includes('Match')) return 'matches'
    if (component.includes('Profile')) return 'profile'
    return 'other'
}
</script> 