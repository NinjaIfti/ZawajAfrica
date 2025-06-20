<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class=" border-b border-gray-200 px-4 sm:px-6 lg:px-8">
                <AppHeader :user="$page.props.auth.user">
                    <template #title>
                        <h1 class="text-2xl font-bold text-gray-900 mt-4">Notifications</h1>
                    </template>
                </AppHeader>
            </div>
            
            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    
                    <!-- Notifications List -->
                    <div class="space-y-3">
                        <!-- Loading State -->
                        <div v-if="loading" class="flex justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                        </div>
                        
                        <!-- No Notifications -->
                        <div v-else-if="!notifications.data || notifications.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                            <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
                        </div>
                        
                        <!-- Demo Notifications if no real ones exist -->
                        <template v-else-if="notifications.data.length === 0">
                            <!-- Session Reminder -->
                            <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="/images/placeholder.jpg" alt="Dr. Maria Azad">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900">
                                                    Today you have a session with therapist <span class="font-semibold">Dr. Maria Azad</span> at <span class="font-semibold">09:00 PM</span>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">Today at 9:42 AM</p>
                                            </div>
                                            <button class="ml-4 bg-purple-600 text-white px-4 py-1 rounded-md text-sm font-medium hover:bg-purple-700 transition-colors duration-200">
                                                Join Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Multiple Verification Notifications -->
                            <template v-for="i in 6" :key="i">
                                <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full" src="/images/placeholder.jpg" alt="Verification">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <p class="text-sm text-gray-900">Your documents has been verified.</p>
                                                    <p class="text-xs text-gray-500 mt-1">Today at 9:42 AM</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                        
                        <!-- Real Notifications -->
                        <template v-else>
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
                                            v-if="notification.data?.sender_photo || notification.data?.match_photo || notification.data?.viewer_photo"
                                            class="h-10 w-10 rounded-full overflow-hidden"
                                        >
                                            <img 
                                                :src="notification.data.sender_photo || notification.data.match_photo || notification.data.viewer_photo || '/images/placeholder.jpg'" 
                                                :alt="notification.data.sender_name || notification.data.match_name || notification.data.viewer_name || 'User'"
                                                class="h-full w-full object-cover"
                                            >
                                        </div>
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full flex items-center justify-center"
                                            :class="getIconBgClass(notification.data?.color || 'gray')"
                                        >
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-900">
                                                    {{ notification.data?.message || notification.data?.title || 'Notification' }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ formatTime(notification.created_at) }}
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2 ml-4">
                                                <!-- Action Button -->
                                                <button
                                                    v-if="notification.data?.action_text && notification.data?.action_url"
                                                    @click.stop="router.visit(notification.data.action_url)"
                                                    class="bg-purple-600 text-white px-3 py-1 rounded-md text-sm font-medium hover:bg-purple-700 transition-colors duration-200"
                                                >
                                                    {{ notification.data.action_text }}
                                                </button>
                                                <!-- Mark as Read -->
                                                <button
                                                    v-if="!notification.read_at"
                                                    @click.stop="markAsRead(notification.id)"
                                                    class="text-purple-600 hover:text-purple-800 text-sm font-medium"
                                                >
                                                    Mark Read
                                                </button>
                                                <!-- Delete -->
                                                <button
                                                    @click.stop="deleteNotification(notification.id)"
                                                    class="text-red-600 hover:text-red-800 text-sm"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                    <div v-if="notifications.data && notifications.data.length > 0 && (notifications.next_page_url || notifications.prev_page_url)" class="mt-6 flex justify-center">
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
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'
import AppHeader from '@/Components/AppHeader.vue'
import axios from 'axios'

export default {
    name: 'NotificationsIndex',
    components: {
        Sidebar,
        AppHeader,
        Link
    },
    setup() {
        const loading = ref(false)
        const notifications = ref({ data: [] })
        const counts = ref({ total: 0, unread: 0, read: 0 })

        const fetchNotifications = async () => {
            loading.value = true
            try {
                const response = await axios.get('/notifications/data')
                notifications.value = response.data.notifications
                counts.value = response.data.counts
            } catch (error) {
                console.error('Failed to fetch notifications:', error)
                // Set demo data if fetch fails
                notifications.value = { data: [] }
            } finally {
                loading.value = false
            }
        }

        const markAsRead = async (id) => {
            try {
                await axios.patch(`/notifications/${id}/read`)
                const notification = notifications.value.data.find(n => n.id === id)
                if (notification) {
                    notification.read_at = new Date().toISOString()
                    counts.value.unread = Math.max(0, counts.value.unread - 1)
                    counts.value.read += 1
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error)
            }
        }

        const deleteNotification = async (id) => {
            if (!confirm('Are you sure you want to delete this notification?')) return
            
            try {
                await axios.delete(`/notifications/${id}`)
                const index = notifications.value.data.findIndex(n => n.id === id)
                if (index !== -1) {
                    const notification = notifications.value.data[index]
                    notifications.value.data.splice(index, 1)
                    counts.value.total -= 1
                    if (!notification.read_at) {
                        counts.value.unread -= 1
                    } else {
                        counts.value.read -= 1
                    }
                }
            } catch (error) {
                console.error('Failed to delete notification:', error)
            }
        }

        const handleNotificationClick = async (notification) => {
            // Mark as read if unread
            if (!notification.read_at) {
                await markAsRead(notification.id)
            }

            // Navigate to action URL if provided
            if (notification.data?.action_url) {
                router.visit(notification.data.action_url)
            }
        }

        const getIconBgClass = (color) => {
            const colors = {
                purple: 'bg-purple-500',
                blue: 'bg-blue-500',
                green: 'bg-green-500',
                indigo: 'bg-indigo-500',
                gray: 'bg-gray-500'
            }
            return colors[color] || 'bg-gray-500'
        }

        const formatTime = (timestamp) => {
            const date = new Date(timestamp)
            const now = new Date()
            const diffMs = now - date
            const diffMins = Math.floor(diffMs / 60000)
            const diffHours = Math.floor(diffMs / 3600000)
            const diffDays = Math.floor(diffMs / 86400000)

            if (diffMins < 1) return 'Just now'
            if (diffMins < 60) return `${diffMins}m ago`
            if (diffHours < 24) return `${diffHours}h ago`
            if (diffDays < 7) return `${diffDays}d ago`
            return date.toLocaleDateString()
        }

        onMounted(() => {
            fetchNotifications()
        })

        return {
            loading,
            notifications,
            counts,
            markAsRead,
            deleteNotification,
            handleNotificationClick,
            getIconBgClass,
            formatTime,
            router
        }
    }
}
</script>

<style scoped>
/* Responsive styles */
@media (max-width: 768px) {
    .flex {
        flex-direction: column;
    }
    
    .w-64 {
        width: 100%;
        height: auto;
    }
    
    .h-screen {
        height: auto;
        min-height: 100vh;
    }
}

/* Smooth transitions */
.transition-shadow {
    transition: box-shadow 0.2s ease-in-out;
}

.transition-colors {
    transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}
</style> 