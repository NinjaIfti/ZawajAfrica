<script setup>
import { defineProps } from 'vue';

defineProps({
    messages: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <!-- Recent Messages - Hidden on mobile and tablet, visible on large screens -->
    <div class="hidden lg:block bg-white rounded-lg shadow">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">Recent Messages</h2>
                <button class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div v-for="message in messages" :key="message.id" class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="relative mr-3 h-14 w-14 overflow-hidden rounded-full bg-gray-200">
                            <img :src="message.image" :alt="message.name" class="h-full w-full object-cover opacity-0" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">{{ message.name }}</h3>
                            <p class="text-gray-500">{{ message.message }}</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end">
                        <span class="text-gray-500">{{ message.time }}</span>
                        <div v-if="message.unread" class="mt-1 h-6 w-6 rounded-full bg-purple-600 text-center text-sm text-white flex items-center justify-center">3</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template> 