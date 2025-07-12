<script setup>
    import { Head, useForm, Link } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { ref, onMounted } from 'vue';

    const props = defineProps({
        therapist: Object,
        specializations: Array,
    });

    const form = useForm({
        name: props.therapist.name || '',
        bio: props.therapist.bio || '',
        photo: null,
        specializations: props.therapist.specializations || [],
        degree: props.therapist.degree || '',
        years_of_experience: props.therapist.years_of_experience || 0,
        hourly_rate: props.therapist.hourly_rate || '',
        availability: props.therapist.availability || [],
        phone: props.therapist.phone || '',
        email: props.therapist.email || '',
        languages: props.therapist.languages || '',
        status: props.therapist.status || 'active',
        additional_info: props.therapist.additional_info || '',
    });

    const photoPreview = ref(null);
    const currentPhoto = ref(props.therapist.photo_url || null);

    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const timeSlots = [
        '09:00-10:00',
        '10:00-11:00',
        '11:00-12:00',
        '12:00-13:00',
        '13:00-14:00',
        '14:00-15:00',
        '15:00-16:00',
        '16:00-17:00',
        '17:00-18:00',
        '18:00-19:00',
        '19:00-20:00',
    ];

    const handlePhotoChange = event => {
        const file = event.target.files[0];
        if (file) {
            form.photo = file;
            const reader = new FileReader();
            reader.onload = e => {
                photoPreview.value = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };

    const toggleSpecialization = spec => {
        const index = form.specializations.indexOf(spec);
        if (index > -1) {
            form.specializations.splice(index, 1);
        } else {
            form.specializations.push(spec);
        }
    };

    const toggleAvailability = (day, time) => {
        const slot = `${day}-${time}`;
        const index = form.availability.indexOf(slot);
        if (index > -1) {
            form.availability.splice(index, 1);
        } else {
            form.availability.push(slot);
        }
    };

    const isAvailable = (day, time) => {
        return form.availability.includes(`${day}-${time}`);
    };

    const submit = () => {
        form.put(route('admin.therapists.update', props.therapist.id));
    };
</script>

<template>
    <Head title="Edit Therapist" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-bold text-gray-900">Edit Therapist</h1>
                            <Link :href="route('admin.therapists.index')" class="text-gray-600 hover:text-gray-900">
                                ← Back to Therapists
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            id="name"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            id="email"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.email }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            id="phone"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.phone }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">
                                            Status *
                                        </label>
                                        <select
                                            v-model="form.status"
                                            id="status"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                                    <textarea
                                        v-model="form.bio"
                                        id="bio"
                                        rows="4"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                    <div v-if="form.errors.bio" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.bio }}
                                    </div>
                                </div>

                                <div>
                                    <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                                    <div v-if="currentPhoto && !photoPreview" class="mt-2 mb-2">
                                        <img
                                            :src="currentPhoto"
                                            alt="Current photo"
                                            class="h-24 w-24 rounded-full object-cover"
                                        />
                                        <p class="text-sm text-gray-500 mt-1">Current photo</p>
                                    </div>
                                    <input
                                        type="file"
                                        id="photo"
                                        @change="handlePhotoChange"
                                        accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    />
                                    <div v-if="photoPreview" class="mt-2">
                                        <img
                                            :src="photoPreview"
                                            alt="Preview"
                                            class="h-24 w-24 rounded-full object-cover"
                                        />
                                        <p class="text-sm text-gray-500 mt-1">New photo preview</p>
                                    </div>
                                    <div v-if="form.errors.photo" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.photo }}
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information -->
                            <div class="space-y-4 border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900">Professional Information</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="degree" class="block text-sm font-medium text-gray-700">
                                            Degree *
                                        </label>
                                        <input
                                            v-model="form.degree"
                                            type="text"
                                            id="degree"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.degree" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.degree }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="years_of_experience"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Years of Experience *
                                        </label>
                                        <input
                                            v-model="form.years_of_experience"
                                            type="number"
                                            id="years_of_experience"
                                            min="0"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.years_of_experience" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.years_of_experience }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="hourly_rate" class="block text-sm font-medium text-gray-700">
                                            Hourly Rate (₦)
                                        </label>
                                        <input
                                            v-model="form.hourly_rate"
                                            type="number"
                                            id="hourly_rate"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.hourly_rate" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.hourly_rate }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="languages" class="block text-sm font-medium text-gray-700">
                                            Languages
                                        </label>
                                        <input
                                            v-model="form.languages"
                                            type="text"
                                            id="languages"
                                            placeholder="e.g., English, Hausa, French"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.languages" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.languages }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label for="additional_info" class="block text-sm font-medium text-gray-700">
                                        Additional Information
                                    </label>
                                    <textarea
                                        v-model="form.additional_info"
                                        id="additional_info"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                    <div v-if="form.errors.additional_info" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.additional_info }}
                                    </div>
                                </div>
                            </div>

                            <!-- Specializations -->
                            <div class="space-y-4 border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900">Specializations *</h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <div v-for="spec in specializations" :key="spec" class="relative">
                                        <label class="flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :checked="form.specializations.includes(spec)"
                                                @change="toggleSpecialization(spec)"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                            <span class="ml-2 text-sm text-gray-700">{{ spec }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div v-if="form.errors.specializations" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.specializations }}
                                </div>
                            </div>

                            <!-- Availability -->
                            <div class="space-y-4 border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900">Availability *</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr>
                                                <th class="text-left text-sm font-medium text-gray-700 pb-2">Time</th>
                                                <th
                                                    v-for="day in days"
                                                    :key="day"
                                                    class="text-center text-sm font-medium text-gray-700 pb-2 px-2"
                                                >
                                                    {{ day.substr(0, 3) }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="time in timeSlots" :key="time">
                                                <td class="text-sm text-gray-600 py-1 pr-4">{{ time }}</td>
                                                <td
                                                    v-for="day in days"
                                                    :key="`${day}-${time}`"
                                                    class="text-center py-1 px-2"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :checked="isAvailable(day, time)"
                                                        @change="toggleAvailability(day, time)"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="form.errors.availability" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.availability }}
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end space-x-3 border-t pt-6">
                                <Link
                                    :href="route('admin.therapists.index')"
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-300"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    <span v-if="form.processing">Updating...</span>
                                    <span v-else>Update Therapist</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
    /* Add any additional styles if needed */
</style> 