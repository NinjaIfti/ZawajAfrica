<template>
    <div class="space-y-6">
        <!-- Import Users Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Import Users to Zoho Campaign</h3>
                    <div class="flex space-x-2">
                        <button @click="refreshMailingLists" 
                                :disabled="isLoadingLists" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <svg v-if="isLoadingLists" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh Lists
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                        <select v-model="importForm.target_audience" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="all">All Users</option>
                            <option value="premium">Premium Users Only</option>
                            <option value="basic">Basic Users Only</option>
                            <option value="free">Free Users Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">List Name (Optional)</label>
                        <input v-model="importForm.list_name" 
                               type="text" 
                               placeholder="Leave empty for auto-generated name"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button @click="importUsers" 
                            :disabled="isImporting" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        <svg v-if="isImporting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        {{ isImporting ? 'Importing...' : 'Import Users to Zoho' }}
                    </button>
                </div>

                <div v-if="importSuccess" class="mt-4 p-4 bg-green-50 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ importSuccess }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="importError" class="mt-4 p-4 bg-red-50 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ importError }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Campaign Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create Campaign</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Mailing List</label>
                        <select v-model="campaignForm.list_key" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select a mailing list...</option>
                            <option v-for="list in mailingLists" :key="list.listkey" :value="list.listkey">
                                {{ list.listname }} ({{ list.list_count || 0 }} contacts)
                            </option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Name</label>
                            <input v-model="campaignForm.campaign_name" 
                                   type="text" 
                                   placeholder="Optional campaign name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                            <input v-model="campaignForm.subject" 
                                   type="text" 
                                   placeholder="Email subject"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Content</label>
                        <textarea v-model="campaignForm.content" 
                                  rows="8" 
                                  placeholder="Enter your email content here..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <div>
                        <button @click="createCampaign" 
                                :disabled="!campaignForm.list_key || !campaignForm.subject || !campaignForm.content || isCreatingCampaign" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                            <svg v-if="isCreatingCampaign" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            {{ isCreatingCampaign ? 'Creating & Sending...' : 'Create & Send Campaign' }}
                        </button>
                    </div>

                    <div v-if="campaignSuccess" class="mt-4 p-4 bg-green-50 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ campaignSuccess }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="campaignError" class="mt-4 p-4 bg-red-50 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ campaignError }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mailing Lists Overview -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Mailing Lists</h3>
                
                <div v-if="isLoadingLists" class="text-center py-4">
                    <svg class="animate-spin mx-auto h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-gray-500">Loading mailing lists...</p>
                </div>
                
                <div v-else-if="mailingLists.length === 0" class="text-center py-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-2 text-gray-500">No mailing lists found. Import users first to create lists.</p>
                </div>
                
                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="list in mailingLists" :key="list.listkey" 
                         class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                        <h4 class="font-medium text-gray-900 mb-2">{{ list.listname }}</h4>
                        <p class="text-sm text-gray-500 mb-2">{{ list.list_count || 0 }} contacts</p>
                        <p class="text-xs text-gray-400">Created: {{ formatDate(list.created_time) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

// Reactive data
const isImporting = ref(false);
const isCreatingCampaign = ref(false);
const isLoadingLists = ref(false);
const mailingLists = ref([]);

const importForm = reactive({
    target_audience: 'all',
    list_name: ''
});

const campaignForm = reactive({
    list_key: '',
    campaign_name: '',
    subject: '',
    content: ''
});

const importSuccess = ref('');
const importError = ref('');
const campaignSuccess = ref('');
const campaignError = ref('');

// Methods
const clearMessages = () => {
    importSuccess.value = '';
    importError.value = '';
    campaignSuccess.value = '';
    campaignError.value = '';
};

const importUsers = async () => {
    clearMessages();
    isImporting.value = true;
    
    try {
        const response = await fetch(route('admin.zoho.import-users'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(importForm),
        });

        const data = await response.json();

        if (data.success) {
            importSuccess.value = data.message;
            // Reset form
            importForm.list_name = '';
            // Refresh mailing lists
            await refreshMailingLists();
        } else {
            importError.value = data.error || 'Failed to import users';
        }
    } catch (err) {
        importError.value = 'Network error occurred. Please try again.';
    } finally {
        isImporting.value = false;
    }
};

const refreshMailingLists = async () => {
    isLoadingLists.value = true;
    
    try {
        const response = await fetch(route('admin.zoho.mailing-lists'), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const data = await response.json();

        if (data.success) {
            mailingLists.value = data.lists || [];
        } else {
            console.error('Failed to fetch mailing lists:', data.error);
        }
    } catch (err) {
        console.error('Error fetching mailing lists:', err);
    } finally {
        isLoadingLists.value = false;
    }
};

const createCampaign = async () => {
    clearMessages();
    isCreatingCampaign.value = true;
    
    try {
        const response = await fetch(route('admin.zoho.create-campaign'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(campaignForm),
        });

        const data = await response.json();

        if (data.success) {
            campaignSuccess.value = data.message;
            // Reset form
            campaignForm.campaign_name = '';
            campaignForm.subject = '';
            campaignForm.content = '';
            campaignForm.list_key = '';
        } else {
            campaignError.value = data.error || 'Failed to create campaign';
        }
    } catch (err) {
        campaignError.value = 'Network error occurred. Please try again.';
    } finally {
        isCreatingCampaign.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Load mailing lists on component mount
onMounted(() => {
    refreshMailingLists();
});
</script> 