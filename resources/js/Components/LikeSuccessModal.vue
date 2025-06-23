<script setup>
import { defineEmits, defineProps, watch, ref } from 'vue';

const props = defineProps({
    show: Boolean,
    type: {
        type: String,
        default: 'like', // 'like', 'match', or 'already_liked'
    },
    userName: String,
    canShowMessageButton: {
        type: Boolean,
        default: true
    },
});

const emit = defineEmits(['close']);

const modalVisible = ref(false);

watch(() => props.show, (newValue) => {
    if (newValue) {
        modalVisible.value = true;
        // Auto close after 3 seconds
        setTimeout(() => {
            closeModal();
        }, 3000);
    } else {
        modalVisible.value = false;
    }
});

const closeModal = () => {
    modalVisible.value = false;
    setTimeout(() => {
        emit('close');
    }, 300); // Wait for animation
};

const getModalContent = () => {
    if (props.type === 'match') {
        return {
            icon: '🎉',
            title: "It's a Match!",
            message: `You and ${props.userName || 'this person'} liked each other!`,
            subMessage: 'You can now message each other.',
            bgGradient: 'from-purple-600 to-pink-600',
            iconBg: 'bg-pink-100',
            iconColor: 'text-pink-600'
        };
    } else if (props.type === 'already_liked') {
        return {
            icon: '💜',
            title: 'Already Liked!',
            message: `You have already liked ${props.userName || 'this person'}!`,
            subMessage: 'Your interest has been previously recorded.',
            bgGradient: 'from-violet-600 to-purple-600',
            iconBg: 'bg-violet-100',
            iconColor: 'text-violet-600'
        };
    } else {
        return {
            icon: '💕',
            title: 'Like Sent!',
            message: `Your like has been sent to ${props.userName || 'this person'}!`,
            subMessage: 'They will be notified about your interest.',
            bgGradient: 'from-purple-600 to-violet-600',
            iconBg: 'bg-purple-100',
            iconColor: 'text-purple-600'
        };
    }
};
</script>

<template>
    <!-- Modal Overlay -->
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        :class="modalVisible ? 'opacity-100' : 'opacity-0'"
        style="transition: opacity 0.3s ease-in-out"
    >
        <!-- Background Overlay -->
        <div
            class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
            @click="closeModal"
        ></div>

        <!-- Modal Content -->
        <div
            class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 transform"
            :class="modalVisible ? 'scale-100 translate-y-0' : 'scale-95 translate-y-4'"
            style="transition: all 0.3s ease-out"
        >
            <!-- Gradient Header -->
            <div class="bg-gradient-to-r p-6 rounded-t-2xl text-center" :class="getModalContent().bgGradient">
                <!-- Icon Circle -->
                <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4" :class="getModalContent().iconBg">
                    <span class="text-3xl">{{ getModalContent().icon }}</span>
                </div>
                
                <!-- Title -->
                <h3 class="text-xl font-bold text-white mb-2">
                    {{ getModalContent().title }}
                </h3>
            </div>

            <!-- Body -->
            <div class="p-6 text-center">
                <!-- Main Message -->
                <p class="text-gray-800 font-medium mb-2">
                    {{ getModalContent().message }}
                </p>
                
                <!-- Sub Message -->
                <p class="text-gray-600 text-sm mb-6">
                    {{ getModalContent().subMessage }}
                </p>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button
                        v-if="type === 'match' && canShowMessageButton"
                        @click="$emit('message')"
                        class="w-full bg-gradient-to-r from-purple-600 to-violet-600 text-white py-3 px-4 rounded-lg font-medium hover:from-purple-700 hover:to-violet-700 transition-all duration-200 transform hover:scale-105"
                    >
                        Send Message
                    </button>
                    
                    <button
                        @click="closeModal"
                        class="w-full border border-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200"
                    >
                        {{ type === 'match' ? 'Maybe Later' : 'Close' }}
                    </button>
                </div>
            </div>

            <!-- Close Button -->
            <button
                @click="closeModal"
                class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors duration-200"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Custom animation for the modal */
.modal-enter-active, .modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from {
    opacity: 0;
    transform: scale(0.9) translateY(-10px);
}

.modal-leave-to {
    opacity: 0;
    transform: scale(0.9) translateY(-10px);
}

/* Pulse animation for the icon */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.animate-pulse-gentle {
    animation: pulse 2s ease-in-out infinite;
}
</style> 