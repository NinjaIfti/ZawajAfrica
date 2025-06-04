<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    user: Object
});
</script>

<template>
    <!-- Left Sidebar -->
    <div class="w-64 bg-white shadow-md">
        <!-- Logo -->
        <div class="p-4 mb-6">
            <div>
                <img src="/images/dash.png" alt="ZawajAfrica" class="h-10">
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="space-y-2 px-3">
            <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('dashboard') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('dashboard') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span>Matches</span>
            </Link>
            
            <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Therapists</span>
            </Link>
            
            <Link :href="route('messages')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('messages') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('messages') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span>Messages</span>
            </Link>
            
            <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span>AI Chatbot</span>
            </Link>
            
            <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>Notifications</span>
            </Link>
            
            <Link :href="route('profile.edit')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium" :class="route().current('profile.*') ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-50'">
                <svg class="mr-3 h-6 w-6" :class="route().current('profile.*') ? 'text-white' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>My Profile</span>
            </Link>
            
            <Link :href="route('dashboard')" class="flex items-center rounded-lg px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50">
                <svg class="mr-3 h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Subscription Plans</span>
            </Link>
        </nav>
        
        <!-- Upgrade Membership -->
        <div class="mt-auto p-4 fixed bottom-0 w-64">
            <div class="rounded-lg bg-purple-700 p-4 text-center text-white">
                <div class="mb-2 flex justify-center">
                    <svg class="h-8 w-8 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold">Upgrade Membership</h3>
                <p class="mt-1 text-xs">Your current membership is standard.</p>
                <button class="mt-4 inline-flex items-center rounded-lg bg-purple-800 px-4 py-2 text-sm font-medium text-white hover:bg-purple-900">
                    Upgrade Now
                    <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template> 