<template>
    <Head title="Ad Test" />
    
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Adsterra Ad Test Page</h1>
            
            <!-- Manual HTML Ad Test -->
            <div class="bg-white p-6 rounded-lg shadow mb-8">
                <h2 class="text-xl font-semibold mb-4">Manual HTML Integration Test</h2>
                <div class="border border-gray-300 p-4 min-h-[250px]">
                    <div id="40252a1397d95eb269852aea67a5c58f" style="text-align: center; min-height: 200px;">
                        <div style="padding: 20px; background: #f0f0f0; border: 1px dashed #ccc;">
                            Manual Ad Container - Zone: 40252a1397d95eb269852aea67a5c58f
                        </div>
                    </div>
                </div>
                <button @click="loadManualAd" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Load Manual Ad
                </button>
            </div>
            
            <!-- Component Test -->
            <div class="bg-white p-6 rounded-lg shadow mb-8">
                <h2 class="text-xl font-semibold mb-4">Component Integration Test</h2>
                <div class="border border-gray-300 p-4">
                    <AdsterraDisplayAd zone-name="banner" :debug="true" />
                </div>
            </div>
            
            <!-- Test with Different Zone -->
            <div class="bg-white p-6 rounded-lg shadow mb-8">
                <h2 class="text-xl font-semibold mb-4">Test Zone 2</h2>
                <div class="border border-gray-300 p-4 min-h-[250px]">
                    <div id="test-zone-2" style="text-align: center; min-height: 200px;">
                        <div style="padding: 20px; background: #f0f0f0; border: 1px dashed #ccc;">
                            Test Zone 2 Container
                        </div>
                    </div>
                </div>
                <button @click="loadTestZone2" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Load Test Zone 2
                </button>
            </div>
            
            <!-- Debug Info -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Debug Information</h2>
                <div class="space-y-2 text-sm">
                    <div><strong>Adsterra Config:</strong> {{ JSON.stringify(adsterraConfig, null, 2) }}</div>
                    <div><strong>Show on Page:</strong> {{ showOnPage }}</div>
                    <div><strong>Manual Ad Status:</strong> {{ manualAdStatus }}</div>
                    <div><strong>Scripts Loaded:</strong> {{ scriptsLoaded }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import AdsterraDisplayAd from '@/Components/AdsterraDisplayAd.vue'

const page = usePage()
const manualAdStatus = ref('Not loaded')
const scriptsLoaded = ref([])

const adsterraConfig = computed(() => page.props.adsterra?.config || {})
const showOnPage = computed(() => page.props.adsterra?.show_on_page || false)

const loadManualAd = async () => {
    manualAdStatus.value = 'Loading...'
    
    try {
        // Set up global options
        window.atOptions = {
            'key': '40252a1397d95eb269852aea67a5c58f',
            'format': 'iframe',
            'height': 250,
            'width': 300,
            'params': {}
        }
        
        // Create and load script
        const script = document.createElement('script')
        script.type = 'text/javascript'
        script.async = true
        script.src = '//www.highcpmrevenuegate.com/40252a1397d95eb269852aea67a5c58f/invoke.js'
        
        script.onload = () => {
            manualAdStatus.value = 'Script loaded'
            scriptsLoaded.value.push(script.src)
            console.log('Manual ad script loaded')
        }
        
        script.onerror = (e) => {
            manualAdStatus.value = 'Script failed to load'
            console.error('Manual ad script failed:', e)
        }
        
        document.head.appendChild(script)
        
        // Check for content after a delay
        setTimeout(() => {
            const container = document.getElementById('40252a1397d95eb269852aea67a5c58f')
            if (container) {
                console.log('Manual ad container content:', container.innerHTML)
                if (container.innerHTML.includes('iframe') || container.innerHTML.length > 200) {
                    manualAdStatus.value = 'Ad content detected'
                } else {
                    manualAdStatus.value = 'No ad content detected'
                }
            }
        }, 5000)
        
    } catch (error) {
        manualAdStatus.value = 'Error: ' + error.message
        console.error('Manual ad error:', error)
    }
}

const loadTestZone2 = async () => {
    try {
        // Try with a different approach - direct script injection
        const container = document.getElementById('test-zone-2')
        if (container) {
            container.innerHTML = `
                <script type="text/javascript">
                    atOptions = {
                        'key' : '40252a1397d95eb269852aea67a5c58f',
                        'format' : 'iframe',
                        'height' : 250,
                        'width' : 300,
                        'params' : {}
                    };
                </script>
                <script type="text/javascript" src="//www.highcpmrevenuegate.com/40252a1397d95eb269852aea67a5c58f/invoke.js"></script>
            `
            
            // Execute the scripts
            const scripts = container.querySelectorAll('script')
            scripts.forEach(script => {
                if (script.src) {
                    const newScript = document.createElement('script')
                    newScript.src = script.src
                    newScript.async = true
                    document.head.appendChild(newScript)
                } else {
                    eval(script.innerHTML)
                }
            })
        }
    } catch (error) {
        console.error('Test zone 2 error:', error)
    }
}

onMounted(() => {
    console.log('AdTest page mounted')
    console.log('Adsterra config:', adsterraConfig.value)
    console.log('Show on page:', showOnPage.value)
})
</script> 