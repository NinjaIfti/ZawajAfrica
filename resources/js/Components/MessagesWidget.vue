<script setup>
    import { defineProps } from 'vue';
    import { Link } from '@inertiajs/vue3';

    defineProps({
        messages: {
            type: Array,
            required: true,
        },
    });
</script>

<template>
    <!-- Recent Messages - Hidden on mobile and tablet, visible on large screens -->
    <div class="hidden lg:block bg-white rounded-lg shadow">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">Recent Messages</h2>
                <Link :href="route('messages')" class="text-gray-500 hover:text-gray-700" title="View All Messages">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"
                        />
                    </svg>
                </Link>
            </div>

            <div class="space-y-4">
                <!-- Show messages if available -->
                <div v-if="messages && messages.length > 0">
                    <Link 
                        v-for="message in messages" 
                        :key="message.id" 
                        :href="route('messages.show', message.id)"
                        class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                    >
                        <div class="flex items-center">
                            <div class="relative mr-3 h-14 w-14 overflow-hidden rounded-full bg-gray-200">
                                <img
                                    :src="message.image"
                                    :alt="message.name"
                                    class="h-full w-full object-cover"
                                    @error="$event.target.src = '/images/placeholder.jpg'"
                                />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">{{ message.name }}</h3>
                                <p class="text-gray-500 truncate max-w-[150px]">{{ message.message }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col items-end">
                            <span class="text-gray-500 text-sm">{{ message.time }}</span>
                            <div
                                v-if="message.unread"
                                class="mt-1 h-5 w-5 rounded-full bg-purple-600 text-center text-xs text-white flex items-center justify-center"
                            >
                                !
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Show empty state when no messages -->
                <div v-else class="text-center py-8">
                    <div class="mb-4">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                            />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">No recent messages</p>
                    <p class="text-gray-400 text-xs mt-1">Start a conversation to see messages here</p>
                </div>
            </div>
        </div>
    </div>
</template>
