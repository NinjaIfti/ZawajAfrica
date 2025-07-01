<template>
    <div v-if="shouldShowAd" class="display-ad mb-4">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <!-- Ad Label -->
            <div class="text-xs text-gray-500 mb-2 text-center">Advertisement</div>
            
            <!-- AdSense Display Ad -->
            <ins 
                ref="adElement"
                class="adsbygoogle"
                style="display:block"
                data-ad-client="ca-pub-8389265465942244"
                data-ad-slot="8367483099"
                data-ad-format="auto"
                data-full-width-responsive="true">
            </ins>
        </div>
    </div>
</template>

<script>
import { computed, onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default {
    name: 'DisplayAd',
    props: {
        userTier: {
            type: String,
            default: 'free'
        },
        forceShow: {
            type: Boolean,
            default: false
        },
        placement: {
            type: String,
            default: 'general' // 'sidebar', 'content', 'footer', etc.
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
                
                        } else {
                            // Retry after 500ms if script isn't ready
                            setTimeout(tryLoadAd, 500)
                        }
                    }
                    
                    setTimeout(tryLoadAd, 100)
                } catch (error) {
                    console.error('Error loading display ad:', error)
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
.display-ad {
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