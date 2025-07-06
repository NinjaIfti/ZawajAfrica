<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserInsightsChatbot from '@/Components/Admin/UserInsightsChatbot.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            active_users_today: 0,
            platform_activity_today: 0,
            premium_users: 0,
            users_at_limits: 0
        })
    }
});
</script>

<template>
    <Head title="User Insights AI" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        AI Admin Assistant
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Your comprehensive admin helper - generate content, solve problems, analyze data, and more
                    </p>
                </div>
                <div class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    Multi-purpose AI Assistant
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Quick Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 01-2.5 2.244M16.5 4.5c.18-.35.806-1.85 2.244-2.5a2.25 2.25 0 00-.18-4.244" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Active Users Today</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.active_users_today }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Platform Activity</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.platform_activity_today }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Premium Users</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.premium_users }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.633 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Users at Limits</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.users_at_limits }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Chatbot Component -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <UserInsightsChatbot />
                </div>

                <!-- Usage Tips -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-blue-900 mb-4">💡 How to use the AI Admin Assistant</h3>
                    <div class="grid md:grid-cols-3 gap-4 text-sm text-blue-800">
                        <div>
                            <h4 class="font-medium mb-2">📝 Content Generation:</h4>
                            <ul class="space-y-1 list-disc list-inside">
                                <li>"Generate a welcome email for new users"</li>
                                <li>"Create a marketing announcement"</li>
                                <li>"Write a professional email template"</li>
                                <li>"Generate content for premium users"</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2">🔧 Problem Solving:</h4>
                            <ul class="space-y-1 list-disc list-inside">
                                <li>"Help me solve a user complaint issue"</li>
                                <li>"Fix a technical problem with messaging"</li>
                                <li>"Troubleshoot performance issues"</li>
                                <li>"Resolve security concerns"</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2">📊 Analytics & Insights:</h4>
                            <ul class="space-y-1 list-disc list-inside">
                                <li>"Analyze user engagement trends"</li>
                                <li>"Show me conversion rate insights"</li>
                                <li>"Generate a performance report"</li>
                                <li>"Identify user behavior patterns"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Add any custom styles if needed */
</style> 