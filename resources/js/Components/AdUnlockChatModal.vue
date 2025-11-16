<template>
    <Modal :show="show" @close="closeModal" :closeable="!isWatching" maxWidth="md">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Unlock Chat Messages
                </h3>
                <button 
                    v-if="!isWatching" 
                    @click="closeModal" 
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div v-if="!isWatching && !isCompleted" class="text-center">
                <div class="mb-4">
                    <div class="mx-auto w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2M9 12l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold mb-2">Watch a 10-second ad</h4>
                    <p class="text-gray-600 mb-4">
                        Get 3 free messages to chat with anyone on ZawajAfrica
                    </p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                        <div class="flex items-center text-sm text-blue-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Credits expire after 24 hours</span>
                        </div>
                    </div>
                </div>

                <!-- Current Credits Display -->
                <div v-if="currentCredits > 0" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-center text-green-800">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">You have {{ currentCredits }} messages remaining</span>
                    </div>
                    <div v-if="creditsExpireAt" class="text-xs text-green-600 mt-1">
                        Expires {{ formatExpirationTime(creditsExpireAt) }}
                    </div>
                </div>

                <!-- Watch Limit Info -->
                <div v-if="remainingWatches <= 3" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="text-sm text-yellow-800">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ remainingWatches }} ad watches remaining today
                    </div>
                </div>

                <button 
                    @click="startWatching"
                    :disabled="remainingWatches <= 0 || loading"
                    class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="loading">Loading...</span>
                    <span v-else-if="remainingWatches <= 0">Daily limit reached</span>
                    <span v-else>Watch Ad & Get 3 Messages</span>
                </button>
            </div>

            <!-- Ad Watching State -->
            <div v-if="isWatching" class="text-center">
                <div class="mb-6">
                    <div class="mx-auto w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                        <div class="text-2xl font-bold text-purple-600">{{ countdown }}</div>
                    </div>
                    <h4 class="text-xl font-semibold mb-2">Please watch the advertisement</h4>
                    <p class="text-gray-600">
                        Keep this window open and watch for {{ requiredDuration }} seconds
                    </p>
                </div>

                <!-- Ad Container -->
                <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 mb-4 min-h-[250px] flex items-center justify-center">
                    <AdsterraDisplayAd 
                        zone-name="interstitial" 
                        :show-close-button="false"
                        class="w-full"
                        @ad-loaded="onAdLoaded"
                        @ad-error="onAdError"
                    />
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                    <div 
                        class="bg-purple-600 h-2 rounded-full transition-all duration-1000"
                        :style="{ width: progressPercentage + '%' }"
                    ></div>
                </div>

                <p class="text-sm text-gray-500">
                    {{ countdown }} seconds remaining
                </p>
            </div>

            <!-- Completion State -->
            <div v-if="isCompleted" class="text-center">
                <div class="mb-4">
                    <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-green-600 mb-2">Success!</h4>
                    <p class="text-gray-600 mb-4">
                        You've unlocked {{ creditsGranted }} messages
                    </p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                        <div class="text-sm text-green-800">
                            <strong>Total Credits:</strong> {{ totalCredits }} messages<br>
                            <strong>Expires:</strong> {{ formatExpirationTime(newExpirationTime) }}
                        </div>
                    </div>
                </div>

                <button 
                    @click="startMessaging"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200"
                >
                    Start Messaging
                </button>
            </div>

            <!-- Error State -->
            <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center text-red-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm">{{ error }}</span>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Modal from '@/Components/Modal.vue'
import AdsterraDisplayAd from '@/Components/AdsterraDisplayAd.vue'
import axios from 'axios'

