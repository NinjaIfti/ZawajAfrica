<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted, nextTick } from 'vue';
    import Sidebar from '@/Components/Sidebar.vue';

    const props = defineProps({
        user: Object,
        otherUser: Object,
        messages: Array,
    });

    // Mobile menu state
    const isMobileMenuOpen = ref(false);
    const newMessage = ref('');
    const messagesContainer = ref(null);

    // Toggle mobile menu
    const toggleMobileMenu = () => {
        isMobileMenuOpen.value = !isMobileMenuOpen.value;

        // Prevent body scrolling when menu is open
        if (isMobileMenuOpen.value) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Send message function
    const sendMessage = async () => {
        if (!newMessage.value.trim()) return;

        try {
            const response = await fetch(route('messages.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    receiver_id: props.otherUser.id,
                    content: newMessage.value.trim()
                })
            });

            if (response.ok) {
                newMessage.value = '';
                // Reload the page to show the new message
                window.location.reload();
            } else {
                const errorData = await response.json();
                alert(errorData.error || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        }
    };

    // Scroll to bottom of messages
    const scrollToBottom = () => {
        nextTick(() => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            }
        });
    };

    // Close mobile menu when clicking outside
    const closeMobileMenu = e => {
        if (isMobileMenuOpen.value && !e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-toggle')) {
            isMobileMenuOpen.value = false;
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Add click event listener when component is mounted
    onMounted(() => {
        document.addEventListener('click', closeMobileMenu);
        scrollToBottom();
    });

    // Remove event listener when component is unmounted
    onUnmounted(() => {
        document.removeEventListener('click', closeMobileMenu);
        document.body.classList.remove('overflow-hidden');
    });
</script>

<template>
    <Head :title="`Messages - ${otherUser.name}`" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-4 flex items-center md:hidden">
            <button @click="toggleMobileMenu" class="mobile-menu-toggle p-1 mr-3" aria-label="Toggle menu">
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: !isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Back to messages -->
            <Link :href="route('messages')" class="p-1 mr-3">
                <svg class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </Link>

            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-gray-300 overflow-hidden mr-3">
                    <img
                        :src="otherUser.profile_photo || '/images/placeholder.jpg'"
                        :alt="otherUser.name"
                        class="h-full w-full object-cover"
                    />
                </div>
                <h1 class="text-lg font-bold">{{ otherUser.name }}</h1>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar Component - Fixed position -->
        <aside
            class="mobile-menu fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:translate-x-0"
            :class="{ 'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen }"
        >
            <Sidebar :user="user" />
        </aside>

        <!-- Main Content - Chat Interface - Add left margin on desktop to account for fixed sidebar -->
        <div class="flex-1 flex flex-col mt-16 md:mt-0 md:ml-64">
            <!-- Chat Header - Desktop only -->
            <div class="hidden md:flex items-center justify-between bg-white shadow-sm p-4 border-b">
                <div class="flex items-center">
                    <Link :href="route('messages')" class="p-2 mr-3 hover:bg-gray-100 rounded-full">
                        <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div class="h-10 w-10 rounded-full bg-gray-300 overflow-hidden mr-3">
                        <img
                            :src="otherUser.profile_photo || '/images/placeholder.jpg'"
                            :alt="otherUser.name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold">{{ otherUser.name }}</h2>
                        <p class="text-sm text-gray-500">Active now</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Additional chat actions can go here -->
                </div>
            </div>

            <!-- Messages Container -->
            <div 
                ref="messagesContainer"
                class="flex-1 overflow-y-auto bg-gray-50 p-4 space-y-4"
                style="min-height: 400px;"
            >
                <div v-if="messages && messages.length > 0">
                    <div 
                        v-for="message in messages" 
                        :key="message.id"
                        class="flex"
                        :class="message.is_mine ? 'justify-end' : 'justify-start'"
                    >
                        <div 
                            class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                            :class="message.is_mine 
                                ? 'bg-purple-600 text-white' 
                                : 'bg-white text-gray-800 shadow-sm'"
                        >
                            <p class="text-sm">{{ message.content }}</p>
                            <p 
                                class="text-xs mt-1"
                                :class="message.is_mine ? 'text-purple-200' : 'text-gray-500'"
                            >
                                {{ message.time }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <div class="mb-4">
                        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Start the conversation</h3>
                    <p class="text-gray-500">Send a message to start chatting with {{ otherUser.name }}</p>
                </div>
            </div>

            <!-- Message Input -->
            <div class="bg-white border-t p-4">
                <form @submit.prevent="sendMessage" class="flex items-center space-x-3">
                    <div class="flex-1">
                        <textarea
                            v-model="newMessage"
                            placeholder="Type a message..."
                            rows="1"
                            class="w-full border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                            @keydown.enter.prevent="sendMessage"
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        :disabled="!newMessage.trim()"
                        class="bg-purple-600 text-white p-2 rounded-full hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
    /* Ensure proper stacking on mobile */
    @media (max-width: 768px) {
        .min-h-screen {
            padding-top: 1rem;
        }
    }

    /* Prevent scrolling when mobile menu is open */
    :global(.overflow-hidden) {
        overflow: hidden;
    }

    /* Transition for mobile menu */
    .translate-x-0 {
        transform: translateX(0);
    }

    .-translate-x-full {
        transform: translateX(-100%);
    }

    @media (min-width: 768px) {
        .md\:translate-x-0 {
            transform: translateX(0) !important;
        }
    }

    /* Custom scrollbar for messages */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
</style> 