<template>
    <div class="relative" ref="dropdown">
        <!-- Notification Bell Button -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors duration-200"
            :class="{ 'text-green-600': hasUnread }"
        >
            <!-- Bell Icon -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <!-- Badge for unread count -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px]"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-show="isOpen"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-sm text-green-600 hover:text-green-800 font-medium"
                    >
                        Mark all read
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                <div v-if="loading" class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-6 h-6 mx-auto animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    <p class="mt-2">Loading notifications...</p>
                </div>

                <div v-else-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>
                    <p>No notifications yet</p>
                    <p class="text-sm">We'll notify you when something happens!</p>
                </div>

                <div v-else>
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="handleNotificationClick(notification)"
                        class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                        :class="{ 'bg-green-50': !notification.read_at }"
                    >
                        <div class="flex items-start space-x-3">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center"
                                    :class="getIconBgClass(notification.data.color || 'gray')"
                                >
                                    <svg
                                        class="w-4 h-4 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            v-if="notification.data.icon === 'heart'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'chat'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'calendar-check'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'eye'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'shield-check'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'credit-card'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'clock'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'x-circle'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                        <path
                                            v-else-if="notification.data.icon === 'calendar-clock'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                        <path
                                            v-else
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ notification.data.title }}
                                    </p>
                                    <div class="flex items-center space-x-1">
                                        <span
                                            v-if="!notification.read_at"
                                            class="w-2 h-2 bg-green-500 rounded-full"
                                        ></span>
                                        <span class="text-xs text-gray-500">
                                            {{ formatTime(notification.created_at) }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ notification.data.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 py-3 border-t border-gray-200">
                <Link
                    :href="route('notifications.index')"
                    class="block text-center text-sm text-green-600 hover:text-green-800 font-medium"
                    @click="closeDropdown"
                >
                    View all notifications
                </Link>
            </div>
        </div>
    </div>
</template>

<script>
    import { ref, onMounted, onUnmounted, computed } from 'vue';
    import { Link, router } from '@inertiajs/vue3';
    import axios from 'axios';

    export default {
        name: 'NotificationBell',
        components: {
            Link,
        },
        setup() {
            const isOpen = ref(false);
            const loading = ref(false);
            const notifications = ref([]);
            const unreadCount = ref(0);
            const dropdown = ref(null);

            const hasUnread = computed(() => unreadCount.value > 0);

            const toggleDropdown = () => {
                isOpen.value = !isOpen.value;
                if (isOpen.value) {
                    fetchNotifications();
                }
            };

            const closeDropdown = () => {
                isOpen.value = false;
            };

            const fetchNotifications = async () => {
                loading.value = true;
                try {
                    const response = await axios.get('/notifications/unread');
                    notifications.value = response.data.notifications;
                    unreadCount.value = response.data.unread_count;
                } catch (error) {
                    console.error('Failed to fetch notifications:', error);
                } finally {
                    loading.value = false;
                }
            };

            const markAllAsRead = async () => {
                try {
                    await axios.patch('/notifications/mark-all-read');
                    notifications.value.forEach(n => (n.read_at = new Date().toISOString()));
                    unreadCount.value = 0;
                } catch (error) {
                    console.error('Failed to mark notifications as read:', error);
                }
            };

            const handleNotificationClick = async notification => {
                // Mark as read if unread
                if (!notification.read_at) {
                    try {
                        await axios.patch(`/notifications/${notification.id}/read`);
                        notification.read_at = new Date().toISOString();
                        unreadCount.value = Math.max(0, unreadCount.value - 1);
                    } catch (error) {
                        console.error('Failed to mark notification as read:', error);
                    }
                }

                // Navigate to action URL if provided
                if (notification.data.action_url) {
                    closeDropdown();
                    router.visit(notification.data.action_url);
                }
            };

            const getIconBgClass = color => {
                const colors = {
                    purple: 'bg-purple-500',
                    blue: 'bg-blue-500',
                    green: 'bg-green-500',
                    indigo: 'bg-indigo-500',
                    gray: 'bg-gray-500',
                    orange: 'bg-orange-500',
                    red: 'bg-red-500',
                };
                return colors[color] || 'bg-gray-500';
            };

            const formatTime = timestamp => {
                const date = new Date(timestamp);
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);

                if (diffMins < 1) return 'Just now';
                if (diffMins < 60) return `${diffMins}m ago`;
                if (diffHours < 24) return `${diffHours}h ago`;
                if (diffDays < 7) return `${diffDays}d ago`;
                return date.toLocaleDateString();
            };

            // Handle clicks outside dropdown
            const handleClickOutside = event => {
                if (dropdown.value && !dropdown.value.contains(event.target)) {
                    closeDropdown();
                }
            };

            onMounted(() => {
                document.addEventListener('click', handleClickOutside);
                fetchNotifications(); // Initial load for count
            });

            onUnmounted(() => {
                document.removeEventListener('click', handleClickOutside);
            });

            return {
                isOpen,
                loading,
                notifications,
                unreadCount,
                hasUnread,
                dropdown,
                toggleDropdown,
                closeDropdown,
                fetchNotifications,
                markAllAsRead,
                handleNotificationClick,
                getIconBgClass,
                formatTime,
            };
        },
    };
</script>
