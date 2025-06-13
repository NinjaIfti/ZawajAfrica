<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';

const props = defineProps({
    users: Object,
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
    <Head title="Users Management" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Users Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-medium">All Users</h3>
                            <div class="text-sm text-gray-500">
                                Total: {{ props.users.total }}
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Joined
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Verification
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="user in props.users.data" :key="user.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-700">
                                                    {{ user.name ? user.name.charAt(0) : '?' }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                                                    <div v-if="user.gender" class="text-sm text-gray-500">{{ user.gender }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ user.email }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span 
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': user.is_verified,
                                                    'bg-yellow-100 text-yellow-800': !user.is_verified && user.verification && user.verification.status === 'pending',
                                                    'bg-red-100 text-red-800': !user.is_verified && user.verification && user.verification.status === 'rejected',
                                                    'bg-gray-100 text-gray-800': !user.is_verified && !user.verification
                                                }"
                                            >
                                                <template v-if="user.is_verified">Verified</template>
                                                <template v-else-if="user.verification && user.verification.status === 'pending'">Pending</template>
                                                <template v-else-if="user.verification && user.verification.status === 'rejected'">Rejected</template>
                                                <template v-else>Not Verified</template>
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <div class="flex space-x-2">
                                                <Link 
                                                    v-if="user.verification && user.verification.status === 'pending'"
                                                    :href="route('admin.verifications.view', { userId: user.id })"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Review
                                                </Link>
                                                <Link 
                                                    v-else-if="user.verification"
                                                    :href="route('admin.verifications.view', { userId: user.id })"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View Verification
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="props.users.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            No users found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="props.users.data.length > 0" class="mt-4 flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <Link
                                    v-if="props.users.prev_page_url"
                                    :href="props.users.prev_page_url"
                                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="props.users.next_page_url"
                                    :href="props.users.next_page_url"
                                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{ props.users.from }}</span>
                                        to
                                        <span class="font-medium">{{ props.users.to }}</span>
                                        of
                                        <span class="font-medium">{{ props.users.total }}</span>
                                        results
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                        <Link
                                            v-if="props.users.prev_page_url"
                                            :href="props.users.prev_page_url"
                                            class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20"
                                        >
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                            </svg>
                                        </Link>
                                        <Link
                                            v-if="props.users.next_page_url"
                                            :href="props.users.next_page_url"
                                            class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20"
                                        >
                                            <span class="sr-only">Next</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                            </svg>
                                        </Link>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 