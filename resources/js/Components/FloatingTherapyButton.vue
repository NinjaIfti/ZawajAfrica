<template>
    <div class="floating-therapy-button">
        <button
            @click="openTherapyBooking"
            class="therapy-btn"
            :class="{ 'pulse': isPulsing }"
            :disabled="loading"
        >
            <div class="therapy-btn-content">
                <svg class="therapy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span class="therapy-text">Book a Therapy</span>
            </div>
            
            <!-- Loading spinner -->
            <div v-if="loading" class="loading-spinner">
                <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    pulseInterval: {
        type: Number,
        default: 3000 // 3 seconds
    },
    showButton: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['therapy-clicked']);

const loading = ref(false);
const isPulsing = ref(false);
let pulseTimer = null;

const startPulseAnimation = () => {
    if (!props.showButton) return;
    
    pulseTimer = setInterval(() => {
        isPulsing.value = true;
        setTimeout(() => {
            isPulsing.value = false;
        }, 1000); // Pulse for 1 second
    }, props.pulseInterval);
};

const openTherapyBooking = async () => {
    if (loading.value) return;
    
    loading.value = true;
    emit('therapy-clicked');
    
    try {
        // Navigate to therapy booking page
        router.visit(route('therapists.index'), {
            method: 'get',
            onFinish: () => {
                loading.value = false;
            },
            onError: (errors) => {
                console.error('Failed to navigate to therapy booking:', errors);
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Error opening therapy booking:', error);
        loading.value = false;
    }
};

onMounted(() => {
    if (props.showButton) {
        // Start pulse animation after a short delay
        setTimeout(() => {
            startPulseAnimation();
        }, 2000);
    }
});

onUnmounted(() => {
    if (pulseTimer) {
        clearInterval(pulseTimer);
    }
});
</script>

<style scoped>
.floating-therapy-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    pointer-events: none;
}

.therapy-btn {
    background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 50%, #C084FC 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    pointer-events: auto;
    position: relative;
    overflow: hidden;
    min-width: 160px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.therapy-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(139, 92, 246, 0.6);
    background: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 50%, #A855F7 100%);
}

.therapy-btn:active {
    transform: translateY(0px);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
}

.therapy-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.therapy-btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity 0.3s ease;
}

.therapy-btn:disabled .therapy-btn-content {
    opacity: 0.5;
}

.therapy-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.therapy-text {
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}

.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
}

.loading-spinner svg {
    width: 100%;
    height: 100%;
}

/* Pulse animation */
.therapy-btn.pulse {
    animation: therapyPulse 1s ease-in-out;
}

@keyframes therapyPulse {
    0% {
        transform: scale(1);
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 12px 35px rgba(139, 92, 246, 0.7);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .floating-therapy-button {
        bottom: 15px;
        right: 15px;
    }
    
    .therapy-btn {
        padding: 10px 16px;
        min-width: 140px;
        height: 45px;
    }
    
    .therapy-text {
        font-size: 13px;
    }
    
    .therapy-icon {
        width: 18px;
        height: 18px;
    }
}

@media (max-width: 480px) {
    .floating-therapy-button {
        bottom: 10px;
        right: 10px;
    }
    
    .therapy-btn {
        padding: 8px 14px;
        min-width: 120px;
        height: 40px;
    }
    
    .therapy-text {
        font-size: 12px;
    }
    
    .therapy-icon {
        width: 16px;
        height: 16px;
    }
}

/* Accessibility */
.therapy-btn:focus {
    outline: 2px solid #A855F7;
    outline-offset: 2px;
}

/* Animation for better UX */
.floating-therapy-button {
    animation: slideInFromBottom 0.5s ease-out;
}

@keyframes slideInFromBottom {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Hover effect for the entire button area */
.therapy-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: inherit;
}

.therapy-btn:hover::before {
    opacity: 1;
}
</style>