export default {
    name: 'AdUnlockChatModal',
    components: {
        Modal,
        AdsterraDisplayAd
    },
    props: {
        show: {
            type: Boolean,
            default: false
        },
        currentCredits: {
            type: Number,
            default: 0
        },
        creditsExpireAt: {
            type: String,
            default: null
        },
        remainingWatches: {
            type: Number,
            default: 10
        }
    },
    emits: ['close', 'credits-updated'],
    setup(props, { emit }) {
        const isWatching = ref(false)
        const isCompleted = ref(false)
        const loading = ref(false)
        const error = ref(null)
        const countdown = ref(10)
        const requiredDuration = 10
        const watchStartTime = ref(null)
        const countdownInterval = ref(null)
        const creditsGranted = ref(0)
        const totalCredits = ref(0)
        const newExpirationTime = ref(null)

        const progressPercentage = computed(() => {
            return ((requiredDuration - countdown.value) / requiredDuration) * 100
        })

        const startWatching = () => {
            if (props.remainingWatches <= 0) {
                error.value = 'Daily ad watch limit reached'
                return
            }

            isWatching.value = true
            isCompleted.value = false
            error.value = null
            countdown.value = requiredDuration
            watchStartTime.value = Date.now()

            // Start countdown
            countdownInterval.value = setInterval(() => {
                countdown.value--
                
                if (countdown.value <= 0) {
                    completeAdWatch()
                }
            }, 1000)
        }

        const completeAdWatch = async () => {
            if (countdownInterval.value) {
                clearInterval(countdownInterval.value)
                countdownInterval.value = null
            }

            const watchDuration = Math.floor((Date.now() - watchStartTime.value) / 1000)
            
            try {
                loading.value = true
                
                const response = await axios.post('/api/adsterra/watch-for-chat', {
                    watch_duration: watchDuration
                })

                if (response.data.success) {
                    isWatching.value = false
                    isCompleted.value = true
                    creditsGranted.value = response.data.credits_granted
                    totalCredits.value = response.data.total_credits
                    newExpirationTime.value = response.data.expires_at

                    // Emit event to update parent component
                    emit('credits-updated', {
                        credits: totalCredits.value,
                        expires_at: newExpirationTime.value
                    })
                } else {
                    throw new Error(response.data.error || 'Failed to process ad watch')
                }
            } catch (err) {
                console.error('Ad watch completion error:', err)
                error.value = err.response?.data?.error || 'Failed to process ad watch. Please try again.'
                isWatching.value = false
            } finally {
                loading.value = false
            }
        }

        const startMessaging = () => {
            console.log('Start Messaging clicked', {
                targetUser: props.targetUser,
                credits: totalCredits.value
            })
            
            // Emit success event with target user info for direct redirect
            emit('success', {
                targetUser: props.targetUser,
                credits: totalCredits.value
            })
            
            // Then close the modal
            closeModal()
        }

        const closeModal = () => {
            // Clean up any running timers
            if (countdownInterval.value) {
                clearInterval(countdownInterval.value)
                countdownInterval.value = null
            }

            // Reset state
            isWatching.value = false
            isCompleted.value = false
            error.value = null
            countdown.value = requiredDuration

            emit('close')
        }

        const onAdLoaded = () => {
            console.log('Ad loaded successfully')
        }

        const onAdError = (errorMsg) => {
            console.error('Ad loading error:', errorMsg)
            error.value = 'Failed to load advertisement. Please try again.'
            isWatching.value = false
        }

        const formatExpirationTime = (timeString) => {
            if (!timeString) return 'Unknown'
            
            try {
                const date = new Date(timeString)
                const now = new Date()
                const diffHours = Math.ceil((date - now) / (1000 * 60 * 60))
                
                if (diffHours <= 1) {
                    const diffMinutes = Math.ceil((date - now) / (1000 * 60))
                    return `in ${diffMinutes} minutes`
                } else if (diffHours < 24) {
                    return `in ${diffHours} hours`
                } else {
                    return date.toLocaleDateString()
                }
            } catch (e) {
                return 'Unknown'
            }
        }

        // Cleanup on unmount
        onUnmounted(() => {
            if (countdownInterval.value) {
                clearInterval(countdownInterval.value)
            }
        })

        return {
            isWatching,
            isCompleted,
            loading,
            error,
            countdown,
            requiredDuration,
            progressPercentage,
            creditsGranted,
            totalCredits,
            newExpirationTime,
            startWatching,
            startMessaging,
            closeModal,
            onAdLoaded,
            onAdError,
            formatExpirationTime
        }
    }
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
</style>
