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
            script.id = 'adsterra-mobile-script'
            
            // Remove any existing script to avoid duplicates
            const existingScript = document.getElementById('adsterra-mobile-script')
            if (existingScript) {
                existingScript.remove()
            }
            
            document.head.appendChild(script)
            
            console.log('Adsterra 320x50 mobile banner loaded with ID:', this.bannerId)
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
