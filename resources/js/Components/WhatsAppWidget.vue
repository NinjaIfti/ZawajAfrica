<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        default: null
    },
    customMessage: {
        type: String,
        default: ''
    },
    show: {
        type: Boolean,
        default: true
    }
});

const isVisible = ref(true);
const showTooltip = ref(false);

const defaultMessage = computed(() => {
    if (props.customMessage) {
        return props.customMessage;
    }
    
    const username = props.user?.email || props.user?.name || 'N/A';
    return `Hello ZawajAfrica, I have made my payment. Kindly upgrade my account. My username is: ${username}`;
});

const whatsappUrl = computed(() => {
    const message = encodeURIComponent(defaultMessage.value);
    return `https://wa.me/2347037727643?text=${message}`;
});

const openWhatsApp = () => {
    window.open(whatsappUrl.value, '_blank');
};

// Auto-hide tooltip after 5 seconds
onMounted(() => {
    setTimeout(() => {
        showTooltip.value = true;
        setTimeout(() => {
            showTooltip.value = false;
        }, 5000);
    }, 2000);
});
</script>

<template>
    <div v-if="show && isVisible" class="fixed bottom-6 right-6 z-50">
        <!-- Tooltip -->
        <div
            v-if="showTooltip"
            class="absolute bottom-16 right-0 bg-gray-800 text-white text-sm px-3 py-2 rounded-lg shadow-lg whitespace-nowrap transform transition-all duration-300 opacity-100 scale-100"
            style="max-width: 200px;"
        >
            <span class="hidden lg:inline">Need Help? Send Payment Proof</span>
            <span class="lg:hidden">Send Payment Proof</span>
            <!-- Tooltip arrow -->
            <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
        </div>

        <!-- WhatsApp Button -->
        <button
            @click="openWhatsApp"
            @mouseenter="showTooltip = true"
            @mouseleave="showTooltip = false"
            class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-full shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-green-300 group"
            aria-label="Contact us on WhatsApp"
        >
            <!-- WhatsApp Icon -->
            <svg 
                class="w-6 h-6 md:w-7 md:h-7" 
                fill="currentColor" 
                viewBox="0 0 24 24"
            >
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
            </svg>
            
            <!-- Pulse animation -->
            <div class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-20"></div>
        </button>

        <!-- Close button (optional) -->
        <button
            @click="isVisible = false"
            class="absolute -top-2 -right-2 bg-gray-600 hover:bg-gray-700 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center opacity-70 hover:opacity-100 transition-opacity"
            aria-label="Close WhatsApp widget"
        >
            ×
        </button>
    </div>
</template>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.group:hover .animate-float {
    animation: float 2s ease-in-out infinite;
}

/* Ensure the widget doesn't interfere with page content */
@media (max-width: 768px) {
    .fixed {
        bottom: 1rem;
        right: 1rem;
    }
}

/* Pulse animation */
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style> 