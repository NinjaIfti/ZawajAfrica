<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    subscriptions: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusColor = (subscription) => {
    if (subscription.subscription_status === 'cancelled') {
        return 'bg-red-100 text-red-800';
    }
    
    if (subscription.subscription_expires_at && new Date(subscription.subscription_expires_at) <= new Date()) {
        return 'bg-yellow-100 text-yellow-800';
    }
    
    if (subscription.subscription_status === 'active') {
        return 'bg-green-100 text-green-800';
    }
    
    return 'bg-gray-100 text-gray-800';
};

const getStatusText = (subscription) => {
    if (subscription.subscription_status === 'cancelled') {
        return 'Cancelled';
    }
    
    if (subscription.subscription_expires_at && new Date(subscription.subscription_expires_at) <= new Date()) {
        return 'Expired';
    }
    
    if (subscription.subscription_status === 'active') {
        return 'Active';
    }
    
    return 'Unknown';
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
    <Head title="Subscription Management" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Subscription Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-4">
                    <!-- Total Subscriptions Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-blue-100 p-3 text-blue-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Subscriptions</p>
                                <p class="text-2xl font-semibold">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Active Subscriptions Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-green-100 p-3 text-green-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Active</p>
                                <p class="text-2xl font-semibold">{{ stats.active }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expired Subscriptions Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-yellow-100 p-3 text-yellow-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Expired</p>
                                <p class="text-2xl font-semibold">{{ stats.expired }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cancelled Subscriptions Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-red-100 p-3 text-red-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Cancelled</p>
                                <p class="text-2xl font-semibold">{{ stats.cancelled }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow-md">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">User Subscriptions</h3>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">User</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Plan</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Expires At</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Verified</th>
                                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="subscription in subscriptions.data" :key="subscription.id">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-700">
                                                {{ subscription.name ? subscription.name.charAt(0) : '?' }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ subscription.name }}</div>
                                                <div class="text-sm text-gray-500">{{ subscription.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ getPlanDisplayName(subscription.subscription_plan) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5" :class="getStatusColor(subscription)">
                                            {{ getStatusText(subscription) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500">
                                            {{ subscription.subscription_expires_at ? formatDate(subscription.subscription_expires_at) : 'Never' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5" :class="subscription.is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                            {{ subscription.is_verified ? 'Verified' : 'Not Verified' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        <Link :href="route('admin.users.view', subscription.id)" class="text-purple-600 hover:text-purple-800">
                                            View Profile
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!subscriptions.data || subscriptions.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No subscriptions found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="subscriptions.links && subscriptions.links.length > 3" class="border-t border-gray-200 px-6 py-3">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ subscriptions.from }} to {{ subscriptions.to }} of {{ subscriptions.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <Link
                                    v-for="link in subscriptions.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm border rounded',
                                        link.active 
                                            ? 'bg-purple-600 text-white border-purple-600' 
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 