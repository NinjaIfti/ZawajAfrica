<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    matches: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <div>
        <h2 class="mb-6 text-xl font-bold">All Matches</h2>
        
        <div class="space-y-6">
            <div v-for="match in matches" :key="match.id" class="relative overflow-hidden rounded-lg bg-gray-800 shadow-lg">
                <!-- Make the entire card clickable except for the action buttons -->
                <Link :href="route('profile.view', { id: match.id })" class="block">
                    <!-- Match Image -->
                    <div class="h-64 w-full bg-gray-700 relative">
                        <img :src="match.image" :alt="match.name" class="h-full w-full object-cover opacity-0" />
                    
                        <!-- Online Status -->
                        <div class="absolute left-4 top-4 flex items-center rounded-full bg-black bg-opacity-70 px-3 py-1 text-sm text-white">
                            <span class="mr-2 h-2 w-2 rounded-full bg-green-500"></span>
                            Online
                        </div>
                        
                        <!-- Favorite Button -->
                        <div class="absolute right-4 top-4 rounded-full bg-amber-500 p-2">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    
                        <!-- Match percentage (on bottom of image) -->
                        <div class="absolute bottom-4 left-4 right-4">
                            <div class="flex items-center justify-between mb-1 text-white">
                                <span class="font-medium">{{ match.compatibility }}%</span>
                                <span class="font-medium">Match</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-white bg-opacity-30">
                                <div class="h-full rounded-full bg-amber-400" :style="{ width: match.compatibility + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </Link>
                
                <!-- Match Info (below image) -->
                <div class="bg-white px-4 py-3 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <Link :href="route('profile.view', { id: match.id })" class="block">
                            <div class="flex items-center">
                                <h3 class="text-lg font-bold">{{ match.name }}</h3>
                                <span class="ml-2 text-amber-500 text-lg">✓</span>
                                <span class="ml-2 text-gray-500">, {{ match.age }}</span>
                            </div>
                            <p class="text-gray-600">{{ match.location }}</p>
                            <p class="text-sm text-gray-500">{{ match.timestamp }}</p>
                        </Link>
                        <div class="flex space-x-2 items-center">
                            <button class="text-purple-800" @click.prevent>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                            <button class="text-purple-800" @click.prevent>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Rainbow pattern image -->
                    <div class="absolute bottom-0 right-8 h-20 w-24 translate-x-1/3 translate-y-1/4">
                        <img src="/images/card.png" alt="Pattern" class="h-full w-full object-contain opacity-60" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template> 