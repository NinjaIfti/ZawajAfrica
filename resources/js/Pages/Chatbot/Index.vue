<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, nextTick, onMounted, computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();

const props = defineProps({
    user: Object,
    chatHistory: Array,
    suggestedStarters: Array,
    profileCompletion: Number,
    isAvailable: Boolean,
});

// Chat state
const messages = ref([...props.chatHistory]);
const currentMessage = ref('');
const isLoading = ref(false);
const chatContainer = ref(null);
const messageInput = ref(null);

// UI state
const isMobileMenuOpen = ref(false);
const showPreferences = ref(false);

// Preferences state
const preferences = ref({
    communication_style: 'friendly',
    advice_level: 'detailed',
    topics_of_interest: [],
    cultural_context: '',
    language_preference: 'English',
});

// Available preference options
const communicationStyles = [
    { value: 'formal', label: 'Formal & Professional' },
    { value: 'casual', label: 'Casual & Relaxed' },
    { value: 'friendly', label: 'Friendly & Warm' },
    { value: 'professional', label: 'Professional & Direct' },
];

const adviceLevels = [
    { value: 'basic', label: 'Basic - Simple answers' },
    { value: 'detailed', label: 'Detailed - Comprehensive responses' },
    { value: 'comprehensive', label: 'Comprehensive - In-depth analysis' },
];

const topicsOfInterest = [
    'Relationship Advice',
    'Profile Optimization',
    'Communication Tips',
    'Cultural Guidance',
    'Dating Strategies',
    'Personal Growth',
    'Conflict Resolution',
    'Marriage Preparation',
];

// Computed properties
const hasMessages = computed(() => messages.value.length > 0);
const canSendMessage = computed(() => 
    currentMessage.value.trim().length > 0 && 
    !isLoading.value && 
    props.isAvailable
);

// Scroll to bottom of chat
const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

// Send message to chatbot
const sendMessage = async () => {
    if (!canSendMessage.value) return;

    const message = currentMessage.value.trim();
    currentMessage.value = '';
    isLoading.value = true;

    // Add user message to chat
    messages.value.push({
        role: 'user',
        content: message,
        timestamp: new Date().toISOString(),
    });

    await scrollToBottom();

    try {
        const response = await fetch(route('chatbot.chat'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                message,
                preferences: preferences.value,
            }),
        });

        const data = await response.json();

        if (data.success) {
            // Add bot response to chat
            messages.value.push({
                role: 'assistant',
                content: data.message,
                timestamp: data.timestamp,
                model: data.model,
                usage: data.usage,
            });
        } else {
            // Add error message
            messages.value.push({
                role: 'system',
                content: `Error: ${data.error}`,
                timestamp: new Date().toISOString(),
                isError: true,
            });
        }
    } catch (error) {
        console.error('Chat error:', error);
        messages.value.push({
            role: 'system',
            content: 'Sorry, there was an error processing your message. Please try again.',
            timestamp: new Date().toISOString(),
            isError: true,
        });
    } finally {
        isLoading.value = false;
        await scrollToBottom();
        messageInput.value?.focus();
    }
};

// Use suggested starter
const useSuggestedStarter = (starter) => {
    currentMessage.value = starter;
    sendMessage();
};

// Clear chat history
const clearHistory = async () => {
    if (!confirm('Are you sure you want to clear your chat history?')) return;

    try {
        const response = await fetch(route('chatbot.clear'), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': page.props.csrf_token,
            },
        });

        const data = await response.json();
        if (data.success) {
            messages.value = [];
        }
    } catch (error) {
        console.error('Clear history error:', error);
    }
};

// Save preferences
const savePreferences = async () => {
    try {
        const response = await fetch(route('chatbot.preferences.update'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token,
            },
            body: JSON.stringify({
                preferences: preferences.value,
            }),
        });

        const data = await response.json();
        if (data.success) {
            showPreferences.value = false;
        }
    } catch (error) {
        console.error('Save preferences error:', error);
    }
};

// Load user preferences
const loadPreferences = async () => {
    try {
        const response = await fetch(route('chatbot.preferences'));
        const data = await response.json();
        if (data.success && data.preferences) {
            preferences.value = { ...preferences.value, ...data.preferences };
        }
    } catch (error) {
        console.error('Load preferences error:', error);
    }
};

// Format timestamp
const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

// Handle Enter key
const handleKeyDown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

// Toggle mobile menu
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Mount lifecycle
onMounted(() => {
    loadPreferences();
    scrollToBottom();
    messageInput.value?.focus();
});
</script>

