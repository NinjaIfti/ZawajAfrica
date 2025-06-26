<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    imageUrl: {
        type: String,
        required: true
    },
    isBlurred: {
        type: Boolean,
        default: false
    },
    canUnblur: {
        type: Boolean,
        default: true
    },
    userId: {
        type: [String, Number],
        default: null
    },
    size: {
        type: String,
        default: 'medium',
        validator: value => ['small', 'medium', 'large', 'full'].includes(value)
    },
    showUnblurButton: {
        type: Boolean,
        default: true
    },
    isOwner: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['unblurred', 'blur-toggled']);

const isLocallyUnblurred = ref(false);
const isLoading = ref(false);

// Compute if photo should be blurred
const shouldBlur = computed(() => {
    return props.isBlurred && !isLocallyUnblurred.value;
});

// Size classes for different image sizes
const sizeClasses = computed(() => {
    const sizes = {
        small: 'w-20 h-20',
        medium: 'w-32 h-32', 
        large: 'w-48 h-48',
        full: 'w-full h-full'
    };
    return sizes[props.size] || sizes.medium;
});

// Handle unblur request
const handleUnblur = async () => {
    if (!props.canUnblur || isLoading.value) return;
    
    isLoading.value = true;
    
    try {
        if (props.userId) {
            // Send request to backend to record photo view/unblur
            const response = await axios.post(route('photos.unblur'), {
                user_id: props.userId
            });
            
            if (response.data.success) {
                isLocallyUnblurred.value = true;
                emit('unblurred', props.userId);
            } else {
                alert(response.data.message || 'Unable to view photo at this time');
            }
        } else {
            // For own photos or when no userId provided
            isLocallyUnblurred.value = true;
            emit('unblurred');
        }
    } catch (error) {
        console.error('Error unblurring photo:', error);
        if (error.response?.status === 429) {
            alert('Photo view limit reached. Upgrade your plan to view more photos.');
        } else if (error.response?.status === 403) {
            alert('You need to upgrade your subscription to view this photo.');
        } else {
            alert('Unable to view photo. Please try again later.');
        }
    } finally {
        isLoading.value = false;
    }
};

// Toggle blur (for owners)
const toggleBlur = () => {
    if (props.isOwner) {
        emit('blur-toggled', !props.isBlurred);
    }
};
</script>

<template>
    <div class="relative overflow-hidden rounded-lg" :class="sizeClasses">
        <!-- Main Image -->
        <img
            :src="imageUrl"
            :alt="isBlurred ? 'Blurred photo' : 'Profile photo'"
            :class="[
                'w-full h-full object-cover transition-all duration-300',
                shouldBlur ? 'filter blur-md scale-105' : ''
            ]"
            @error="$event.target.src = '/images/male.png'"
        />
        
        <!-- Blur Overlay -->
        <div 
            v-if="shouldBlur"
            class="absolute inset-0 bg-black bg-opacity-20 backdrop-blur-sm flex items-center justify-center"
        >
            <!-- Unblur Button -->
            <button
                v-if="showUnblurButton && canUnblur"
                @click="handleUnblur"
                :disabled="isLoading"
                class="bg-white bg-opacity-90 hover:bg-opacity-100 text-gray-800 font-semibold py-2 px-4 rounded-full shadow-lg transition-all duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg 
                    v-if="!isLoading"
                    class="w-5 h-5" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg 
                    v-else
                    class="w-5 h-5 animate-spin" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ isLoading ? 'Loading...' : 'View Photo' }}</span>
            </button>
            
            <!-- Blur Lock Icon (when can't unblur) -->
            <div 
                v-else-if="!canUnblur"
                class="bg-gray-800 bg-opacity-80 text-white p-3 rounded-full"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>
        
        <!-- Owner Controls (if is owner) -->
        <div 
            v-if="isOwner" 
            class="absolute top-2 right-2"
        >
            <button
                @click="toggleBlur"
                class="bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-2 rounded-full transition-all duration-200"
                :title="isBlurred ? 'Make photo visible' : 'Blur photo for privacy'"
            >
                <svg 
                    v-if="isBlurred"
                    class="w-4 h-4" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
                <svg 
                    v-else
                    class="w-4 h-4" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
        
        <!-- Blur Status Indicator -->
        <div 
            v-if="isBlurred"
            class="absolute top-2 left-2 bg-purple-600 text-white text-xs px-2 py-1 rounded-full"
        >
            Private
        </div>
    </div>
</template> 