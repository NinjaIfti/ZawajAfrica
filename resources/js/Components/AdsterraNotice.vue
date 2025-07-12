<template>
    <div v-if="showNotice" class="adsterra-notice bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4 rounded-lg shadow-lg mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="text-2xl mr-3">💰</div>
                <div>
                    <h3 class="font-bold text-lg">Support ZawajAfrica</h3>
                    <p class="text-sm opacity-90">
                        This site is supported by advertisements. Consider upgrading to Premium for an ad-free experience!
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a 
                    href="/subscription" 
                    class="bg-white text-purple-600 px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100 transition-colors"
                >
                    Go Premium
                </a>
                <button 
                    @click="dismissNotice" 
                    class="text-white hover:text-gray-200 transition-colors p-1"
                    aria-label="Dismiss notice"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
    name: 'AdsterraNotice',
    props: {
        userTier: {
            type: String,
            required: true
        },
        currentPage: {
            type: String,
            required: true
        }
    },
    setup(props) {
        const showNotice = computed(() => {
            // Only show for free users
            if (props.userTier !== 'free') {
                return false
            }

            // Check if notice was dismissed for this page
            const isDismissed = localStorage.getItem(`adsterra_notice_dismissed_${props.currentPage}`)
            if (isDismissed === 'true') {
                return false
            }

            // Show notice on specific pages
            const showOnPages = ['dashboard', 'matches', 'messages', 'profile']
            return showOnPages.includes(props.currentPage)
        })

        const dismissNotice = () => {
            localStorage.setItem(`adsterra_notice_dismissed_${props.currentPage}`, 'true')
            // Force reactivity by updating the parent component or reloading
            window.location.reload()
        }

        return {
            showNotice,
            dismissNotice
        }
    }
}
</script>

<style scoped>
.adsterra-notice {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.adsterra-notice:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
</style> 