<template>
    <Head title="AI Chatbot" />

    <div class="min-h-screen bg-gray-50 flex">
        <!-- Mobile menu backdrop -->
        <div 
            v-if="isMobileMenuOpen" 
            class="fixed inset-0 z-40 bg-black bg-opacity-50 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar -->
        <div class="hidden md:flex md:w-64 md:flex-col">
            <Sidebar :user="user" />
        </div>

        <!-- Mobile Sidebar -->
        <div 
            class="fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 ease-in-out md:hidden mobile-menu"
            :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <Sidebar :user="user" />
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class=" shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 py-4">
                    <div class="flex items-center">
                        <button 
                            @click="toggleMobileMenu"
                            class="md:hidden mr-3 p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl font-semibold text-gray-900">AI Relationship Assistant</h1>
                                <p class="text-sm text-gray-500">Powered by GPT-4.1 Nano</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            @click="showPreferences = true"
                            class="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-lg"
                            title="Preferences"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        
                        <button
                            @click="clearHistory"
                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-gray-100 rounded-lg"
                            title="Clear History"
                            v-if="hasMessages"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Messages Container -->
                <div 
                    ref="chatContainer"
                    class="flex-1 overflow-y-auto p-4 space-y-4"
                >
                    <!-- Welcome Message -->
                    <div v-if="!hasMessages" class="max-w-2xl mx-auto text-center py-8">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Welcome to your AI Relationship Assistant!</h2>
                        <p class="text-gray-600 mb-6">I'm here to help you with relationship advice, profile optimization, and navigating your journey on ZawajAfrica.</p>
                        
                        <!-- Suggested Starters -->
                        <div v-if="suggestedStarters.length" class="space-y-2">
                            <p class="text-sm font-medium text-gray-700 mb-3">Try asking me about:</p>
                            <div class="grid gap-2 md:grid-cols-2">
                                <button
                                    v-for="starter in suggestedStarters"
                                    :key="starter"
                                    @click="useSuggestedStarter(starter)"
                                    class="p-3 text-left text-sm bg-white border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors"
                                >
                                    {{ starter }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div v-for="message in messages" :key="message.timestamp" class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl">
                            <!-- User Message -->
                            <div v-if="message.role === 'user'" class="bg-purple-600 text-white rounded-lg px-4 py-2">
                                <p class="text-sm">{{ message.content }}</p>
                                <span class="text-xs opacity-75 mt-1 block">{{ formatTime(message.timestamp) }}</span>
                            </div>

                            <!-- Assistant Message -->
                            <div v-else-if="message.role === 'assistant'" class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                                <div class="flex items-start space-x-2">
                                    <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ message.content }}</div>
                                        <span class="text-xs text-gray-500 mt-1 block">{{ formatTime(message.timestamp) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- System/Error Message -->
                            <div v-else-if="message.role === 'system'" class="bg-red-50 border border-red-200 rounded-lg px-4 py-2">
                                <p class="text-sm text-red-800">{{ message.content }}</p>
                                <span class="text-xs text-red-600 mt-1 block">{{ formatTime(message.timestamp) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div v-if="isLoading" class="flex justify-start">
                        <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-purple-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Thinking...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="border-t border-gray-200 bg-white p-4">
                    <div class="flex items-end space-x-3">
                        <div class="flex-1">
                            <textarea
                                ref="messageInput"
                                v-model="currentMessage"
                                @keydown="handleKeyDown"
                                :disabled="!isAvailable || isLoading"
                                placeholder="Type your message... (Press Enter to send, Shift+Enter for new line)"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 resize-none focus:ring-2 focus:ring-purple-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                                rows="1"
                                style="min-height: 40px; max-height: 120px;"
                            ></textarea>
                        </div>
                        <PrimaryButton
                            @click="sendMessage"
                            :disabled="!canSendMessage"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-300"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </PrimaryButton>
                    </div>
                    
                    <div v-if="!isAvailable" class="mt-2 text-sm text-red-600">
                        AI Chatbot is currently unavailable. Please try again later.
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences Modal -->
        <div v-if="showPreferences" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 bg-black opacity-50" @click="showPreferences = false"></div>
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Chat Preferences</h3>
                        <button @click="showPreferences = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Communication Style -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Communication Style</label>
                            <select v-model="preferences.communication_style" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option v-for="style in communicationStyles" :key="style.value" :value="style.value">
                                    {{ style.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Advice Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Response Detail Level</label>
                            <select v-model="preferences.advice_level" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option v-for="level in adviceLevels" :key="level.value" :value="level.value">
                                    {{ level.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Topics of Interest -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Topics of Interest</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label v-for="topic in topicsOfInterest" :key="topic" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :value="topic"
                                        v-model="preferences.topics_of_interest"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">{{ topic }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Cultural Context -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cultural Context</label>
                            <input
                                type="text"
                                v-model="preferences.cultural_context"
                                placeholder="e.g., Nigerian, Ghanaian, etc."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                        </div>
                    </div>

                    <div class="flex space-x-3 mt-6">
                        <button
                            @click="showPreferences = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="savePreferences"
                            class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Auto-resize textarea */
textarea {
    resize: none;
    overflow-y: hidden;
}

/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Mobile menu transitions */
.mobile-menu {
    transform: translateX(-100%);
}

.mobile-menu.translate-x-0 {
    transform: translateX(0);
}
</style> 