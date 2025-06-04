<script setup>
import { defineProps } from 'vue';

defineProps({
    therapists: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <!-- Therapists Section -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">Therapists</h2>
                <button class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div v-for="therapist in therapists" :key="therapist.id" class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="relative mr-3 h-16 w-16 overflow-hidden rounded-full bg-gray-200 border-2 border-amber-500">
                            <img :src="therapist.image" :alt="therapist.name" class="h-full w-full object-cover opacity-0" />
                            <div v-if="therapist.online" class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-green-500"></div>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">{{ therapist.name }}</h3>
                            <p class="text-gray-500">{{ therapist.specialty }}</p>
                        </div>
                    </div>
                    
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template> 