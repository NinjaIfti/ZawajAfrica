<script setup>
    import { ref, onMounted, onUnmounted } from 'vue';
    import { Link } from '@inertiajs/vue3';

    defineProps({
        title: String,
    });

    const showingMobileMenu = ref(false);

    const toggleMobileMenu = () => {
        showingMobileMenu.value = !showingMobileMenu.value;
        
        // Prevent body scroll when mobile menu is open
        if (showingMobileMenu.value) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }
    };

    // Close mobile menu when clicking outside or on links
    const closeMobileMenu = () => {
        showingMobileMenu.value = false;
        document.body.style.overflow = 'unset';
    };

    // Handle escape key to close mobile menu
    const handleEscape = (e) => {
        if (e.key === 'Escape' && showingMobileMenu.value) {
            closeMobileMenu();
        }
    };

    onMounted(() => {
        document.addEventListener('keydown', handleEscape);
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', handleEscape);
        document.body.style.overflow = 'unset';
    });
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Admin Sidebar - Desktop -->
        <div
            class="hidden lg:flex lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:w-64 lg:flex-col bg-gray-900 text-white"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 flex-shrink-0">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-amber-500">ZawajAfrica</span>
                </div>
                <span class="px-2 py-1 text-xs font-semibold bg-purple-800 text-white rounded">Admin</span>
            </div>

            <!-- Nav Items - Scrollable -->
            <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin scrollbar-track-gray-800 scrollbar-thumb-gray-600 hover:scrollbar-thumb-gray-500">
                <div class="px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main</div>
                <Link
                    :href="route('admin.dashboard')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.dashboard') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        ></path>
                    </svg>
                    <span>Dashboard</span>
                </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    User Management
                </div>
                <Link
                    :href="route('admin.users')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.users') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                        ></path>
                    </svg>
                    <span>Users</span>
                </Link>

                <Link
                    :href="route('admin.verifications')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.verifications') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                        ></path>
                    </svg>
                    <span>Verifications</span>
                </Link>

                <Link
                    :href="route('admin.reports.index')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.reports.*') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2z"
                        ></path>
                    </svg>
                    <span>User Reports</span>
                </Link>

                <Link
                    :href="route('admin.therapists.index')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.therapists.*') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        ></path>
                    </svg>
                    <span>Therapists</span>
                </Link>

                <Link
                    :href="route('admin.therapists.bookings')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.therapists.bookings') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        ></path>
                    </svg>
                    <span>Therapist Bookings</span>
                </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Financial Management
                </div>
                <Link
                    :href="route('admin.subscriptions')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.subscriptions') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span>Subscriptions</span>
                </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Content Management
                </div>
                <a href="#" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                        ></path>
                    </svg>
                    <span>Announcements</span>
                </a>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    AI Tools
                </div>
                <Link
                    :href="route('admin.ai.insights')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.ai.insights') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                        ></path>
                    </svg>
                    <span>AI Broadcast</span>
                </Link>

                <Link
                    :href="route('admin.user-insights')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.user-insights') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                        ></path>
                    </svg>
                    <span>AI Admin Assistant</span>
                </Link>
                <Link
                    :href="route('admin.gpt.integration')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.gpt.integration') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        ></path>
                    </svg>
                    <span>GPT Integration</span>
                </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</div>
                <Link
                    :href="route('admin.settings')"
                    class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.settings') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>
                    <span>Admin Settings</span>
                </Link>
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t border-gray-800 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-red-500 hover:text-red-400 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            ></path>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile menu button -->
        <div class="fixed top-0 left-0 z-50 flex items-center p-4 lg:hidden">
            <button 
                @click="toggleMobileMenu" 
                class="bg-gray-900 text-white p-2 rounded-lg shadow-lg hover:bg-gray-800 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500"
                :class="{ 'bg-gray-800': showingMobileMenu }"
            >
                <svg v-if="!showingMobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    ></path>
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    ></path>
                </svg>
            </button>
        </div>

        <!-- Mobile menu backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
        <div
            v-show="showingMobileMenu"
                class="fixed inset-0 z-40 bg-black bg-opacity-50 backdrop-blur-sm lg:hidden"
                @click="closeMobileMenu"
        ></div>
        </Transition>

        <!-- Mobile menu sidebar -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
        <div
            v-show="showingMobileMenu"
                class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 flex flex-col lg:hidden"
        >
            <!-- Logo -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 flex-shrink-0">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-amber-500">ZawajAfrica</span>
                </div>
                    <button 
                        @click="closeMobileMenu" 
                        class="text-gray-300 hover:text-white p-1 rounded transition-colors duration-200"
                    >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        ></path>
                    </svg>
                </button>
            </div>

                <!-- Nav Items - Mobile Scrollable -->
                <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin scrollbar-track-gray-800 scrollbar-thumb-gray-600">
                <div class="px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main</div>
                <Link
                    :href="route('admin.dashboard')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.dashboard') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        ></path>
                    </svg>
                    <span>Dashboard</span>
                </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    User Management
                </div>
                <Link
                    :href="route('admin.users')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.users') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                        ></path>
                    </svg>
                    <span>Users</span>
                </Link>

                <Link
                    :href="route('admin.verifications')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.verifications') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                        ></path>
                    </svg>
                    <span>Verifications</span>
                </Link>

                <Link
                    :href="route('admin.reports.index')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.reports.*') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2z"
                        ></path>
                    </svg>
                    <span>User Reports</span>
                </Link>

                <Link
                    :href="route('admin.therapists.index')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.therapists.*') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        ></path>
                    </svg>
                    <span>Therapists</span>
                </Link>

                <Link
                    :href="route('admin.therapists.bookings')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.therapists.bookings') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        ></path>
                    </svg>
                    <span>Therapist Bookings</span>
                </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Financial Management
                </div>
                <Link
                    :href="route('admin.subscriptions')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                    :class="{ 'bg-gray-800 text-white': route().current('admin.subscriptions') }"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span>Subscriptions</span>
                </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Content Management
                </div>
                    <a href="#" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                        ></path>
                    </svg>
                    <span>Announcements</span>
                </a>

                    <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        AI Tools
                    </div>
                    <Link
                        :href="route('admin.ai.insights')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                        :class="{ 'bg-gray-800 text-white': route().current('admin.ai.*') }"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                            ></path>
                        </svg>
                        <span>AI Insights & Tools</span>
                    </Link>

                <div class="mt-6 px-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</div>
                    <Link
                        :href="route('admin.settings')"
                        @click="closeMobileMenu"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors duration-200"
                        :class="{ 'bg-gray-800 text-white': route().current('admin.settings') }"
                    >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>
                        <span>Admin Settings</span>
                    </Link>
            </nav>

            <!-- User Info - Mobile -->
                <div class="p-4 border-t border-gray-800 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</p>
                    </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-red-500 hover:text-red-400 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            ></path>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
        </Transition>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top navigation -->
            <div class="bg-white shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 lg:px-8">
                    <h1 class="text-xl font-semibold text-gray-800 ml-16 lg:ml-0">{{ title }}</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ $page.props.auth.user.name }}</span>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="$page.props.flash.success"
                class="mx-6 mt-4 mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                role="alert"
            >
                <span class="block sm:inline">{{ $page.props.flash.success }}</span>
            </div>
            <div
                v-if="$page.props.flash.error"
                class="mx-6 mt-4 mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                role="alert"
            >
                <span class="block sm:inline">{{ $page.props.flash.error }}</span>
            </div>

            <!-- Page content -->
            <main class="p-6">
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar styles */
.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-track-gray-800 {
    scrollbar-color: rgb(75 85 99) rgb(31 41 55);
}

.scrollbar-thumb-gray-600:hover {
    scrollbar-color: rgb(75 85 99) rgb(31 41 55);
}

/* For webkit browsers */
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-track-gray-800::-webkit-scrollbar-track {
    background: rgb(31 41 55);
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb {
    background: rgb(75 85 99);
    border-radius: 3px;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb:hover {
    background: rgb(107 114 128);
}
</style>
