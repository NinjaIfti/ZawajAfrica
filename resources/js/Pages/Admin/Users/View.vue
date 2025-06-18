<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const calculateAge = (user) => {
    if (!user.dob_day || !user.dob_month || !user.dob_year) {
        return 'N/A';
    }
    
    const monthMap = {
        'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 
        'May': 4, 'Jun': 5, 'Jul': 6, 'Aug': 7, 
        'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
    };
    
    const month = monthMap[user.dob_month];
    const birthDate = new Date(user.dob_year, month, user.dob_day);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        return age - 1;
    }
    
    return age;
};
</script>

<template>
    <Head :title="`User Profile - ${user.name}`" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}'s Profile</h1>
                    <Link :href="route('admin.users')" class="text-indigo-600 hover:text-indigo-900">
                        ← Back to Users
                    </Link>
                </div>

                <!-- Basic Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Gender</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.gender || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Interested In</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.interested_in || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Age</label>
                                <p class="mt-1 text-sm text-gray-900">{{ calculateAge(user) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Location</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ [user.city, user.state, user.country].filter(Boolean).join(', ') || 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Joined</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(user.created_at) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Verification Status</label>
                                <span class="mt-1 inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                      :class="{
                                          'bg-green-100 text-green-800': user.is_verified,
                                          'bg-yellow-100 text-yellow-800': !user.is_verified && user.verification?.status === 'pending',
                                          'bg-red-100 text-red-800': !user.is_verified && user.verification?.status === 'rejected',
                                          'bg-gray-100 text-gray-800': !user.is_verified && !user.verification
                                      }">
                                    <template v-if="user.is_verified">Verified</template>
                                    <template v-else-if="user.verification?.status === 'pending'">Pending</template>
                                    <template v-else-if="user.verification?.status === 'rejected'">Rejected</template>
                                    <template v-else>Not Verified</template>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" v-if="user.photos && user.photos.length > 0">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Photos ({{ user.photos.length }})</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div v-for="photo in user.photos" :key="photo.id" class="relative">
                                <img 
                                    :src="photo.url" 
                                    :alt="`Photo ${photo.id}`"
                                    class="w-full h-48 object-cover rounded-lg shadow-sm"
                                />
                                <div v-if="photo.is_primary" class="absolute top-2 left-2">
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Primary</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" v-if="user.about">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">About</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ user.about.bio || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Looking For</label>
                                <p class="text-sm text-gray-900">{{ user.about.looking_for || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" v-if="user.appearance">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Appearance</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Height</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.appearance.height || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Body Type</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.appearance.body_type || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Eye Color</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.appearance.eye_color || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Data Message -->
                <div v-if="!user.photos?.length && !user.about && !user.appearance" 
                     class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-gray-500">This user hasn't completed their profile yet.</p>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template> 