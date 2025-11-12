<template>
    <div v-if="showCredits" class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-3 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-purple-800">
                        {{ remainingCredits }} Free Messages
                    </div>
                    <div class="text-xs text-purple-600">
                        Expires {{ formatExpirationTime(expiresAt) }}
                    </div>
                </div>
            </div>
            <button 
                @click="$emit('watch-ad')"
                class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded-full transition duration-200"
            >
                Get More
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ChatCreditsDisplay',
    props: {
        remainingCredits: {
            type: Number,
            default: 0
        },
        expiresAt: {
            type: String,
            default: null
        },
        userTier: {
            type: String,
            default: 'free'
        }
    },
    emits: ['watch-ad'],
    computed: {
        showCredits() {
            return this.userTier === 'free' && this.remainingCredits > 0
        }
    },
    methods: {
        formatExpirationTime(timeString) {
            if (!timeString) return 'Unknown'
            
            try {
                const date = new Date(timeString)
                const now = new Date()
                const diffHours = Math.ceil((date - now) / (1000 * 60 * 60))
                
                if (diffHours <= 1) {
                    const diffMinutes = Math.ceil((date - now) / (1000 * 60))
                    return `in ${diffMinutes}m`
                } else if (diffHours < 24) {
                    return `in ${diffHours}h`
                } else {
                    return 'tomorrow'
                }
            } catch (e) {
                return 'soon'
            }
        }
    }
}
</script>
