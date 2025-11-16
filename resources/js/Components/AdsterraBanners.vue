<template>
    <!-- Responsive Adsterra Banner Ads -->
    <div class="adsterra-banners-container my-4">
        <!-- Desktop Banner (728x90) -->
        <div class="desktop-banner hidden md:flex justify-center mb-4">
            <div class="bg-gray-100 border border-gray-200 rounded-lg p-2 text-center">
                <div class="text-xs text-gray-500 mb-2">Advertisement</div>
                <div id="adsterra-desktop-banner" class="min-h-[90px] w-[728px] max-w-full"></div>
            </div>
        </div>

        <!-- Mobile Banner (320x50) -->
        <div class="mobile-banner flex md:hidden justify-center mb-4">
            <div class="bg-gray-100 border border-gray-200 rounded-lg p-2 text-center">
                <div class="text-xs text-gray-500 mb-2">Advertisement</div>
                <div id="adsterra-mobile-banner" class="min-h-[50px] w-[320px]"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdsterraBanners',
    mounted() {
        this.loadDesktopBanner()
        this.loadMobileBanner()
    },
    methods: {
        loadDesktopBanner() {
            // Load 728x90 desktop banner
            window.atOptions = {
                'key': 'd1214f3bf383ccc9a397125fddd1db47',
                'format': 'iframe',
                'height': 90,
                'width': 728,
                'params': {}
            }

            const script = document.createElement('script')
            script.type = 'text/javascript'
            script.src = '//www.highperformanceformat.com/d1214f3bf383ccc9a397125fddd1db47/invoke.js'
            script.async = true
            
            // Remove existing script to avoid duplicates
            const existing = document.querySelector('script[src="//www.highperformanceformat.com/d1214f3bf383ccc9a397125fddd1db47/invoke.js"]')
            if (existing) existing.remove()
            
            document.head.appendChild(script)
        },
        
        loadMobileBanner() {
            // Load 320x50 mobile banner
            setTimeout(() => {
                window.atOptions = {
                    'key': '32e2ce291e38cfd947a035aeb2c3549c',
                    'format': 'iframe',
                    'height': 50,
                    'width': 320,
                    'params': {}
                }

                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.src = '//www.highperformanceformat.com/32e2ce291e38cfd947a035aeb2c3549c/invoke.js'
                script.async = true
                
                // Remove existing script to avoid duplicates
                const existing = document.querySelector('script[src="//www.highperformanceformat.com/32e2ce291e38cfd947a035aeb2c3549c/invoke.js"]')
                if (existing) existing.remove()
                
                document.head.appendChild(script)
            }, 1000) // Small delay to avoid conflicts
        }
    }
}
</script>

<style scoped>
.adsterra-banners-container {
    max-width: 100%;
    overflow: hidden;
}
</style>
