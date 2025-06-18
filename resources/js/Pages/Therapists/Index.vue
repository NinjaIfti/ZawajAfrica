<template>
    <Head title="Therapists" />
    
    <div class="flex h-screen bg-gray-50">
        <!-- Left Sidebar -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header with AppHeader component -->
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                <AppHeader :user="$page.props.auth.user">
                    <template #title>
                        <h1 class="text-2xl font-bold text-gray-900">Therapists</h1>
                    </template>
                </AppHeader>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Search Bar -->
                <div class="mb-6 flex justify-center">
                    <div class="w-full max-w-md relative">
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            placeholder="Search therapists by name or specialization..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Therapist Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="therapist in filteredTherapists" :key="therapist.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
                        <!-- Profile Picture -->
                        <div class="flex justify-center mb-4">
                            <div class="relative">
                                <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 border-2 border-orange-300">
                                    <img 
                                        v-if="therapist.photo_url" 
                                        :src="therapist.photo_url" 
                                        :alt="therapist.name"
                                        class="w-full h-full object-cover object-center"
                                    />
                                    <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Online status indicator -->
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white"></div>
                            </div>
                        </div>

                        <!-- Name and Specialization -->
                        <div class="text-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ therapist.name }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ getMainSpecialization(therapist.specializations) }}
                            </p>
                        </div>

                        <!-- Stats Row -->
                        <div class="flex justify-between items-center mb-4 text-sm">
                            <div class="text-center">
                                <span class="block font-semibold text-gray-900">{{ therapist.years_of_experience }} years</span>
                                <span class="text-gray-500 text-xs">Experience</span>
                            </div>
                            <div class="text-center">
                                <span class="block font-semibold text-gray-900">{{ getSessionDuration(therapist) }}</span>
                                <span class="text-gray-500 text-xs">Session</span>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-1">
                                    <span class="font-semibold text-gray-900 mr-1">{{ getRating(therapist).toFixed(1) }}</span>
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                                <span class="text-gray-500 text-xs">Reviews</span>
                            </div>
                        </div>

                        <!-- Consultation Fee -->
                        <div class="text-center mb-4 bg-gray-100 rounded-lg p-3">
                            <span class="text-sm text-gray-600">Online Consultation Fee:</span>
                            <p class="text-xl font-bold text-gray-900">${{ Number(therapist.hourly_rate).toLocaleString() }}</p>
                        </div>

                        <!-- Book Appointment Button -->
                        <button 
                            @click="bookAppointment(therapist)"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-4 rounded-xl transition-colors duration-200"
                        >
                            Book Appointment
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredTherapists.length === 0" class="text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No therapists found</h3>
                    <p class="text-gray-500">Try adjusting your search criteria.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'
import AppHeader from '@/Components/AppHeader.vue'

const props = defineProps({
    therapists: Array,
    filters: Object,
})

const searchQuery = ref(props.filters?.search || '')

// Use props.therapists directly since backend now handles filtering
const filteredTherapists = computed(() => {
    return props.therapists
})

// Debounced search function
let searchTimeout = null
watch(searchQuery, (newValue) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('therapists.index'), { 
            search: newValue 
        }, { 
            preserveState: true,
            preserveScroll: true,
            replace: true
        })
    }, 300) // 300ms debounce
})

const getMainSpecialization = (specializations) => {
    if (!specializations || specializations.length === 0) return 'General Therapist'
    
    if (Array.isArray(specializations)) {
        // Show only the first specialization for cleaner design
        return specializations[0] || 'General Therapist'
    }
    
    return specializations
}

const getSessionDuration = (therapist) => {
    return therapist.session_duration || '90 min'
}

const getSessionType = (therapist) => {
    return therapist.working_hours ? 'Working Hours' : 'Session'
}

const getRating = (therapist) => {
    return therapist.avg_rating || 4.5
}

const getReviewsCount = (therapist) => {
    return therapist.total_reviews || 0
}

const bookAppointment = (therapist) => {
    router.visit(route('therapists.show', therapist.id))
}
</script> 