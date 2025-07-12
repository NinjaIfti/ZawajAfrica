<script setup>
    import { Head, Link, router } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps({
        therapist: Object,
    });

    const formatDate = dateString => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const formatAvailability = availability => {
        if (!availability || !Array.isArray(availability)) return 'Not set';
        
        const schedule = {};
        availability.forEach(slot => {
            const [day, time] = slot.split('-');
            if (!schedule[day]) schedule[day] = [];
            schedule[day].push(time);
        });
        
        return Object.entries(schedule)
            .map(([day, times]) => `${day}: ${times.join(', ')}`)
            .join('\n');
    };

    const getStatusColor = status => {
        return status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    };

    const deleteTherapist = () => {
        if (confirm(`Are you sure you want to delete ${props.therapist.name}? This action cannot be undone.`)) {
            router.delete(route('admin.therapists.destroy', props.therapist.id), {
                onSuccess: () => {
                    router.visit(route('admin.therapists.index'));
                },
            });
        }
    };
</script>

<template>
    <Head :title="`${therapist.name} - Therapist Details`" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-bold text-gray-900">Therapist Details</h1>
                            <div class="flex space-x-3">
                                <Link :href="route('admin.therapists.index')" class="text-gray-600 hover:text-gray-900">
                                    ← Back to Therapists
                                </Link>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mb-6 flex items-center space-x-3">
                            <Link
                                :href="route('admin.therapists.edit', therapist.id)"
                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                Edit Therapist
                            </Link>
                            <button
                                @click="deleteTherapist"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                Delete Therapist
                            </button>
                        </div>

                        <!-- Therapist Profile -->
                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <div class="flex items-start space-x-6">
                                    <div class="flex-shrink-0">
                                        <div v-if="therapist.photo_url" class="h-32 w-32 rounded-full overflow-hidden">
                                            <img
                                                :src="therapist.photo_url"
                                                :alt="therapist.name"
                                                class="h-32 w-32 object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="h-32 w-32 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 text-2xl font-bold"
                                        >
                                            {{ therapist.name.charAt(0) }}
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4 mb-3">
                                            <h2 class="text-3xl font-bold text-gray-900">{{ therapist.name }}</h2>
                                            <span
                                                class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                                :class="getStatusColor(therapist.status)"
                                            >
                                                {{ therapist.status }}
                                            </span>
                                        </div>
                                        <p class="text-xl text-gray-600 mb-2">{{ therapist.degree }}</p>
                                        <p class="text-gray-600">{{ therapist.years_of_experience }} years of experience</p>
                                        <p v-if="therapist.hourly_rate" class="text-lg font-semibold text-green-600 mt-2">
                                            ₦{{ therapist.hourly_rate }}/hour
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                                    <div class="space-y-3">
                                        <div v-if="therapist.email">
                                            <span class="text-sm font-medium text-gray-500">Email</span>
                                            <p class="text-gray-900">{{ therapist.email }}</p>
                                        </div>
                                        <div v-if="therapist.phone">
                                            <span class="text-sm font-medium text-gray-500">Phone</span>
                                            <p class="text-gray-900">{{ therapist.phone }}</p>
                                        </div>
                                        <div v-if="therapist.languages">
                                            <span class="text-sm font-medium text-gray-500">Languages</span>
                                            <p class="text-gray-900">{{ therapist.languages }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Booking Statistics -->
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Statistics</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-blue-600">{{ therapist.bookings_count || 0 }}</div>
                                            <div class="text-sm text-gray-500">Total Bookings</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-yellow-600">{{ therapist.pending_bookings_count || 0 }}</div>
                                            <div class="text-sm text-gray-500">Pending</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-green-600">{{ therapist.confirmed_bookings_count || 0 }}</div>
                                            <div class="text-sm text-gray-500">Confirmed</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-gray-600">{{ (therapist.bookings_count || 0) - (therapist.pending_bookings_count || 0) - (therapist.confirmed_bookings_count || 0) }}</div>
                                            <div class="text-sm text-gray-500">Completed</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bio -->
                            <div v-if="therapist.bio" class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bio</h3>
                                <p class="text-gray-700 leading-relaxed">{{ therapist.bio }}</p>
                            </div>

                            <!-- Specializations -->
                            <div v-if="therapist.specializations && therapist.specializations.length > 0" class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Specializations</h3>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="spec in therapist.specializations"
                                        :key="spec"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ spec }}
                                    </span>
                                </div>
                            </div>

                            <!-- Availability -->
                            <div v-if="therapist.availability && therapist.availability.length > 0" class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Availability</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="day in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']" :key="day">
                                        <div class="mb-3">
                                            <h4 class="font-medium text-gray-900">{{ day }}</h4>
                                            <div class="mt-1">
                                                <div v-if="therapist.availability.filter(slot => slot.startsWith(day)).length > 0" class="flex flex-wrap gap-1">
                                                    <span
                                                        v-for="slot in therapist.availability.filter(slot => slot.startsWith(day))"
                                                        :key="slot"
                                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800"
                                                    >
                                                        {{ slot.split('-')[1] }}
                                                    </span>
                                                </div>
                                                <span v-else class="text-sm text-gray-500">Not available</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div v-if="therapist.additional_info" class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                                <p class="text-gray-700 leading-relaxed">{{ therapist.additional_info }}</p>
                            </div>

                            <!-- Recent Bookings -->
                            <div v-if="therapist.bookings && therapist.bookings.length > 0" class="bg-white border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Bookings</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    User
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Date & Time
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Status
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Session Type
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="booking in therapist.bookings.slice(0, 10)" :key="booking.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ booking.user?.name || 'N/A' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ booking.user?.email || 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ formatDate(booking.appointment_datetime) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                        :class="{
                                                            'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                                                            'bg-green-100 text-green-800': booking.status === 'confirmed',
                                                            'bg-red-100 text-red-800': booking.status === 'cancelled',
                                                            'bg-blue-100 text-blue-800': booking.status === 'completed'
                                                        }"
                                                    >
                                                        {{ booking.status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ booking.session_type || 'N/A' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="therapist.bookings.length > 10" class="mt-4 text-center">
                                    <Link
                                        :href="route('admin.therapists.bookings')"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                    >
                                        View All Bookings
                                    </Link>
                                </div>
                            </div>

                            <!-- System Information -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Created:</span>
                                        <span class="ml-2 text-gray-900">{{ formatDate(therapist.created_at) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Last Updated:</span>
                                        <span class="ml-2 text-gray-900">{{ formatDate(therapist.updated_at) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Therapist ID:</span>
                                        <span class="ml-2 text-gray-900">#{{ therapist.id }}</span>
                                    </div>
                                    <div v-if="therapist.zoho_service_id">
                                        <span class="text-gray-500">Zoho Service ID:</span>
                                        <span class="ml-2 text-gray-900">{{ therapist.zoho_service_id }}</span>
                                    </div>
                                </div>
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