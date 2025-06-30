<template>
    <div class="user-insights-chatbot bg-white border border-gray-200 rounded-lg shadow-sm">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-medium">AI User Insights</h3>
                        <p class="text-white text-sm opacity-90">Ask me about user activities and patterns</p>
                    </div>
                </div>
                <button @click="clearChat" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div ref="messagesContainer" class="h-96 overflow-y-auto p-4 space-y-4">
            <!-- Welcome Message -->
            <div v-if="messages.length === 0" class="text-center py-8">
                <div class="w-16 h-16 mx-auto bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h4 class="text-lg font-medium text-gray-900 mb-2">User Insights AI Assistant</h4>
                <p class="text-gray-600 mb-4">Ask me questions about user activities, patterns, and insights.</p>
                
                <!-- Quick Action Buttons -->
                <div class="grid grid-cols-2 gap-2 max-w-md mx-auto">
                    <button 
                        @click="askQuickQuestion('Show me top 5 most active users today')"
                        class="text-sm bg-indigo-50 text-indigo-700 px-3 py-2 rounded-lg hover:bg-indigo-100 transition-colors"
                    >
                        📊 Top Active Users
                    </button>
                    <button 
                        @click="askQuickQuestion('Which users hit their daily limits?')"
                        class="text-sm bg-purple-50 text-purple-700 px-3 py-2 rounded-lg hover:bg-purple-100 transition-colors"
                    >
                        ⚠️ Users at Limits
                    </button>
                    <button 
                        @click="askQuickQuestion('Show premium user engagement this week')"
                        class="text-sm bg-green-50 text-green-700 px-3 py-2 rounded-lg hover:bg-green-100 transition-colors"
                    >
                        💎 Premium Insights
                    </button>
                    <button 
                        @click="askQuickQuestion('Find users who need upgrade suggestions')"
                        class="text-sm bg-orange-50 text-orange-700 px-3 py-2 rounded-lg hover:bg-orange-100 transition-colors"
                    >
                        🚀 Upgrade Candidates
                    </button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div v-for="(message, index) in messages" :key="index" class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                <div class="max-w-3xl" :class="message.role === 'user' ? 'ml-12' : 'mr-12'">
                    <!-- User Message -->
                    <div v-if="message.role === 'user'" class="bg-indigo-600 text-white rounded-lg px-4 py-2">
                        <p class="text-sm">{{ message.content }}</p>
                    </div>
                    
                    <!-- AI Response -->
                    <div v-else class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="prose prose-sm max-w-none">
                                    <div v-if="message.data" class="mb-3">
                                        <!-- User Data Display -->
                                        <div v-if="message.data.user_profile" class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                            <h5 class="font-medium text-blue-900 mb-2">User Profile</h5>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <div><span class="font-medium">Name:</span> {{ message.data.user_profile.name }}</div>
                                                <div><span class="font-medium">Email:</span> {{ message.data.user_profile.email }}</div>
                                                <div><span class="font-medium">Tier:</span> {{ message.data.user_profile.tier }}</div>
                                                <div><span class="font-medium">Joined:</span> {{ formatDate(message.data.user_profile.created_at) }}</div>
                                            </div>
                                        </div>

                                        <!-- Activity Data Display -->
                                        <div v-if="message.data.activities" class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                                            <h5 class="font-medium text-green-900 mb-2">Recent Activities</h5>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <div v-for="(count, activity) in message.data.activities" :key="activity">
                                                    <span class="font-medium">{{ formatActivityName(activity) }}:</span> {{ count }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Statistics Display -->
                                        <div v-if="message.data.statistics" class="bg-purple-50 border border-purple-200 rounded-lg p-3 mb-3">
                                            <h5 class="font-medium text-purple-900 mb-2">Platform Statistics</h5>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <div v-for="(value, key) in message.data.statistics" :key="key">
                                                    <span class="font-medium">{{ formatStatKey(key) }}:</span> {{ value }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="whitespace-pre-wrap text-gray-700">{{ message.content }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading indicator -->
            <div v-if="isLoading" class="flex justify-start">
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mr-12">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <span class="text-gray-600 text-sm">Analyzing user data...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="border-t border-gray-200 p-4">
            <form @submit.prevent="sendMessage" class="flex space-x-3">
                <input
                    v-model="newMessage"
                    type="text"
                    placeholder="Ask about user activities, patterns, or insights..."
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    :disabled="isLoading"
                >
                <button
                    type="submit"
                    :disabled="!newMessage.trim() || isLoading"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg v-if="isLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue'

const messages = ref([])
const newMessage = ref('')
const isLoading = ref(false)
const messagesContainer = ref(null)

// Load chat history from localStorage on component mount
onMounted(() => {
    loadChatHistory()
    scrollToBottom()
})

const loadChatHistory = () => {
    try {
        const savedMessages = localStorage.getItem('userInsightsChatHistory')
        if (savedMessages) {
            messages.value = JSON.parse(savedMessages)
        }
    } catch (error) {
        console.error('Error loading chat history:', error)
    }
}

const saveChatHistory = () => {
    try {
        localStorage.setItem('userInsightsChatHistory', JSON.stringify(messages.value))
    } catch (error) {
        console.error('Error saving chat history:', error)
    }
}

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    })
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatActivityName = (activity) => {
    const names = {
        'profile_views': 'Profile Views',
        'messages_sent': 'Messages Sent',
        'likes_sent': 'Likes Sent',
        'matches_created': 'Matches Created',
        'profile_updates': 'Profile Updates',
        'ads_viewed': 'Ads Viewed'
    }
    return names[activity] || activity
}

const formatStatKey = (key) => {
    return key.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

const sendMessage = async () => {
    if (!newMessage.value.trim()) return

    const userMessage = {
        role: 'user',
        content: newMessage.value.trim(),
        timestamp: new Date()
    }

    messages.value.push(userMessage)
    saveChatHistory() // Save after adding user message
    
    const query = newMessage.value.trim()
    newMessage.value = ''
    
    isLoading.value = true
    scrollToBottom()

    try {
        const response = await fetch(route('admin.ai.user-insights'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                query: query,
                conversation_history: messages.value.slice(-10) // Send last 10 messages for context
            }),
        })

        const data = await response.json()

        if (data.success) {
            const aiMessage = {
                role: 'assistant',
                content: data.response,
                data: data.data || null,
                timestamp: new Date()
            }
            messages.value.push(aiMessage)
            saveChatHistory() // Save after adding AI response
        } else {
            const errorMessage = {
                role: 'assistant',
                content: `Sorry, I encountered an error: ${data.error}`,
                timestamp: new Date()
            }
            messages.value.push(errorMessage)
            saveChatHistory() // Save after adding error message
        }
    } catch (error) {
        console.error('Error sending message:', error)
        const errorMessage = {
            role: 'assistant',
            content: 'Sorry, I encountered a network error. Please try again.',
            timestamp: new Date()
        }
        messages.value.push(errorMessage)
        saveChatHistory() // Save after adding error message
    } finally {
        isLoading.value = false
        scrollToBottom()
    }
}

const askQuickQuestion = (question) => {
    newMessage.value = question
    sendMessage()
}

const clearChat = () => {
    if (confirm('Are you sure you want to clear the chat history?')) {
        messages.value = []
        localStorage.removeItem('userInsightsChatHistory')
    }
}
</script>

<style scoped>
.prose h5 {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
}

.prose p {
    margin: 0;
}

.user-insights-chatbot {
    min-height: 600px;
    display: flex;
    flex-direction: column;
}
</style> 