<script setup>
    import { Head, router } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import Modal from '@/Components/Modal.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import { ref, reactive } from 'vue';

    const props = defineProps({
        chatbot_stats: {
            type: Object,
            required: true,
            default: () => ({
                total_conversations: 0,
                unique_users: 0,
                avg_messages_per_user: 0,
                total_tokens_used: 0,
            }),
        },
        user_engagement: {
            type: Object,
            required: true,
            default: () => ({
                most_active_chatbot_users: [],
                recent_conversations: [],
                daily_ai_usage: [],
            }),
        },
    });

    // Broadcast Modal State
    const showBroadcastModal = ref(false);
    const broadcastForm = reactive({
        message_type: 'announcement',
        target_audience: 'all',
        topic: '',
        tone: 'friendly'
    });
    const generatedBroadcast = ref(null);
    const isGenerating = ref(false);
    const isSending = ref(false);
    const isEditing = ref(false);
    const editableContent = reactive({
        subject: '',
        body: ''
    });
    const isSavingDraft = ref(false);
    const isLoadingDraft = ref(false);
    const lastDraftSaved = ref(null);
    const error = ref('');

    // Insights Modal State
    const showInsightsModal = ref(false);
    const generatedInsights = ref(null);
    const isGeneratingInsights = ref(false);

    const formatDate = dateString => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const formatNumber = number => {
        return number ? number.toLocaleString() : '0';
    };

    // Generate AI Broadcast
    const generateBroadcast = async () => {
        isGenerating.value = true;
        error.value = '';
        
        try {
            const response = await fetch(route('admin.ai.generate-broadcast'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    tone: 'professional',
                    include_metrics: true
                }),
            });

            const data = await response.json();

            if (data.success) {
                generatedBroadcast.value = data.email;
                showPreview.value = true;
            } else {
                error.value = data.error || 'Failed to generate email';
            }
        } catch (err) {
            console.error('Error generating broadcast:', err);
            error.value = 'Network error occurred. Please try again.';
        } finally {
            isGenerating.value = false;
        }
    };

    // Generate AI Insights
    const generateInsights = async () => {
        isGeneratingInsights.value = true;
        try {
            const response = await fetch(route('admin.ai.insights.generate'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const data = await response.json();

            if (data.success) {
                generatedInsights.value = data.insights;
            } else {
                alert('Failed to generate insights: ' + data.error);
            }
        } catch (error) {
            console.error('Error generating insights:', error);
            alert('An error occurred while generating insights');
        } finally {
            isGeneratingInsights.value = false;
        }
    };

    const resetBroadcastForm = () => {
        broadcastForm.message_type = 'announcement';
        broadcastForm.target_audience = 'all';
        broadcastForm.topic = '';
        broadcastForm.tone = 'friendly';
        generatedBroadcast.value = null;
        isEditing.value = false;
        editableContent.subject = '';
        editableContent.body = '';
        lastDraftSaved.value = null;
    };

    // Enable editing mode
    const enableEdit = () => {
        isEditing.value = true;
    };

    // Save edited content
    const saveEditedContent = () => {
        if (!editableContent.subject.trim()) {
            alert('Subject cannot be empty');
            return;
        }
        if (!editableContent.body.trim()) {
            alert('Email body cannot be empty');
            return;
        }

        // Update the generated broadcast with edited content
        generatedBroadcast.value.subject = editableContent.subject;
        generatedBroadcast.value.body = editableContent.body;
        generatedBroadcast.value.preview = editableContent.body.substring(0, 150) + '...';
        
        isEditing.value = false;
    };

    // Cancel editing and revert to original content
    const cancelEdit = () => {
        if (generatedBroadcast.value) {
            editableContent.subject = generatedBroadcast.value.subject;
            editableContent.body = generatedBroadcast.value.body;
        }
        isEditing.value = false;
    };

    // Save draft
    const saveDraft = async () => {
        if (!editableContent.subject.trim() || !editableContent.body.trim()) {
            alert('Subject and body are required to save draft');
            return;
        }

        isSavingDraft.value = true;
        try {
            const response = await fetch(route('admin.ai.save-broadcast-draft'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    subject: editableContent.subject,
                    body: editableContent.body,
                    target_audience: broadcastForm.target_audience,
                    message_type: broadcastForm.message_type,
                    tone: broadcastForm.tone,
                    topic: broadcastForm.topic
                }),
            });

            const data = await response.json();

            if (data.success) {
                lastDraftSaved.value = data.saved_at;
                alert('Draft saved successfully!');
            } else {
                alert('Failed to save draft: ' + data.error);
            }
        } catch (error) {
            console.error('Error saving draft:', error);
            alert('An error occurred while saving the draft');
        } finally {
            isSavingDraft.value = false;
        }
    };

    // Load draft
    const loadDraft = async () => {
        isLoadingDraft.value = true;
        try {
            const response = await fetch(route('admin.ai.load-broadcast-draft'), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const data = await response.json();

            if (data.success && data.draft) {
                const draft = data.draft;
                
                // Load form data
                broadcastForm.message_type = draft.message_type;
                broadcastForm.target_audience = draft.target_audience;
                broadcastForm.topic = draft.topic;
                broadcastForm.tone = draft.tone;
                
                // Load editable content
                editableContent.subject = draft.subject;
                editableContent.body = draft.body;
                
                // Create a mock generated broadcast to display
                generatedBroadcast.value = {
                    subject: draft.subject,
                    body: draft.body,
                    preview: draft.body.substring(0, 150) + '...'
                };
                
                lastDraftSaved.value = draft.saved_at;
                isEditing.value = true; // Start in edit mode
                
                alert('Draft loaded successfully!');
            } else {
                alert(data.message || 'No draft found');
            }
        } catch (error) {
            console.error('Error loading draft:', error);
            alert('An error occurred while loading the draft');
        } finally {
            isLoadingDraft.value = false;
        }
    };

    const openBroadcastModal = () => {
        resetBroadcastForm();
        showBroadcastModal.value = true;
    };

    const openInsightsModal = () => {
        generatedInsights.value = null;
        showInsightsModal.value = true;
    };

    // Send Broadcast Email
    const sendBroadcastEmail = async () => {
        if (!generatedBroadcast.value) {
            alert('Please generate a broadcast first');
            return;
        }

        if (!confirm(`Are you sure you want to send this email to ${broadcastForm.target_audience === 'all' ? 'all users' : `${broadcastForm.target_audience} users`}? This action cannot be undone.`)) {
            return;
        }

        isSending.value = true;
        error.value = '';

        try {
            const emailToSend = isEditing.value ? editableContent : generatedBroadcast.value;

            const response = await fetch(route('admin.ai.send-broadcast'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    subject: emailToSend.subject,
                    body: emailToSend.body,
                    is_edited: isEditing.value
                }),
            });

            const data = await response.json();

            if (data.success) {
                showPreview.value = false;
                isEditing.value = false;
                generatedBroadcast.value = null;
                editableContent.subject = '';
                editableContent.body = '';
                lastSent.value = new Date().toISOString();
                
                alert(`✅ ${data.message}\n\nStats:\n• Total Users: ${data.stats.total_users}\n• Successfully Sent: ${data.stats.sent_count}\n• Failed: ${data.stats.failed_count}`);
            } else {
                error.value = data.error || 'Failed to send email';
            }
        } catch (err) {
            console.error('Error sending broadcast:', err);
            error.value = 'Network error occurred. Please try again.';
        } finally {
            isSending.value = false;
        }
    };

    const deleteDraft = async () => {
        if (!confirm('Are you sure you want to delete the saved draft?')) return;

        try {
            const response = await fetch(route('admin.ai.delete-broadcast-draft'), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const data = await response.json();

            if (data.success) {
                lastDraftSaved.value = null;
                alert('Draft deleted successfully!');
            } else {
                console.error('Failed to delete draft:', data.error);
            }
        } catch (err) {
            console.error('Error deleting draft:', err);
        }
    };
</script>

<template>
    <Head title="AI Insights & Tools" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">AI Insights & Tools</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Action Buttons -->
                <div class="mb-8 flex flex-wrap gap-4">
                    <PrimaryButton @click="openBroadcastModal" class="bg-purple-600 hover:bg-purple-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        Generate AI Broadcast
                    </PrimaryButton>
                    
                    <SecondaryButton @click="openInsightsModal" class="border-purple-300 text-purple-700 hover:bg-purple-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Get AI Insights
                    </SecondaryButton>
                </div>

                <!-- AI Chatbot Statistics -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-4">
                    <!-- Total Conversations -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-blue-100 p-3 text-blue-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Conversations</p>
                                <p class="text-2xl font-semibold">{{ formatNumber(chatbot_stats.total_conversations) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Unique Users -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-green-100 p-3 text-green-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Unique Users</p>
                                <p class="text-2xl font-semibold">{{ formatNumber(chatbot_stats.unique_users) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Messages Per User -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-yellow-100 p-3 text-yellow-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Avg Messages/User</p>
                                <p class="text-2xl font-semibold">{{ Math.round(chatbot_stats.avg_messages_per_user || 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tokens Used -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center">
                            <div class="mr-4 rounded-full bg-purple-100 p-3 text-purple-800">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Tokens Used</p>
                                <p class="text-2xl font-semibold">{{ formatNumber(chatbot_stats.total_tokens_used) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Most Active Users -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-md">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold">Most Active AI Chat Users</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 text-left">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">User</th>
                                        <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500">Messages</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="user in user_engagement.most_active_chatbot_users.slice(0, 8)" :key="user.user_id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-700 text-sm">
                                                    {{ user.user?.name ? user.user.name.charAt(0) : '?' }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ user.user?.name || 'Unknown User' }}</div>
                                                    <div class="text-sm text-gray-500">{{ user.user?.email || 'No email' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                {{ user.message_count }} messages
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Conversations -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-md">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold">Recent AI Conversations</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div class="space-y-4 p-6">
                                <div v-for="conversation in user_engagement.recent_conversations.slice(0, 10)" :key="conversation.id" class="border-l-4 border-purple-200 pl-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-xs font-semibold text-purple-800">
                                                {{ conversation.user?.name ? conversation.user.name.charAt(0) : '?' }}
                                            </div>
                                            <span class="ml-2 text-sm font-medium text-gray-900">{{ conversation.user?.name || 'Unknown User' }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ formatDate(conversation.created_at) }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 truncate">{{ conversation.content.substring(0, 100) }}{{ conversation.content.length > 100 ? '...' : '' }}</p>
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs" :class="conversation.role === 'user' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                                        {{ conversation.role === 'user' ? 'User' : 'AI Assistant' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Broadcast Modal -->
        <Modal :show="showBroadcastModal" @close="showBroadcastModal = false" max-width="4xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Generate AI Broadcast Email</h3>
                    <button @click="showBroadcastModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="!generatedBroadcast" class="space-y-4">
                    <!-- Draft Controls -->
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Draft Options</h4>
                            <p class="text-xs text-gray-600">Load a previously saved draft or start fresh</p>
                        </div>
                        <PrimaryButton @click="loadDraft" :disabled="isLoadingDraft" class="bg-indigo-600 hover:bg-indigo-700">
                            <svg v-if="isLoadingDraft" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            {{ isLoadingDraft ? 'Loading...' : 'Load Draft' }}
                        </PrimaryButton>
                    </div>

                    <!-- Form Fields -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message Type</label>
                            <select v-model="broadcastForm.message_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="announcement">Announcement</option>
                                <option value="promotion">Promotion</option>
                                <option value="update">Platform Update</option>
                                <option value="newsletter">Newsletter</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                            <select v-model="broadcastForm.target_audience" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="all">All Users</option>
                                <option value="premium">Premium Users Only</option>
                                <option value="basic">Basic Users Only</option>
                                <option value="free">Free Users Only</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tone</label>
                            <select v-model="broadcastForm.tone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="formal">Formal</option>
                                <option value="friendly">Friendly</option>
                                <option value="exciting">Exciting</option>
                                <option value="professional">Professional</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Topic/Subject</label>
                        <input 
                            v-model="broadcastForm.topic" 
                            type="text" 
                            placeholder="What is this email about?"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                    </div>

                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showBroadcastModal = false">Cancel</SecondaryButton>
                        <PrimaryButton @click="generateBroadcast" :disabled="isGenerating" class="bg-purple-600 hover:bg-purple-700">
                            <svg v-if="isGenerating" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isGenerating ? 'Generating...' : 'Generate Broadcast' }}
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Generated Broadcast Preview/Edit -->
                <div v-if="generatedBroadcast" class="space-y-4">
                    <!-- Preview Mode -->
                    <div v-if="!isEditing">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-900 mb-2">Generated Email:</h4>
                            <div class="space-y-2">
                                <div><strong>Subject:</strong> {{ generatedBroadcast.subject }}</div>
                                <div><strong>Preview:</strong> {{ generatedBroadcast.preview }}</div>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto">
                            <h5 class="font-medium text-gray-900 mb-2">Full Email Body:</h5>
                            <div class="whitespace-pre-wrap text-sm text-gray-700">{{ generatedBroadcast.body }}</div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <SecondaryButton @click="resetBroadcastForm">Generate New</SecondaryButton>
                            <SecondaryButton @click="enableEdit" class="border-blue-300 text-blue-700 hover:bg-blue-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Email
                            </SecondaryButton>
                            <SecondaryButton @click="showBroadcastModal = false">Close</SecondaryButton>
                            <PrimaryButton @click="sendBroadcastEmail" :disabled="isSending" class="bg-green-600 hover:bg-green-700">
                                <svg v-if="isSending" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isSending ? 'Sending...' : `Send to ${broadcastForm.target_audience === 'all' ? 'All Users' : broadcastForm.target_audience + ' Users'}` }}
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div v-if="isEditing" class="space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <h4 class="font-semibold text-blue-900">Edit Email Content</h4>
                            </div>
                            <p class="text-sm text-blue-700">Make any changes you'd like to the AI-generated content before sending.</p>
                        </div>

                        <!-- Edit Subject -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Subject</label>
                            <input 
                                v-model="editableContent.subject" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter email subject..."
                            >
                        </div>

                        <!-- Edit Body -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Body</label>
                            <textarea 
                                v-model="editableContent.body" 
                                rows="12" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter email content..."
                            ></textarea>
                        </div>

                        <!-- Character Count -->
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Characters: {{ editableContent.body.length }} / 5000</span>
                            <span v-if="lastDraftSaved" class="text-green-600 text-xs">
                                Last saved: {{ formatDate(lastDraftSaved) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <div class="flex space-x-3">
                                <SecondaryButton @click="saveDraft" :disabled="isSavingDraft" class="border-indigo-300 text-indigo-700 hover:bg-indigo-50">
                                    <svg v-if="isSavingDraft" class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                    </svg>
                                    {{ isSavingDraft ? 'Saving...' : 'Save Draft' }}
                                </SecondaryButton>
                            </div>
                            
                            <div class="flex space-x-3">
                                <SecondaryButton @click="cancelEdit">Cancel</SecondaryButton>
                                <PrimaryButton @click="saveEditedContent" class="bg-blue-600 hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Changes
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- AI Insights Modal -->
        <Modal :show="showInsightsModal" @close="showInsightsModal = false" max-width="4xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">AI Platform Insights</h3>
                    <button @click="showInsightsModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="!generatedInsights" class="text-center py-8">
                    <PrimaryButton @click="generateInsights" :disabled="isGeneratingInsights" class="bg-purple-600 hover:bg-purple-700">
                        <svg v-if="isGeneratingInsights" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isGeneratingInsights ? 'Analyzing Platform Data...' : 'Generate AI Insights' }}
                    </PrimaryButton>
                </div>

                <div v-if="generatedInsights" class="space-y-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <div class="whitespace-pre-wrap text-sm text-gray-700">{{ generatedInsights }}</div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="generatedInsights = null">Generate New</SecondaryButton>
                        <SecondaryButton @click="showInsightsModal = false">Close</SecondaryButton>
                    </div>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template> 