<template>
    <div v-if="showNotice" class="adsense-notice bg-gradient-to-r from-pink-500 to-purple-600 text-white p-4 rounded-lg shadow-lg mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold">{{ title }}</h4>
                    <p class="text-sm opacity-90">{{ message }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button 
                    @click="goToSubscription" 
                    class="bg-white text-purple-600 hover:text-purple-700 font-medium py-2 px-4 rounded transition-colors"
                >
                    {{ upgradeButtonText }}
                </button>
                <button 
                    @click="dismiss" 
                    class="text-white hover:text-gray-200 p-1 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Additional info for free users -->
        <div v-if="showAdInfo" class="mt-3 pt-3 border-t border-white/20">
            <p class="text-xs opacity-75">
                💡 As a free user, you'll see ads to help us keep ZawajAfrica free. 
                <span class="font-medium">Upgrade to remove ads and unlock premium features!</span>
            </p>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export default {
    name: 'AdSenseNotice',
    props: {
        userTier: {
            type: String,
            default: 'free'
        },
        showOnPages: {
            type: Array,
            default: () => ['dashboard', 'matches', 'messages']
        },
        currentPage: {
            type: String,
            default: ''
        },
        autoShow: {
            type: Boolean,
            default: true
        }
    },
    
    setup(props) {
        const dismissed = ref(false)
        
        const showNotice = computed(() => {
            return props.autoShow && 
                   !dismissed.value &&
                   props.userTier === 'free' &&
                   props.showOnPages.includes(props.currentPage)
        })
        
        const title = computed(() => {
            switch (props.currentPage) {
                case 'matches':
                    return '💝 Find Your Perfect Match Faster!'
                case 'messages':
                    return '💬 Unlock Unlimited Messaging!'
                case 'dashboard':
                    return '⭐ Upgrade Your Experience!'
                default:
                    return '🚀 Go Premium Today!'
            }
        })
        
        const message = computed(() => {
            switch (props.currentPage) {
                case 'matches':
                    return 'Get unlimited profile views, no ads, and priority in search results.'
                case 'messages':
                    return 'Send unlimited messages and access contact details with Basic plan.'
                case 'dashboard':
                    return 'Remove ads, get unlimited features, and find love faster with premium membership.'
                default:
                    return 'Remove ads and unlock all premium features for better matchmaking.'
            }
        })
        
        const upgradeButtonText = computed(() => {
            switch (props.currentPage) {
                case 'matches':
                    return 'Unlock Matches'
                case 'messages':
                    return 'Enable Messaging'
                default:
                    return 'Upgrade Now'
            }
        })
        
        const showAdInfo = computed(() => {
            return props.currentPage === 'dashboard' || props.currentPage === 'matches'
        })
        
        const goToSubscription = () => {
            router.visit('/subscription')
        }
        
        const dismiss = () => {
            dismissed.value = true
            // Store dismissal in localStorage for session
            localStorage.setItem(`adsense_notice_dismissed_${props.currentPage}`, 'true')
        }
        
        // Check if notice was previously dismissed
        const checkDismissalStatus = () => {
            const isDismissed = localStorage.getItem(`adsense_notice_dismissed_${props.currentPage}`)
            if (isDismissed === 'true') {
                dismissed.value = true
            }
        }
        
        // Initialize dismissal check
        checkDismissalStatus()
        
        return {
            showNotice,
            title,
            message,
            upgradeButtonText,
            showAdInfo,
            goToSubscription,
            dismiss
        }
    }
}
</script>

<style scoped>
.adsense-notice {
    animation: slideInFromTop 0.5s ease-out;
}

@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style> 