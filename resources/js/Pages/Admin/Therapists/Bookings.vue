<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    bookings: Object,
});

const showModal = ref(false);
const selectedBooking = ref(null);

const form = useForm({
    status: '',
    admin_notes: '',
    meeting_link: '',
    cancellation_reason: '',
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'confirmed': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        case 'completed': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const openModal = (booking) => {
    selectedBooking.value = booking;
    form.status = booking.status;
    form.admin_notes = booking.admin_notes || '';
    form.meeting_link = booking.meeting_link || '';
    form.cancellation_reason = booking.cancellation_reason || '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedBooking.value = null;
    form.reset();
};

const updateBooking = () => {
    form.put(route('admin.therapists.bookings.update', selectedBooking.value.id), {
        onSuccess: () => {
            closeModal();
        }
    });
};
</script>

<template>
    <Head title="Therapist Bookings" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-bold text-gray-900">Therapist Bookings</h1>
                            <Link :href="route('admin.therapists.index')" class="text-gray-600 hover:text-gray-900">
                                ← Back to Therapists
                            </Link>
                        </div>

                        <!-- Bookings Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            User
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Therapist
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Appointment
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Session Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="booking in bookings.data" :key="booking.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900">{{ booking.user?.name || 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">{{ booking.user?.email || 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ booking.therapist?.name || 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ booking.therapist?.degree || 'N/A' }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ formatDate(booking.appointment_datetime) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                  :class="getStatusColor(booking.status)">
                                                {{ booking.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ booking.session_type }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                            <button @click="openModal(booking)" 
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                Manage
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="bookings.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No bookings found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="bookings.data.length > 0" class="mt-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ bookings.from || 0 }} to {{ bookings.to || 0 }} of {{ bookings.total }} results
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <Link v-if="bookings.prev_page_url" :href="bookings.prev_page_url"
                                      class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                    Previous
                                </Link>
                                <Link v-if="bookings.next_page_url" :href="bookings.next_page_url"
                                      class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                    Next
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeModal"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="updateBooking">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                        Manage Booking
                                    </h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                            <select v-model="form.status" id="status" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="pending">Pending</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="completed">Completed</option>
                                            </select>
                                        </div>

                                        <div v-if="form.status === 'confirmed'">
                                            <label for="meeting_link" class="block text-sm font-medium text-gray-700">Meeting Link</label>
                                            <input v-model="form.meeting_link" type="url" id="meeting_link"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>

                                        <div v-if="form.status === 'cancelled'">
                                            <label for="cancellation_reason" class="block text-sm font-medium text-gray-700">Cancellation Reason</label>
                                            <textarea v-model="form.cancellation_reason" id="cancellation_reason" rows="3"
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        </div>

                                        <div>
                                            <label for="admin_notes" class="block text-sm font-medium text-gray-700">Admin Notes</label>
                                            <textarea v-model="form.admin_notes" id="admin_notes" rows="3"
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="form.processing"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Update Booking</span>
                            </button>
                            <button type="button" @click="closeModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 