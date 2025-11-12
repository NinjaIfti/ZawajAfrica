<template>
    <!-- This component handles badge updates but doesn't render anything visible -->
    <div style="display: none;"></div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import axios from 'axios'

export default {
    name: 'MessageBadgeManager',
    props: {
        userId: {
            type: Number,
            required: true
        },
        initialUnreadCount: {
            type: Number,
            default: 0
        }
    },
    emits: ['badge-updated', 'new-message'],
    setup(props, { emit }) {
        const unreadCount = ref(props.initialUnreadCount)
        const isSupported = ref(false)
        const permission = ref('default')
        let pollInterval = null
        let lastKnownCount = props.initialUnreadCount

        // Check browser support
        const checkSupport = () => {
            isSupported.value = {
                badge: 'setAppBadge' in navigator,
                notification: 'Notification' in window,
                serviceWorker: 'serviceWorker' in navigator,
                favicon: true // Always supported
            }
        }

        // Request notification permission
        const requestNotificationPermission = async () => {
            if (!isSupported.value.notification) return false

            try {
                const result = await Notification.requestPermission()
                permission.value = result
                return result === 'granted'
            } catch (error) {
                console.error('Error requesting notification permission:', error)
                return false
            }
        }

        // Update app badge (PWA)
        const updateAppBadge = async (count) => {
            if (!isSupported.value.badge) return

            try {
                if (count > 0) {
                    await navigator.setAppBadge(count)
                } else {
                    await navigator.clearAppBadge()
                }
                console.log('App badge updated:', count)
            } catch (error) {
                console.error('Error updating app badge:', error)
            }
        }

        // Update favicon with badge
        const updateFaviconBadge = (count) => {
            try {
                const canvas = document.createElement('canvas')
                canvas.width = 32
                canvas.height = 32
                const ctx = canvas.getContext('2d')

                // Load the original favicon
                const favicon = document.querySelector('link[rel="icon"]')
                const originalHref = favicon?.href || '/images/fav.png'
                
                const img = new Image()
                img.onload = () => {
                    // Draw original favicon
                    ctx.drawImage(img, 0, 0, 32, 32)

                    // Draw badge if count > 0
                    if (count > 0) {
                        const badgeSize = 16
                        const x = 32 - badgeSize
                        const y = 0

                        // Draw red circle
                        ctx.fillStyle = '#ff4444'
                        ctx.beginPath()
                        ctx.arc(x + badgeSize/2, y + badgeSize/2, badgeSize/2, 0, 2 * Math.PI)
                        ctx.fill()

                        // Draw white text
                        ctx.fillStyle = 'white'
                        ctx.font = 'bold 10px Arial'
                        ctx.textAlign = 'center'
                        ctx.textBaseline = 'middle'
                        
                        const displayCount = count > 99 ? '99+' : count.toString()
                        ctx.fillText(displayCount, x + badgeSize/2, y + badgeSize/2)
                    }

                    // Update favicon
                    const link = document.querySelector('link[rel="icon"]') || document.createElement('link')
                    link.type = 'image/x-icon'
                    link.rel = 'icon'
                    link.href = canvas.toDataURL('image/png')
                    
                    if (!document.querySelector('link[rel="icon"]')) {
                        document.head.appendChild(link)
                    }
                }
                img.src = originalHref
            } catch (error) {
                console.error('Error updating favicon badge:', error)
            }
        }

        // Update document title with unread count
        const updateDocumentTitle = (count) => {
            const baseTitle = 'ZawajAfrica'
            
            if (count > 0) {
                const displayCount = count > 99 ? '(99+)' : `(${count})`
                document.title = `${displayCount} ${baseTitle}`
            } else {
                document.title = baseTitle
            }
        }

        // Show browser notification
        const showNotification = (message) => {
            if (!isSupported.value.notification || permission.value !== 'granted') return

            try {
                const notification = new Notification('New Message - ZawajAfrica', {
                    body: `${message.sender.name}: ${message.content}`,
                    icon: '/images/fav.png',
                    badge: '/images/fav.png',
                    tag: `message-${message.id}`,
                    requireInteraction: false,
                    silent: false
                })

                notification.onclick = () => {
                    window.focus()
                    // Navigate to messages
                    window.location.href = `/messages/${message.sender.id}`
                    notification.close()
                }

                // Auto close after 5 seconds
                setTimeout(() => notification.close(), 5000)
            } catch (error) {
                console.error('Error showing notification:', error)
            }
        }

        // Fetch current badge data
        const fetchBadgeData = async () => {
            try {
                const response = await axios.get('/api/message-badge')
                const newCount = response.data.total_unread
                
                if (newCount !== lastKnownCount) {
                    updateBadges(newCount)
                    
                    // If count increased, fetch recent messages for notifications
                    if (newCount > lastKnownCount) {
                        await fetchRecentMessages()
                    }
                    
                    lastKnownCount = newCount
                    emit('badge-updated', response.data)
                }
                
                return response.data
            } catch (error) {
                console.error('Error fetching badge data:', error)
                return null
            }
        }

        // Fetch recent messages for notifications
        const fetchRecentMessages = async () => {
            try {
                const response = await axios.get('/api/recent-messages')
                const messages = response.data.messages
                
                // Show notification for the most recent message
                if (messages.length > 0) {
                    const latestMessage = messages[0]
                    showNotification(latestMessage)
                    emit('new-message', latestMessage)
                }
            } catch (error) {
                console.error('Error fetching recent messages:', error)
            }
        }

        // Update all badge types
        const updateBadges = (count) => {
            unreadCount.value = count
            updateAppBadge(count)
            updateFaviconBadge(count)
            updateDocumentTitle(count)
        }

        // Start polling for updates
        const startPolling = () => {
            // Initial fetch
            fetchBadgeData()
            
            // Poll every 30 seconds
            pollInterval = setInterval(fetchBadgeData, 30000)
        }

        // Stop polling
        const stopPolling = () => {
            if (pollInterval) {
                clearInterval(pollInterval)
                pollInterval = null
            }
        }

        // Handle visibility change (pause polling when tab is hidden)
        const handleVisibilityChange = () => {
            if (document.hidden) {
                stopPolling()
            } else {
                startPolling()
            }
        }

        // Public methods
        const refreshBadge = () => {
            fetchBadgeData()
        }

        const clearBadge = () => {
            updateBadges(0)
            lastKnownCount = 0
        }

        // Initialize
        onMounted(async () => {
            checkSupport()
            
            // Request notification permission
            await requestNotificationPermission()
            
            // Set initial badge
            updateBadges(props.initialUnreadCount)
            
            // Start polling
            startPolling()
            
            // Listen for visibility changes
            document.addEventListener('visibilitychange', handleVisibilityChange)
            
            // Listen for focus events to refresh badge
            window.addEventListener('focus', refreshBadge)
        })

        onUnmounted(() => {
            stopPolling()
            document.removeEventListener('visibilitychange', handleVisibilityChange)
            window.removeEventListener('focus', refreshBadge)
        })

        // Watch for prop changes
        watch(() => props.initialUnreadCount, (newCount) => {
            if (newCount !== unreadCount.value) {
                updateBadges(newCount)
                lastKnownCount = newCount
            }
        })

        return {
            unreadCount,
            isSupported,
            permission,
            refreshBadge,
            clearBadge,
            updateBadges
        }
    }
}
</script>

<style scoped>
/* This component is invisible */
</style>
