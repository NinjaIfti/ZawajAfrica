<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    therapists: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Professional Support</h3>
            <Link :href="route('therapists.index')" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                View All →
            </Link>
        </div>
        
        <div v-if="therapists && therapists.length > 0" class="space-y-4">
            <div v-for="therapist in therapists.slice(0, 2)" :key="therapist.id" 
                 class="flex items-center p-3 border border-gray-200 rounded-lg hover:border-indigo-300 transition-colors">
                <div class="flex-shrink-0">
                    <img v-if="therapist.photo" :src="therapist.photo_url" :alt="therapist.name" 
                         class="h-12 w-12 rounded-full object-cover">
                    <div v-else class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-600 font-semibold">{{ therapist.name.charAt(0) }}</span>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium text-gray-900">{{ therapist.name }}</h4>
                    <p class="text-xs text-gray-500">{{ therapist.degree }}</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <span v-for="spec in therapist.specializations.slice(0, 2)" :key="spec"
                              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ spec }}
                        </span>
                    </div>
                </div>
                <div class="ml-2">
                    <Link :href="route('therapists.show', therapist.id)" 
                          class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-full">
                        Book
                    </Link>
                </div>
            </div>
            
            <div class="text-center pt-2">
                <Link :href="route('therapists.index')" 
                      class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                    Find Your Perfect Therapist →
                </Link>
            </div>
        </div>
        
        <div v-else class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Professional Support</h3>
            <p class="mt-1 text-sm text-gray-500">Connect with qualified therapists for guidance and support.</p>
            <div class="mt-4">
                <Link :href="route('therapists.index')" 
                      class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Browse Therapists
                </Link>
            </div>
        </div>
    </div>
</template> 