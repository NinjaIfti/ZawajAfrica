<template>
    <Head title="Notifications" />
    
    <div class="flex flex-col md:flex-row min-h-screen bg-gray-50 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-4 flex items-center md:hidden">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="mobile-menu-toggle p-1 mr-3" aria-label="Toggle menu">
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: mobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: !mobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <h1 class="text-lg font-bold">Notifications</h1>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="mobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="mobileMenuOpen = false"
        ></div>

        <!-- Left Sidebar Component - Fixed position -->
        <aside
            class="mobile-menu fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:translate-x-0"
            :class="{ 'translate-x-0': mobileMenuOpen, '-translate-x-full': !mobileMenuOpen }"
        >
        <Sidebar :user="$page.props.auth.user" />
        </aside>

        <!-- Main Content - Add left margin on desktop to account for fixed sidebar -->
        <div class="flex-1 flex flex-col overflow-hidden mt-16 md:mt-0 md:ml-64">
            <!-- Header with AppHeader component - Only visible on desktop -->
            <div class="hidden md:block border-b border-gray-200 px-4 lg:px-6 py-4">
                <AppHeader :user="$page.props.auth.user">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Notifications</h1>
                            <div class="flex space-x-2">
                                <button
                                    @click="fetchNotifications"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-purple-700 transition-colors"
                                >
                                    Refresh
                                </button>
                                <button
                                    v-if="counts.unread > 0"
                                    @click="markAllAsRead"
                                    class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition-colors"
                                >
                                    Mark All Read
                                </button>
                            </div>
                        </div>
                    </template>
                </AppHeader>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="max-w-4xl mx-auto">
                    <!-- Stats Bar -->
                    <div v-if="counts.total > 0" class="grid grid-cols-3 gap-2 sm:gap-4 mb-4">
                        <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200">
                            <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ counts.total }}</div>
                            <p class="text-xs sm:text-sm text-gray-600">Total</p>
                        </div>
                        <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200">
                            <div class="text-lg sm:text-2xl font-bold text-purple-600">{{ counts.unread }}</div>
                            <p class="text-xs sm:text-sm text-gray-600">Unread</p>
                        </div>
                        <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200">
                            <div class="text-lg sm:text-2xl font-bold text-green-600">{{ counts.read }}</div>
                            <p class="text-xs sm:text-sm text-gray-600">Read</p>
                        </div>
                    </div>

                    <!-- Mobile Action Buttons - Only visible on mobile -->
                    <div class="md:hidden mb-6 flex flex-wrap gap-2 justify-center">
                        <button
                            @click="fetchNotifications"
                            class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-purple-700 transition-colors inline-flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <button
                            v-if="counts.unread > 0"
                            @click="markAllAsRead"
                            class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition-colors inline-flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Mark All Read
                        </button>
                    </div>

                    <!-- Notifications List -->
                    <div class="space-y-3">
                        <!-- Loading State -->
                        <div v-if="loading" class="flex justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                        </div>

                        <!-- No Notifications -->
                        <div
                            v-else-if="!notifications?.data || notifications.data.length === 0"
                            class="text-center py-12"
                        >
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
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                            <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
                        </div>

                        <!-- Real Notifications -->
                        <template v-else-if="notifications?.data && notifications.data.length > 0">
                            <div
                                v-for="notification in notifications.data"
                                :key="notification.id"
                                @click="handleNotificationClick(notification)"
                                class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200 cursor-pointer"
                                :class="{ 'border-l-4 border-l-purple-500 bg-purple-50': !notification.read_at }"
                            >
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            v-if="
                                                notification.data?.sender_photo ||
                                                notification.data?.match_photo ||
                                                notification.data?.viewer_photo ||
                                                notification.data?.therapist_photo ||
                                                notification.data?.liker_photo
                                            "
                                            class="h-10 w-10 rounded-full overflow-hidden"
                                        >
                                            <img
                                                :src="
                                                    notification.data.sender_photo ||
                                                    notification.data.match_photo ||
                                                    notification.data.viewer_photo ||
                                                    notification.data.therapist_photo ||
                                                    notification.data.liker_photo ||
                                                    '/images/placeholder.jpg'
                                                "
                                                :alt="
                                                    notification.data.sender_name ||
                                                    notification.data.match_name ||
                                                    notification.data.viewer_name ||
                                                    notification.data.therapist_name ||
                                                    notification.data.liker_name ||
                                                    'User'
                                                "
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full flex items-center justify-center"
                                            :class="getIconBgClass(notification.data?.color || 'gray')"
                                        >
                                            <svg
                                                class="w-5 h-5 text-white"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    v-if="notification.data?.icon === 'heart'"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                                />
                                                <path
                                                    v-else-if="notification.data?.icon === 'chat'"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                                />
                                                <path
                                                    v-else-if="notification.data?.icon === 'calendar-check'"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                                                />
                                                <path
                                                    v-else-if="notification.data?.icon === 'shield-check'"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                                />
                                                <path
                                                    v-else-if="notification.data?.icon === 'credit-card'"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                                />
                                                <path
                                                    v-else-if="
                                                        notification.data?.icon === 'clock' ||
                                                        notification.data?.icon === 'calendar-clock'
                                                    "
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                                <path
                                                    v-else-if="notification.data?.icon === 'x-circle'"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                                <path
                                                    v-else-if="notification.data?.icon === 'eye'"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
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
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ notification.data?.title || 'Notification' }}
                                                </p>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ notification.data?.message }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ formatTime(notification.created_at) }}
                                                </p>
                                            </div>
                                            <div class="flex items-center justify-end space-x-2 sm:ml-4">
                                                <!-- Action Button -->
                                                <button
                                                    v-if="
                                                        notification.data?.action_text && notification.data?.action_url
                                                    "
                                                    @click.stop="router.visit(notification.data.action_url)"
                                                    class="bg-purple-600 text-white px-3 py-1.5 rounded-md text-xs sm:text-sm font-medium hover:bg-purple-700 transition-colors duration-200"
                                                >
                                                    {{ notification.data.action_text }}
                                                </button>
                                                <!-- Mark as Read -->
                                                <button
                                                    v-if="!notification.read_at"
                                                    @click.stop="markAsRead(notification.id)"
                                                    class="text-purple-600 hover:text-purple-800 text-xs sm:text-sm font-medium"
                                                >
                                                    Mark Read
                                                </button>
                                                <!-- Delete -->
                                                <button
                                                    @click.stop="deleteNotification(notification.id)"
                                                    class="text-red-600 hover:text-red-800 text-sm"
                                                >
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="
                            notifications.data &&
                            notifications.data.length > 0 &&
                            (notifications.next_page_url || notifications.prev_page_url)
                        "
                        class="mt-6 flex justify-center"
                    >
                        <nav class="flex items-center space-x-2">
                            <Link
                                v-if="notifications.prev_page_url"
                                :href="notifications.prev_page_url"
                                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="notifications.next_page_url"
                                :href="notifications.next_page_url"
                                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { ref, onMounted } from 'vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import Sidebar from '@/Components/Sidebar.vue';
    import AppHeader from '@/Components/AppHeader.vue';
    import axios from 'axios';

    export default {
        name: 'NotificationsIndex',
        components: {
            Head,
            Sidebar,
            AppHeader,
            Link,
        },
        setup() {
            const loading = ref(false);
            const notifications = ref({ data: [] });
            const counts = ref({ total: 0, unread: 0, read: 0 });
            const mobileMenuOpen = ref(false);

            const fetchNotifications = async () => {
                loading.value = true;
                try {
                    const response = await axios.get('/notifications/data');

                    // The response should have: { notifications: PaginatedData, counts: {...} }
                    if (response.data && response.data.notifications) {
                        notifications.value = response.data.notifications;
                        counts.value = response.data.counts || { total: 0, unread: 0, read: 0 };
                    } else {
                        // Fallback if response format is different
                        notifications.value = { data: [] };
                        counts.value = { total: 0, unread: 0, read: 0 };
                    }
                } catch (error) {
                    console.error('Failed to fetch notifications:', error);

                    // Set empty data if fetch fails
                    notifications.value = { data: [] };
                    counts.value = { total: 0, unread: 0, read: 0 };
                } finally {
                    loading.value = false;
                }
            };

            const markAsRead = async id => {
                try {
                    await axios.patch(`/notifications/${id}/read`);
                    const notification = notifications.value.data.find(n => n.id === id);
                    if (notification) {
                        notification.read_at = new Date().toISOString();
                        counts.value.unread = Math.max(0, counts.value.unread - 1);
                        counts.value.read += 1;
                    }
                } catch (error) {
                    console.error('Failed to mark notification as read:', error);
                }
            };

            const markAllAsRead = async () => {
                try {
                    await axios.patch('/notifications/mark-all-read');
                    // Update all unread notifications to read
                    notifications.value.data.forEach(notification => {
                        if (!notification.read_at) {
                            notification.read_at = new Date().toISOString();
                        }
                    });
                    counts.value.read += counts.value.unread;
                    counts.value.unread = 0;
                } catch (error) {
                    console.error('Failed to mark all notifications as read:', error);
                }
            };

            const deleteNotification = async id => {
                if (!confirm('Are you sure you want to delete this notification?')) return;

                try {
                    await axios.delete(`/notifications/${id}`);
                    const index = notifications.value.data.findIndex(n => n.id === id);
                    if (index !== -1) {
                        const notification = notifications.value.data[index];
                        notifications.value.data.splice(index, 1);
                        counts.value.total -= 1;
                        if (!notification.read_at) {
                            counts.value.unread -= 1;
                        } else {
                            counts.value.read -= 1;
                        }
                    }
                } catch (error) {
                    console.error('Failed to delete notification:', error);
                }
            };

            const handleNotificationClick = async notification => {
                // Mark as read if unread
                if (!notification.read_at) {
                    await markAsRead(notification.id);
                }

                // Navigate to action URL if provided
                if (notification.data?.action_url) {
                    router.visit(notification.data.action_url);
                }
            };

            const getIconBgClass = color => {
                const colors = {
                    purple: 'bg-purple-500',
                    blue: 'bg-blue-500',
                    green: 'bg-green-500',
                    indigo: 'bg-indigo-500',
                    red: 'bg-red-500',
                    orange: 'bg-orange-500',
                    yellow: 'bg-yellow-500',
                    pink: 'bg-pink-500',
                    gray: 'bg-gray-500',
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

            onMounted(() => {
                fetchNotifications();
            });

            return {
                loading,
                notifications,
                counts,
                mobileMenuOpen,
                fetchNotifications,
                markAsRead,
                markAllAsRead,
                deleteNotification,
                handleNotificationClick,
                getIconBgClass,
                formatTime,
                router,
            };
        },
    };
</script>

<style scoped>
    /* Smooth transitions */
    .transition-shadow {
        transition: box-shadow 0.2s ease-in-out;
    }

    .transition-colors {
        transition:
            color 0.2s ease-in-out,
            background-color 0.2s ease-in-out;
    }
</style>
