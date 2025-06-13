<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const showingMobileMenu = ref(false);

const toggleMobileMenu = () => {
    showingMobileMenu.value = !showingMobileMenu.value;
};

onMounted(() => {
    console.log('AdminLayout mounted');
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 hidden w-64 bg-gray-900 text-white lg:block">
            <div class="flex items-center justify-between border-b border-gray-800 p-4">
                <div class="text-xl font-bold text-amber-500">ZawajAfrica</div>
                <span class="rounded bg-purple-700 px-2 py-1 text-xs">Admin</span>
            </div>
            
            <nav class="mt-5">
                <Link 
                    :href="route('admin.dashboard')" 
                    class="flex items-center px-6 py-3"
                    :class="route().current('admin.dashboard') ? 'bg-purple-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </Link>
                <Link 
                    :href="route('admin.users')" 
                    class="flex items-center px-6 py-3"
                    :class="route().current('admin.users') ? 'bg-purple-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Users
                </Link>
                <Link 
                    :href="route('admin.verifications')" 
                    class="flex items-center px-6 py-3"
                    :class="route().current('admin.verifications') || route().current('admin.verifications.*') ? 'bg-purple-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Verifications
                </Link>
            </nav>
            
            <!-- User Info -->
            <div class="border-t border-gray-800 p-4 mt-auto">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-red-500 hover:text-red-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile menu button -->
        <div class="fixed left-0 top-0 z-50 flex items-center p-4 lg:hidden">
            <button @click="toggleMobileMenu" class="focus:outline-none text-gray-800">
                <svg v-if="!showingMobileMenu" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div v-show="showingMobileMenu" class="fixed inset-0 z-40 bg-black bg-opacity-25" @click="toggleMobileMenu"></div>
        
        <div v-show="showingMobileMenu" class="fixed inset-y-0 left-0 z-50 w-64 transform bg-gray-900 transition-transform duration-300">
            <div class="flex items-center justify-between border-b border-gray-800 p-4">
                <div class="text-xl font-bold text-amber-500">ZawajAfrica</div>
                <button @click="toggleMobileMenu" class="text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Same nav items as desktop sidebar -->
            <nav class="mt-5">
                <Link 
                    :href="route('admin.dashboard')" 
                    class="flex items-center px-6 py-3"
                    :class="route().current('admin.dashboard') ? 'bg-purple-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </Link>
                <Link 
                    :href="route('admin.users')" 
                    class="flex items-center px-6 py-3"
                    :class="route().current('admin.users') ? 'bg-purple-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Users
                </Link>
                <Link 
                    :href="route('admin.verifications')" 
                    class="flex items-center px-6 py-3"
                    :class="route().current('admin.verifications') || route().current('admin.verifications.*') ? 'bg-purple-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Verifications
                </Link>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:ml-64">
            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template> 