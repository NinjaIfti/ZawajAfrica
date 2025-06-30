<template>
    <div v-if="shouldShowAd" class="dashboard-feed-ad mb-6">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <!-- Ad Label -->
            <div class="text-xs text-gray-500 mb-2 text-center">Advertisement</div>
            
            <!-- AdSense Fluid Ad -->
            <ins 
                ref="adElement"
                class="adsbygoogle"
                style="display:block"
                data-ad-format="fluid"
                data-ad-layout-key="-6t+ef+2h-1o-5b"
                data-ad-client="ca-pub-8389265465942244"
                data-ad-slot="5106076698">
            </ins>
        </div>
    </div>
</template>

<script>
import { computed, onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default {
    name: 'DashboardFeedAd',
    props: {
        userTier: {
            type: String,
            default: 'free'
        },
        forceShow: {
            type: Boolean,
            default: false
        }
    },
    
    setup(props) {
        const page = usePage()
        const adLoaded = ref(false)
        
        const shouldShowAd = computed(() => {
            // Check global AdSense config first
            const adSenseConfig = page.props.adsense?.config
            if (!adSenseConfig?.enabled) {
                return false
            }
            
            // Don't show ads for premium users unless forced
            if (!props.forceShow && props.userTier !== 'free') {
                return false
            }
            
            // Additional check: ensure AdSense service allows showing ads on this page
            const showOnPage = page.props.adsense?.show_on_page
            if (!showOnPage) {
                return false
            }
            
            return true
        })
        
        const ensureAdSenseScript = () => {
            if (!document.querySelector('script[src*="adsbygoogle.js"]')) {
                const script = document.createElement('script')
                script.async = true
                script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8389265465942244'
                script.crossOrigin = 'anonymous'
                document.head.appendChild(script)
            }
        }

        const loadAd = () => {
            if (shouldShowAd.value && !adLoaded.value) {
                try {
                    // Ensure AdSense script is loaded
                    ensureAdSenseScript()
                    
                    // Wait for script to load, then push ad
                    const tryLoadAd = () => {
                        if (window.adsbygoogle) {
                            (window.adsbygoogle = window.adsbygoogle || []).push({})
                            adLoaded.value = true
                            console.log('Dashboard feed ad loaded')
                        } else {
                            // Retry after 500ms if script isn't ready
                            setTimeout(tryLoadAd, 500)
                        }
                    }
                    
                    setTimeout(tryLoadAd, 100)
                } catch (error) {
                    console.error('Error loading dashboard feed ad:', error)
                }
            }
        }
        
        onMounted(() => {
            if (shouldShowAd.value) {
                loadAd()
            }
        })
        
        return {
            shouldShowAd,
            loadAd
        }
    }
}
</script>

<style scoped>
.dashboard-feed-ad {
    /* Ensure proper responsive behavior */
    max-width: 100%;
    overflow: hidden;
}

.adsbygoogle {
    /* Ensure ads don't break layout */
    max-width: 100%;
    overflow: hidden;
}
</style> 