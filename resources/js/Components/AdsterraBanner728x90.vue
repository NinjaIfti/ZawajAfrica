<template>
    <div v-if="adLoaded" class="adsterra-banner-728x90 my-4 flex justify-center">
        <div class="bg-gray-100 border border-gray-200 rounded-lg p-2 text-center">
            <div class="text-xs text-gray-500 mb-2">Advertisement</div>
            <div :id="bannerId" class="min-h-[90px] w-[728px] max-w-full"></div>
        </div>
    </div>
</template>

<script>
import { usePage } from '@inertiajs/vue3'

export default {
    name: 'AdsterraBanner728x90',
    data() {
        return {
            bannerId: `adsterra-banner-728x90-${Math.random().toString(36).substr(2, 9)}`,
            adLoaded: false,
            loadTimeout: null
        }
    },
    computed: {
        debugMode() {
            const page = usePage()
            return page.props.adsterra?.config?.debug || false
        }
    },
    mounted() {
        this.loadAd()
    },
    beforeUnmount() {
        if (this.loadTimeout) {
            clearTimeout(this.loadTimeout)
        }
    },
    methods: {
        loadAd() {
            // Wait for DOM to be ready
            this.$nextTick(() => {
                // Set the ad options for 728x90 banner
                window.atOptions = {
                    'key': 'd1214f3bf383ccc9a397125fddd1db47',
                    'format': 'iframe',
                    'height': 90,
                    'width': 728,
                    'params': {}
                }

                // Create and load the script
                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.src = '//www.highperformanceformat.com/d1214f3bf383ccc9a397125fddd1db47/invoke.js'
                script.async = true
                script.crossOrigin = 'anonymous'
                script.id = `adsterra-desktop-script-${this.bannerId}`
                
                // Remove any existing script to avoid duplicates
                const existingScript = document.getElementById(script.id)
                if (existingScript) {
                    existingScript.remove()
                }
                
                // Set timeout to check if ad loaded (5 seconds)
                this.loadTimeout = setTimeout(() => {
                    const container = document.getElementById(this.bannerId)
                    if (container && container.innerHTML.trim() === '') {
                        // Ad didn't load, hide component gracefully
                        this.adLoaded = false
                        if (this.debugMode) {
                            console.warn('Adsterra 728x90: Timeout - no ad content loaded, hiding component')
                        }
                    }
                }, 5000)
                
                // Add error handler - fail silently
                script.onerror = () => {
                    if (this.loadTimeout) {
                        clearTimeout(this.loadTimeout)
                    }
                    if (this.debugMode) {
                        console.warn('Adsterra 728x90: Script failed to load (may be blocked by ad blocker)')
                    }
                    // Hide component gracefully
                    this.adLoaded = false
                }
                
                // Add onload handler to check if ad loaded
                script.onload = () => {
                    if (this.debugMode) {
                        console.log('Adsterra 728x90 script loaded successfully')
                    }
                    // Check if ad content appeared after a delay
                    setTimeout(() => {
                        if (this.loadTimeout) {
                            clearTimeout(this.loadTimeout)
                        }
                        const container = document.getElementById(this.bannerId)
                        if (container) {
                            if (container.innerHTML.trim() !== '') {
                                // Ad content detected
                                this.adLoaded = true
                                if (this.debugMode) {
                                    console.log('Adsterra 728x90: Ad content detected')
                                }
                            } else {
                                // No ad content, hide component
                                this.adLoaded = false
                                if (this.debugMode) {
                                    console.warn('Adsterra 728x90: No ad content loaded, hiding component')
                                }
                            }
                        } else {
                            this.adLoaded = false
                        }
                    }, 3000)
                }
                
                document.head.appendChild(script)
                
                if (this.debugMode) {
                    console.log('Adsterra 728x90 banner loaded with ID:', this.bannerId)
                }
            })
        }
    }
}
</script>

<style scoped>
.adsterra-banner-728x90 {
    max-width: 100%;
    overflow: hidden;
}

@media (max-width: 768px) {
    .adsterra-banner-728x90 {
        display: none;
    }
}
</style>
