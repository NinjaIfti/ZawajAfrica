<template>
    <div class="adsterra-banner-320x50 my-4 flex justify-center">
        <div class="bg-gray-100 border border-gray-200 rounded-lg p-2 text-center">
            <div class="text-xs text-gray-500 mb-2">Advertisement</div>
            <div :id="bannerId" class="min-h-[50px] w-[320px]"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdsterraBanner320x50',
    data() {
        return {
            bannerId: `adsterra-banner-320x50-${Math.random().toString(36).substr(2, 9)}`
        }
    },
    mounted() {
        this.loadAd()
    },
    methods: {
        loadAd() {
            // Wait for DOM to be ready
            this.$nextTick(() => {
                // Set the ad options for 320x50 mobile banner
                window.atOptions = {
                    'key': '32e2ce291e38cfd947a035aeb2c3549c',
                    'format': 'iframe',
                    'height': 50,
                    'width': 320,
                    'params': {}
                }

                // Create and load the script
                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.src = '//www.highperformanceformat.com/32e2ce291e38cfd947a035aeb2c3549c/invoke.js'
                script.async = true
                script.id = `adsterra-mobile-script-${this.bannerId}`
                
                // Remove any existing script to avoid duplicates
                const existingScript = document.getElementById(script.id)
                if (existingScript) {
                    existingScript.remove()
                }
                
                // Add onload handler to check if ad loaded
                script.onload = () => {
                    console.log('Adsterra 320x50 script loaded successfully')
                    // Check if ad content appeared after a delay
                    setTimeout(() => {
                        const container = document.getElementById(this.bannerId)
                        if (container && container.innerHTML.trim() === '') {
                            console.warn('Adsterra 320x50: No ad content loaded, hiding container')
                            container.style.display = 'none'
                        } else {
                            console.log('Adsterra 320x50: Ad content detected')
                        }
                    }, 3000)
                }
                
                document.head.appendChild(script)
                
                console.log('Adsterra 320x50 mobile banner loaded with ID:', this.bannerId)
            })
        }
    }
}
</script>

<style scoped>
.adsterra-banner-320x50 {
    max-width: 100%;
}

@media (min-width: 769px) {
    .adsterra-banner-320x50 {
        display: none;
    }
}
</style>
