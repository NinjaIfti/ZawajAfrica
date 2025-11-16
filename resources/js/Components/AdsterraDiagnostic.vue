<template>
    <div class="adsterra-diagnostic my-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="text-sm font-bold text-blue-800 mb-2">Adsterra Diagnostic</h3>
        <div class="text-xs text-blue-700 space-y-1">
            <div>Script Loaded: {{ scriptLoaded ? '✅ Yes' : '❌ No' }}</div>
            <div>Container ID: {{ containerId }}</div>
            <div>Container Content: {{ containerContent || 'Empty' }}</div>
            <div>Window.atOptions: {{ JSON.stringify(windowAtOptions) }}</div>
            <div>Ad Blocker Detected: {{ adBlockerDetected ? '❌ Yes' : '✅ No' }}</div>
        </div>
        
        <!-- Ad container for testing -->
        <div class="mt-4 p-2 bg-white border rounded">
            <div class="text-xs text-gray-500 mb-2">Diagnostic Ad Container</div>
            <div :id="containerId" class="min-h-[90px] bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center">
                <span class="text-gray-400 text-xs">Waiting for ad...</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdsterraDiagnostic',
    data() {
        return {
            containerId: `diagnostic-ad-${Math.random().toString(36).substr(2, 9)}`,
            scriptLoaded: false,
            containerContent: '',
            windowAtOptions: null,
            adBlockerDetected: false
        }
    },
    mounted() {
        this.checkAdBlocker()
        this.loadDiagnosticAd()
        this.startMonitoring()
    },
    methods: {
        checkAdBlocker() {
            // Simple ad blocker detection
            const testAd = document.createElement('div')
            testAd.innerHTML = '&nbsp;'
            testAd.className = 'adsbox'
            testAd.style.position = 'absolute'
            testAd.style.left = '-10000px'
            document.body.appendChild(testAd)
            
            setTimeout(() => {
                if (testAd.offsetHeight === 0) {
                    this.adBlockerDetected = true
                }
                document.body.removeChild(testAd)
            }, 100)
        },
        
        loadDiagnosticAd() {
            this.$nextTick(() => {
                // Set ad options
                window.atOptions = {
                    'key': 'd1214f3bf383ccc9a397125fddd1db47',
                    'format': 'iframe',
                    'height': 90,
                    'width': 728,
                    'params': {}
                }
                
                this.windowAtOptions = window.atOptions
                
                // Load script
                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.src = '//www.highperformanceformat.com/d1214f3bf383ccc9a397125fddd1db47/invoke.js'
                script.async = true
                script.id = `diagnostic-script-${this.containerId}`
                
                script.onload = () => {
                    this.scriptLoaded = true
                    console.log('Diagnostic: Adsterra script loaded')
                }
                
                script.onerror = () => {
                    console.error('Diagnostic: Failed to load Adsterra script')
                }
                
                document.head.appendChild(script)
            })
        },
        
        startMonitoring() {
            // Monitor container content every 2 seconds
            const monitor = setInterval(() => {
                const container = document.getElementById(this.containerId)
                if (container) {
                    this.containerContent = container.innerHTML.substring(0, 100)
                    
                    // If we detect content, stop monitoring
                    if (container.innerHTML.trim() && !container.innerHTML.includes('Waiting for ad')) {
                        console.log('Diagnostic: Ad content detected!')
                        clearInterval(monitor)
                    }
                }
            }, 2000)
            
            // Stop monitoring after 30 seconds
            setTimeout(() => {
                clearInterval(monitor)
            }, 30000)
        }
    }
}
</script>
</template>
