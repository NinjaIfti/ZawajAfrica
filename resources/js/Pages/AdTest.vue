<template>
    <AppLayout title="Adsterra Ad Test">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold mb-4">Adsterra Integration Test</h2>
                        
                        <!-- Debug Information -->
                        <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Debug Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong>Adsterra Enabled:</strong> 
                                    <span class="ml-2" :class="adsterraConfig.enabled ? 'text-green-600' : 'text-red-600'">
                                        {{ adsterraConfig.enabled ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                                <div>
                                    <strong>Show on Page:</strong> 
                                    <span class="ml-2" :class="showOnPage ? 'text-green-600' : 'text-red-600'">
                                        {{ showOnPage ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                                <div>
                                    <strong>User Tier:</strong> 
                                    <span class="ml-2">{{ getUserTier() }}</span>
                                </div>
                                <div>
                                    <strong>Script URL:</strong> 
                                    <span class="ml-2 text-xs break-all">{{ adsterraConfig.script_url }}</span>
                                </div>
                                <div>
                                    <strong>Publisher ID:</strong> 
                                    <span class="ml-2 text-xs">{{ adsterraConfig.publisher_id }}</span>
                                </div>
                                <div>
                                    <strong>Debug Mode:</strong> 
                                    <span class="ml-2" :class="adsterraConfig.debug ? 'text-green-600' : 'text-red-600'">
                                        {{ adsterraConfig.debug ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Ad Zones -->
                        <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Configured Ad Zones</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div v-for="(zoneId, zoneName) in adsterraConfig.ad_zones" :key="zoneName">
                                    <strong>{{ zoneName }}:</strong> 
                                    <span class="ml-2 text-xs" :class="zoneId ? 'text-green-600' : 'text-red-600'">
                                        {{ zoneId || 'Not configured' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Test Ads -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold">Test Advertisements</h3>
                            
                            <!-- Banner Ad Test -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium mb-2">Banner Ad (zone: banner)</h4>
                                <AdsterraDisplayAd 
                                    zone-name="banner" 
                                    placement="test"
                                    :debug="true"
                                    class="border-2 border-dashed border-gray-300"
                                />
                            </div>

                            <!-- Feed Ad Test -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium mb-2">Feed Ad (zone: feed)</h4>
                                <AdsterraDisplayAd 
                                    zone-name="feed" 
                                    placement="test"
                                    :debug="true"
                                    class="border-2 border-dashed border-gray-300"
                                />
                            </div>

                            <!-- Sidebar Ad Test -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium mb-2">Sidebar Ad (zone: sidebar)</h4>
                                <AdsterraDisplayAd 
                                    zone-name="sidebar" 
                                    placement="test"
                                    :debug="true"
                                    class="border-2 border-dashed border-gray-300"
                                />
                            </div>

                            <!-- Native Ad Test -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium mb-2">Native Ad (zone: native)</h4>
                                <AdsterraDisplayAd 
                                    zone-name="native" 
                                    placement="test"
                                    :debug="true"
                                    class="border-2 border-dashed border-gray-300"
                                />
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2 text-blue-800">Instructions</h3>
                            <ol class="list-decimal list-inside text-sm space-y-1 text-blue-700">
                                <li>Check that "Adsterra Enabled" and "Show on Page" are both "Yes"</li>
                                <li>Verify that ad zones have proper zone IDs configured</li>
                                <li>Look for ads appearing in the test sections above</li>
                                <li>Check browser console for any Adsterra-related errors</li>
                                <li>If you're a paid user, you won't see ads (this is expected)</li>
                            </ol>
                        </div>

                        <!-- API Test Button -->
                        <div class="mt-6">
                            <button 
                                @click="testAdsterraAPI" 
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                                :disabled="loading"
                            >
                                <span v-if="loading">Testing...</span>
                                <span v-else>Test Adsterra API</span>
                            </button>
                            
                            <div v-if="apiTestResult" class="mt-4 p-4 rounded-lg" 
                                 :class="apiTestResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                                <pre class="text-xs overflow-auto">{{ JSON.stringify(apiTestResult.data, null, 2) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AdsterraDisplayAd from '@/Components/AdsterraDisplayAd.vue'

const page = usePage()
const loading = ref(false)
const apiTestResult = ref(null)

const adsterraConfig = computed(() => page.props.adsterra?.config || {})
const showOnPage = computed(() => page.props.adsterra?.show_on_page || false)

const getUserTier = () => {
    const user = page.props.auth?.user
    if (!user) return 'guest'
    if (!user.subscription_plan || user.subscription_status !== 'active') {
        return 'free'
    }
    return user.subscription_plan.toLowerCase()
}

const testAdsterraAPI = async () => {
    loading.value = true
    apiTestResult.value = null
    
    try {
        const response = await fetch('/api/adsterra/debug', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        
        const data = await response.json()
        
        apiTestResult.value = {
            success: response.ok,
            data: data
        }
    } catch (error) {
        apiTestResult.value = {
            success: false,
            data: { error: error.message }
        }
    } finally {
        loading.value = false
    }
}
</script> 