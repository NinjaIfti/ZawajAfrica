<template>
    <Head title="GPT Integration - Admin" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">GPT Integration</h1>
                    <div class="flex items-center space-x-4">
                        <button
                            @click="refreshCommands"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors"
                        >
                            Refresh Commands
                        </button>
                    </div>
                </div>

                <!-- API Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">API Status</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium">OpenAI API Configured</span>
                            </div>
                            <button
                                @click="testConnection"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
                            >
                                Test Connection
                            </button>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">
                            Using OpenAI API key from environment configuration. No manual setup required.
                        </p>
                    </div>
                </div>

                <!-- Command Interface -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Execute Commands</h3>
                        
                        <!-- Command Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Command</label>
                            <select
                                v-model="selectedCommand"
                                @change="onCommandChange"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">Choose a command...</option>
                                <option v-for="command in availableCommands" :key="command.command" :value="command.command">
                                    {{ command.description }}
                                </option>
                            </select>
                        </div>

                        <!-- Command Parameters -->
                        <div v-if="selectedCommand && commandParameters.length > 0" class="mb-6">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Parameters</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="param in commandParameters" :key="param.name" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ param.label }}
                                        <span v-if="param.required" class="text-red-500">*</span>
                                    </label>
                                    
                                    <!-- String/Text input -->
                                    <input
                                        v-if="param.type === 'string' || param.type === 'text'"
                                        v-model="paramValues[param.name]"
                                        type="text"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :placeholder="param.description"
                                    />
                                    
                                    <!-- Number input -->
                                    <input
                                        v-else-if="param.type === 'integer' || param.type === 'number'"
                                        v-model.number="paramValues[param.name]"
                                        type="number"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :placeholder="param.description"
                                    />
                                    
                                    <!-- Select input -->
                                    <select
                                        v-else-if="param.type === 'select'"
                                        v-model="paramValues[param.name]"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option v-for="option in param.options" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    
                                    <!-- URL input -->
                                    <input
                                        v-else-if="param.type === 'url'"
                                        v-model="paramValues[param.name]"
                                        type="url"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :placeholder="param.description"
                                    />
                                    
                                    <p class="text-xs text-gray-500">{{ param.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Execute Button -->
                        <div v-if="selectedCommand" class="flex items-center space-x-4">
                            <button
                                @click="executeCommand"
                                :disabled="isExecuting"
                                class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-50"
                            >
                                <span v-if="isExecuting">Executing...</span>
                                <span v-else>Execute Command</span>
                            </button>
                            
                            <button
                                @click="clearForm"
                                class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700 transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div v-if="commandResult" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Command Results</h3>
                        
                        <!-- Success/Error Status -->
                        <div class="mb-4">
                            <div v-if="commandResult.success" class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Command executed successfully
                            </div>
                            <div v-else class="flex items-center text-red-600">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Command failed: {{ commandResult.error }}
                            </div>
                        </div>

                        <!-- Results Data -->
                        <div v-if="commandResult.data" class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ JSON.stringify(commandResult.data, null, 2) }}</pre>
                        </div>

                        <!-- Timestamp -->
                        <div class="mt-4 text-sm text-gray-500">
                            Executed at: {{ formatTimestamp(commandResult.timestamp) }}
                        </div>
                    </div>
                </div>

                <!-- SEO Audit Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Audit</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">URL to Audit</label>
                                <input
                                    v-model="seoAuditUrl"
                                    type="url"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="https://example.com"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Audit Type</label>
                                <select
                                    v-model="seoAuditType"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="basic">Basic</option>
                                    <option value="technical">Technical</option>
                                    <option value="content">Content</option>
                                    <option value="full">Full</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button
                                    @click="runSeoAudit"
                                    :disabled="isSeoAuditing"
                                    class="bg-purple-600 text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-purple-700 transition-colors disabled:opacity-50"
                                >
                                    <span v-if="isSeoAuditing">Running Audit...</span>
                                    <span v-else>Run SEO Audit</span>
                                </button>
                            </div>
                        </div>

                        <!-- SEO Audit Results -->
                        <div v-if="seoAuditResult" class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Audit Results</h4>
                            
                            <div v-if="seoAuditResult.success" class="space-y-4">
                                <!-- Summary -->
                                <div class="bg-white rounded-lg p-4 border">
                                    <h5 class="font-medium text-gray-900 mb-2">Summary</h5>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Overall Score:</span>
                                            <span class="font-medium">{{ seoAuditResult.data.summary.overall_score }}%</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Total Checks:</span>
                                            <span class="font-medium">{{ seoAuditResult.data.summary.total_checks }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Good:</span>
                                            <span class="font-medium text-green-600">{{ seoAuditResult.data.summary.good }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Issues:</span>
                                            <span class="font-medium text-red-600">{{ seoAuditResult.data.summary.warning + seoAuditResult.data.summary.critical }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Results -->
                                <div class="bg-white rounded-lg p-4 border">
                                    <h5 class="font-medium text-gray-900 mb-2">Detailed Results</h5>
                                    <div class="space-y-3">
                                        <div v-for="(result, check) in seoAuditResult.data.results" :key="check" class="border-l-4 p-3"
                                             :class="{
                                                 'border-green-500 bg-green-50': result.status === 'good',
                                                 'border-yellow-500 bg-yellow-50': result.status === 'warning',
                                                 'border-red-500 bg-red-50': result.status === 'critical'
                                             }">
                                            <div class="flex items-center justify-between">
                                                <h6 class="font-medium text-gray-900 capitalize">{{ check.replace('_', ' ') }}</h6>
                                                <span class="text-sm px-2 py-1 rounded-full"
                                                      :class="{
                                                          'bg-green-100 text-green-800': result.status === 'good',
                                                          'bg-yellow-100 text-yellow-800': result.status === 'warning',
                                                          'bg-red-100 text-red-800': result.status === 'critical'
                                                      }">
                                                    {{ result.status }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-600">
                                                <pre class="whitespace-pre-wrap">{{ JSON.stringify(result.data, null, 2) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="text-red-600">
                                Audit failed: {{ seoAuditResult.error }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Reactive data
const selectedCommand = ref('');
const commandParameters = ref([]);
const paramValues = ref({});
const availableCommands = ref([]);
const commandResult = ref(null);
const isExecuting = ref(false);
const isSeoAuditing = ref(false);

// SEO Audit
const seoAuditUrl = ref('');
const seoAuditType = ref('full');
const seoAuditResult = ref(null);

// Load available commands on mount
onMounted(() => {
    loadAvailableCommands();
    seoAuditUrl.value = window.location.origin;
});

// Methods
const loadAvailableCommands = async () => {
    try {
        const response = await fetch(route('admin.gpt.commands'));
        const data = await response.json();
        
        if (data.success) {
            availableCommands.value = data.commands;
        }
    } catch (error) {
        console.error('Failed to load commands:', error);
    }
};

const refreshCommands = () => {
    loadAvailableCommands();
};

const testConnection = async () => {
    try {
        const response = await fetch(route('admin.gpt.command'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                command: 'check_site_issues',
                parameters: {}
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Connection successful! OpenAI API is working properly.');
        } else {
            alert('❌ Connection failed: ' + data.error);
        }
    } catch (error) {
        alert('❌ Connection failed: ' + error.message);
    }
};

const onCommandChange = () => {
    if (!selectedCommand.value) {
        commandParameters.value = [];
        paramValues.value = {};
        return;
    }
    
    const command = availableCommands.value.find(c => c.command === selectedCommand.value);
    if (command && command.parameters) {
        commandParameters.value = Object.entries(command.parameters).map(([name, config]) => ({
            name,
            label: name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            type: config.type || 'string',
            required: config.required || false,
            description: config.description || '',
            default: config.default,
            options: config.options || []
        }));
        
        // Set default values
        paramValues.value = {};
        commandParameters.value.forEach(param => {
            if (param.default !== undefined) {
                paramValues.value[param.name] = param.default;
            }
        });
    } else {
        commandParameters.value = [];
        paramValues.value = {};
    }
};

const executeCommand = async () => {
    if (!selectedCommand.value) {
        alert('Please select a command');
        return;
    }
    
    isExecuting.value = true;
    
    try {
        const response = await fetch(route('admin.gpt.command'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                command: selectedCommand.value,
                parameters: paramValues.value
            })
        });
        
        const data = await response.json();
        commandResult.value = data;
        
    } catch (error) {
        commandResult.value = {
            success: false,
            error: error.message
        };
    } finally {
        isExecuting.value = false;
    }
};

const runSeoAudit = async () => {
    if (!seoAuditUrl.value) {
        alert('Please enter a URL to audit');
        return;
    }
    
    isSeoAuditing.value = true;
    
    try {
        const response = await fetch(route('admin.gpt.seo-audit'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                url: seoAuditUrl.value,
                type: seoAuditType.value
            })
        });
        
        const data = await response.json();
        seoAuditResult.value = data;
        
    } catch (error) {
        seoAuditResult.value = {
            success: false,
            error: error.message
        };
    } finally {
        isSeoAuditing.value = false;
    }
};

const clearForm = () => {
    selectedCommand.value = '';
    commandParameters.value = [];
    paramValues.value = {};
    commandResult.value = null;
};

const formatTimestamp = (timestamp) => {
    return new Date(timestamp).toLocaleString();
};
</script> 