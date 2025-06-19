<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            totalUsers: 0,
            pendingVerifications: 0,
            pendingReports: 0
        })
    },
    recentUsers: {
        type: Array,
        required: true,
        default: () => []
    }
});

const showPremiumModal = ref(false);
const premiumUsers = ref([]);
const premiumUsersTotal = ref(0);
const premiumLoading = ref(false);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const loadPremiumUsers = async () => {
    if (premiumUsers.value.length > 0) return; // Already loaded
    
    premiumLoading.value = true;
    try {
        const response = await fetch(route('admin.premium.users'));
        const data = await response.json();
        premiumUsers.value = data.users;
        premiumUsersTotal.value = data.total;
    } catch (error) {
        console.error('Error loading premium users:', error);
    } finally {
        premiumLoading.value = false;
    }
};

const openPremiumModal = async () => {
    showPremiumModal.value = true;
    await loadPremiumUsers();
};

const getPlanDisplayName = (plan) => {
    switch(plan) {
        case 'basic_monthly':
            return 'Basic Monthly';
        case 'basic_yearly':
            return 'Basic Yearly';
        case 'premium_monthly':
            return 'Premium Monthly';
        case 'premium_yearly':
            return 'Premium Yearly';
        default:
            return plan || 'Unknown';
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-4">
                    <!-- Total Users Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-purple-100 p-3 text-purple-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Users</p>
                                <p class="text-2xl font-semibold">{{ props.stats.totalUsers }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Verifications Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <Link :href="route('admin.verifications')" class="flex items-center">
                            <div class="mr-4 rounded-full bg-amber-100 p-3 text-amber-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Pending Verifications</p>
                                <p class="text-2xl font-semibold">{{ props.stats.pendingVerifications }}</p>
                            </div>
                        </Link>
                    </div>
                    
                    <!-- User Reports Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <Link :href="route('admin.reports.index')" class="flex items-center">
                            <div class="mr-4 rounded-full bg-red-100 p-3 text-red-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Pending Reports</p>
                                <p class="text-2xl font-semibold">{{ props.stats.pendingReports }}</p>
                            </div>
                        </Link>
                    </div>
                    
                    <!-- Premium Users Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md cursor-pointer" @click="openPremiumModal">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-green-100 p-3 text-green-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Premium Users</p>
                                <p class="text-2xl font-semibold">{{ props.stats.premiumUsers }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow-md">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Recent Users</h3>
                            <Link :href="route('admin.users')" class="text-sm text-purple-600 hover:text-purple-800">
                                View All
                            </Link>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Email</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Joined</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="user in props.recentUsers" :key="user.id">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-700">
                                                {{ user.name ? user.name.charAt(0) : '?' }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ formatDate(user.created_at) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5" :class="user.is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                            {{ user.is_verified ? 'Verified' : 'Not Verified' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!props.recentUsers || props.recentUsers.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        No users found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Premium Users Modal -->
        <Modal :show="showPremiumModal" @close="showPremiumModal = false" max-width="4xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Premium Users</h3>
                    <button @click="showPremiumModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="premiumLoading" class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                </div>

                <div v-else>
                    <div class="mb-4 text-sm text-gray-600">
                        Showing top 10 premium users ({{ premiumUsersTotal }} total)
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500">User</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500">Plan</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500">Expires</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500">Verified</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="user in premiumUsers" :key="user.id">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-700">
                                                {{ user.name ? user.name.charAt(0) : '?' }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                                <div class="text-xs text-gray-500">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ getPlanDisplayName(user.subscription_plan) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="text-sm text-gray-500">
                                            {{ user.subscription_expires_at ? formatDate(user.subscription_expires_at) : 'Never' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5" :class="user.is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                            {{ user.is_verified ? 'Verified' : 'Not Verified' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!premiumUsers || premiumUsers.length === 0">
                                    <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                                        No premium users found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            {{ premiumUsers.length }} of {{ premiumUsersTotal }} premium users shown
                        </div>
                        <Link :href="route('admin.subscriptions')" @click="showPremiumModal = false" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            View All Subscriptions
                        </Link>
                    </div>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template> 