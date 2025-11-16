<template>
    <div class="adsterra-banner-728x90 my-4 flex justify-center">
        <div class="bg-gray-100 border border-gray-200 rounded-lg p-2 text-center">
            <div class="text-xs text-gray-500 mb-2">Advertisement</div>
            <div :id="bannerId" class="min-h-[90px] w-[728px] max-w-full"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdsterraBanner728x90',
    data() {
        return {
            bannerId: `adsterra-banner-728x90-${Math.random().toString(36).substr(2, 9)}`
        }
    },
    mounted() {
        this.loadAd()
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
                script.id = `adsterra-desktop-script-${this.bannerId}`
                
                // Remove any existing script to avoid duplicates
                const existingScript = document.getElementById(script.id)
                if (existingScript) {
                    existingScript.remove()
                }
                
                // Add onload handler to check if ad loaded
                script.onload = () => {
                    console.log('Adsterra 728x90 script loaded successfully')
                    // Check if ad content appeared after a delay
                    setTimeout(() => {
                        const container = document.getElementById(this.bannerId)
                        if (container && container.innerHTML.trim() === '') {
                            console.warn('Adsterra 728x90: No ad content loaded, hiding container')
                            container.style.display = 'none'
                        } else {
                            console.log('Adsterra 728x90: Ad content detected')
                        }
                    }, 3000)
                }
                
                document.head.appendChild(script)
                
                console.log('Adsterra 728x90 banner loaded with ID:', this.bannerId)
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
