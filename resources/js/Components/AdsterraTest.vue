<template>
    <div class="adsterra-test my-4 flex justify-center">
        <div class="bg-gray-100 border border-gray-200 rounded-lg p-2 text-center">
            <div class="text-xs text-gray-500 mb-2">Test Advertisement</div>
            <!-- This div will be populated by Adsterra -->
            <div ref="adContainer" class="ad-content"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdsterraTest',
    mounted() {
        this.loadTestAd()
    },
    methods: {
        loadTestAd() {
            // Use the exact format from Adsterra
            const script1 = document.createElement('script')
            script1.type = 'text/javascript'
            script1.innerHTML = `
                atOptions = {
                    'key' : 'd1214f3bf383ccc9a397125fddd1db47',
                    'format' : 'iframe',
                    'height' : 90,
                    'width' : 728,
                    'params' : {}
                };
            `
            
            const script2 = document.createElement('script')
            script2.type = 'text/javascript'
            script2.src = '//www.highperformanceformat.com/d1214f3bf383ccc9a397125fddd1db47/invoke.js'
            script2.async = true
            
            // Add both scripts to the container
            this.$refs.adContainer.appendChild(script1)
            this.$refs.adContainer.appendChild(script2)
            
            console.log('Adsterra test ad loaded')
        }
    }
}
</script>

<style scoped>
.adsterra-test {
    max-width: 100%;
    overflow: hidden;
}

@media (max-width: 768px) {
    .adsterra-test {
        display: none;
    }
}
</style>
