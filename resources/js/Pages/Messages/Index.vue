<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted } from 'vue';
    import Sidebar from '@/Components/Sidebar.vue';

    const props = defineProps({
        user: Object,
        conversations: Array,
    });

    // Mobile menu state
    const isMobileMenuOpen = ref(false);

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

    // Search input
    const searchQuery = ref('');

    // Filtered conversations based on search
    const filteredConversations = () => {
        if (!searchQuery.value || searchQuery.value.trim() === '') {
            return props.conversations;
        }

        const query = searchQuery.value.toLowerCase();
        return props.conversations.filter(
            conversation =>
                conversation.name.toLowerCase().includes(query) ||
                conversation.last_message.toLowerCase().includes(query)
        );
    };

    // Separate unread and read conversations
    const unreadConversations = () => {
        return filteredConversations().filter(conversation => conversation.unread_count > 0);
    };

    const readConversations = () => {
        return filteredConversations().filter(conversation => conversation.unread_count === 0);
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
    });

    // Remove event listener when component is unmounted
    onUnmounted(() => {
        document.removeEventListener('click', closeMobileMenu);
        document.body.classList.remove('overflow-hidden');
    });
</script>

<template>
    <Head title="Messages" />

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

            <h1 class="text-lg font-bold">Messages</h1>
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

        <!-- Main Content - Messages List - Add left margin on desktop to account for fixed sidebar -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0 md:ml-64">
            <!-- Header with language selector and profile dropdown -->
            <div class="hidden md:block mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Messages</h1>
                    <div class="flex items-center space-x-4">
                       
                        <div class="h-8 w-8 rounded-full bg-gray-300 overflow-hidden">
                            <img
                                :src="user.profile_photo || '/images/placeholder.jpg'"
                                alt="Profile"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages content -->
            <div class="flex flex-col md:flex-row h-full">
                <!-- Messages List -->
                <div class="w-full md:w-1/3 md:border-r md:pr-4">
                    <!-- Search Box -->
                    <div class="mb-4 relative">
                        <div class="flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2">
                            <svg
                                class="mr-2 h-5 w-5 text-gray-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search"
                                class="w-full border-none bg-transparent outline-none"
                            />
                        </div>
                    </div>

                    <!-- Messages Section -->
                    <div v-if="props.conversations && props.conversations.length > 0">
                        <!-- Unread Messages Section -->
                        <div v-if="unreadConversations().length > 0" class="mb-6">
                            <h3 class="mb-3 text-sm font-medium text-gray-500">Unread Messages</h3>
                            <div class="space-y-2">
                                <Link
                                    v-for="conversation in unreadConversations()"
                                    :key="conversation.id"
                                    :href="route('messages.show', conversation.id)"
                                    class="flex items-center rounded-lg p-3 hover:bg-white"
                                >
                                    <!-- Profile Picture -->
                                    <div class="relative h-12 w-12 flex-shrink-0 overflow-hidden rounded-full">
                                        <img
                                            :src="conversation.profile_photo"
                                            :alt="conversation.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-if="conversation.is_online"
                                            class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 ring-1 ring-white"
                                        ></div>
                                    </div>

                                    <!-- Message Preview -->
                                    <div class="ml-3 flex-1 overflow-hidden">
                                        <div class="flex items-center justify-between">
                                            <p class="font-medium text-gray-900">{{ conversation.name }}</p>
                                            <p class="text-xs text-gray-500">{{ conversation.last_message_time }}</p>
                                        </div>
                                        <p class="truncate text-sm text-gray-600">{{ conversation.last_message }}</p>
                                    </div>

                                    <!-- Unread Count Badge -->
                                    <div
                                        v-if="conversation.unread_count > 0"
                                        class="ml-2 flex h-5 w-5 items-center justify-center rounded-full bg-purple-600 text-xs font-medium text-white"
                                    >
                                        {{ conversation.unread_count }}
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- All Messages Section -->
                        <div>
                            <h3 class="mb-3 text-sm font-medium text-gray-500">All Messages</h3>
                            <div class="space-y-2">
                                <Link
                                    v-for="conversation in readConversations()"
                                    :key="conversation.id"
                                    :href="route('messages.show', conversation.id)"
                                    class="flex items-center rounded-lg p-3 hover:bg-white"
                                >
                                    <!-- Profile Picture -->
                                    <div class="relative h-12 w-12 flex-shrink-0 overflow-hidden rounded-full">
                                        <img
                                            :src="conversation.profile_photo"
                                            :alt="conversation.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-if="conversation.is_online"
                                            class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 ring-1 ring-white"
                                        ></div>
                                    </div>

                                    <!-- Message Preview -->
                                    <div class="ml-3 flex-1 overflow-hidden">
                                        <div class="flex items-center justify-between">
                                            <p class="font-medium text-gray-900">{{ conversation.name }}</p>
                                            <p class="text-xs text-gray-500">{{ conversation.last_message_time }}</p>
                                        </div>
                                        <p class="truncate text-sm text-gray-600">{{ conversation.last_message }}</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State - No Messages -->
                    <div v-else class="text-center py-12">
                        <div class="mb-4">
                            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">No Messages Yet</h3>
                        <p class="text-gray-500">Your recent messages will appear here when you start conversations</p>
                    </div>
                </div>

                <!-- Message Content Area - Empty State -->
                <div class="hidden md:flex md:w-2/3 md:flex-col md:items-center md:justify-center md:p-8">
                    <div class="text-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="mx-auto h-32 w-32 opacity-50 text-gray-400"
                        >
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            <circle cx="9" cy="10" r="1"></circle>
                            <circle cx="15" cy="10" r="1"></circle>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-700">Open a conversation to see messages</h3>
                    </div>
                </div>
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
</style>
