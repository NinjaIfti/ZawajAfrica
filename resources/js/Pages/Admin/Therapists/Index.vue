<script setup>
    import { Head, Link, router } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps({
        therapists: Object,
    });

    const formatDate = dateString => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    const deleteTherapist = therapistId => {
        const therapist = props.therapists.data.find(t => t.id === therapistId);
        if (
            confirm(
                `Are you sure you want to delete ${therapist?.name || 'this therapist'}? This will permanently delete the therapist and cannot be undone.`
            )
        ) {
            router.delete(route('admin.therapists.destroy', { therapist: therapistId }), {
                preserveScroll: true,
                onSuccess: () => {
                    // The page will be refreshed with the success message
                },
                onError: errors => {
                    alert('Failed to delete therapist: ' + (errors.message || 'Unknown error'));
                },
            });
        }
    };
</script>

<template>
    <Head title="Therapists Management" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-bold text-gray-900">Therapists Management</h1>
                            <Link
                                :href="route('admin.therapists.create')"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                Add New Therapist
                            </Link>
                        </div>

                        <!-- Therapists Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Therapist
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Specializations
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Experience
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Status
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Bookings
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="therapist in therapists.data" :key="therapist.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-12 w-12 flex-shrink-0">
                                                    <img
                                                        v-if="therapist.photo && therapist.photo_url"
                                                        class="h-12 w-12 rounded-full object-cover"
                                                        :src="therapist.photo_url"
                                                        :alt="therapist.name"
                                                        @error="$event.target.style.display = 'none'"
                                                    />
                                                    <div
                                                        v-else
                                                        class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-300 text-gray-700 font-semibold"
                                                    >
                                                        {{ therapist.name.charAt(0) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900">{{ therapist.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ therapist.degree }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="spec in therapist.specializations.slice(0, 2)"
                                                    :key="spec"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    {{ spec }}
                                                </span>
                                                <span
                                                    v-if="therapist.specializations.length > 2"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                                >
                                                    +{{ therapist.specializations.length - 2 }} more
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ therapist.years_of_experience }} years
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                :class="{
                                                    'bg-green-100 text-green-800': therapist.status === 'active',
                                                    'bg-red-100 text-red-800': therapist.status === 'inactive',
                                                }"
                                            >
                                                {{ therapist.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            <div class="text-sm">
                                                <div>Total: {{ therapist.bookings_count || 0 }}</div>
                                                <div class="text-xs text-gray-500">
                                                    Pending: {{ therapist.pending_bookings_count || 0 }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link
                                                    :href="route('admin.therapists.show', therapist.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="route('admin.therapists.edit', therapist.id)"
                                                    class="text-yellow-600 hover:text-yellow-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="deleteTherapist(therapist.id)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="therapists.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No therapists found.
                                            <Link
                                                :href="route('admin.therapists.create')"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Add the first therapist
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="therapists.data.length > 0" class="mt-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ therapists.from || 0 }} to {{ therapists.to || 0 }} of
                                    {{ therapists.total }} results
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    v-if="therapists.prev_page_url"
                                    :href="therapists.prev_page_url"
                                    class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="therapists.next_page_url"
                                    :href="therapists.next_page_url"
                                    class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                                >
                                    Next
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
    /* Add any additional styles if needed */
</style>
