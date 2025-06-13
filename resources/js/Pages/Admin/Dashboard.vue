<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            totalUsers: 0,
            pendingVerifications: 0
        })
    },
    recentUsers: {
        type: Array,
        required: true,
        default: () => []
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
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
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
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

                    <!-- Premium Users Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-green-100 p-3 text-green-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Premium Users</p>
                                <p class="text-2xl font-semibold">0</p>
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
    </AdminLayout>
</template> 