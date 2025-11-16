<template>
    <div v-if="shouldShowBanner && (isLoading || (!error && !isLoading))" class="dashboard-banner-ad my-4">
        <!-- Banner Ad Container -->
        <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 text-center relative">
            <div class="flex justify-between items-center mb-2">
                <div class="text-xs text-gray-500">Advertisement</div>
            </div>
            
            <!-- Loading overlay -->
            <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-gray-100 bg-opacity-90 rounded-lg">
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                    <span class="ml-2 text-sm text-gray-600">Loading ad...</span>
                </div>
            </div>
            
            <!-- Ad Container -->
            <div ref="bannerContainer" :id="bannerId" class="banner-ad-container min-h-[90px]">
                <!-- This will be populated by the ad script -->
            </div>
            
            <!-- Error state (if needed) -->
            <div v-if="error && !isLoading" class="py-4 text-center">
                <div class="text-red-500 text-sm">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ error }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default {
    name: 'DashboardBanner',
    props: {
        className: {
            type: String,
            default: ''
        },
        placement: {
            type: String,
            default: 'dashboard'
        }
    },
    setup(props) {
        const page = usePage()
        const bannerContainer = ref(null)
        const isLoading = ref(false)
        const error = ref(null)
        const bannerId = ref(`dashboard-banner-${Math.random().toString(36).substr(2, 9)}`)
        
        // Check if banner should show
        const shouldShowBanner = computed(() => {
            // Only show for free users
            const userTier = page.props.userTier || 'free'
            return userTier === 'free'
        })

        const loadBannerAd = () => {
            if (!shouldShowBanner.value) return
            
            isLoading.value = true
            error.value = null
            
            try {
                // Clear any existing content
                if (bannerContainer.value) {
                    bannerContainer.value.innerHTML = ''
                }
                
                // Create a unique container div for the ad
                const adDiv = document.createElement('div')
                adDiv.id = `ad-${bannerId.value}`
                adDiv.style.cssText = 'width: 100%; text-align: center; min-height: 90px;'
                
                // Add the ad div to container
                if (bannerContainer.value) {
                    bannerContainer.value.appendChild(adDiv)
                }
                
                // Create unique window property to avoid conflicts
                const uniqueKey = `atOptions_${bannerId.value}`
                window[uniqueKey] = {
                    'key': 'd1214f3bf383ccc9a397125fddd1db47',
                    'format': 'iframe',
                    'height': 90,
                    'width': 728,
                    'params': {}
                }
                
                // Set global atOptions for this specific ad
                window.atOptions = window[uniqueKey]
                
                // Create and load the invoke script
                const script = document.createElement('script')
                script.type = 'text/javascript'
                script.src = '//www.highperformanceformat.com/d1214f3bf383ccc9a397125fddd1db47/invoke.js'
                script.async = true
                script.id = `banner-script-${bannerId.value}`
                
                // Handle script load events
                script.onload = () => {
                    console.log('Banner script loaded successfully')
                    // Check for ad content after script loads
                    setTimeout(() => {
                        checkAdLoaded()
                    }, 1000)
                }
                
                script.onerror = () => {
                    console.error('Banner script failed to load')
                    isLoading.value = false
                    error.value = 'Failed to load ad script'
                }
                
                // Append script to head instead of container
                document.head.appendChild(script)
                
                // Set timeout to stop loading state
                setTimeout(() => {
                    if (isLoading.value) {
                        checkAdLoaded()
                    }
                }, 8000) // 8 second timeout
                
            } catch (err) {
                console.error('Banner ad loading error:', err)
                error.value = 'Failed to load advertisement'
                isLoading.value = false
            }
        }
        
        const checkAdLoaded = () => {
            if (!bannerContainer.value) {
                isLoading.value = false
                return
            }
            
            // Check for iframe or other ad content
            const hasIframe = bannerContainer.value.querySelector('iframe')
            const hasScript = bannerContainer.value.querySelector('script')
            const hasAdContent = bannerContainer.value.querySelector('[id*="ad"]') || 
                               bannerContainer.value.querySelector('[class*="ad"]') ||
                               bannerContainer.value.children.length > 1 // More than just our created div
            
            // Also check if the ad script has injected content into the body or document
            const bodyHasAdContent = document.body.querySelector('iframe[src*="highperformanceformat"]') ||
                                   document.body.querySelector('div[id*="d1214f3bf383ccc9a397125fddd1db47"]')
            
            console.log('DashboardBanner: Checking ad loaded:', {
                hasIframe: !!hasIframe,
                hasScript: !!hasScript,
                hasAdContent: !!hasAdContent,
                bodyHasAdContent: !!bodyHasAdContent,
                childrenCount: bannerContainer.value.children.length,
                innerHTML: bannerContainer.value.innerHTML.substring(0, 200)
            })
            
            if (hasIframe || hasScript || hasAdContent || bodyHasAdContent) {
                isLoading.value = false
                error.value = null
                console.log('DashboardBanner: Ad content detected - showing banner')
            } else {
                isLoading.value = false
                console.log('DashboardBanner: No ad content detected - hiding banner')
                // Don't show error, just hide the component
                // error.value = 'Ad content not available'
            }
        }

        onMounted(() => {
            if (shouldShowBanner.value) {
                // Small delay to ensure DOM is ready
                setTimeout(loadBannerAd, 100)
            }
        })

        onUnmounted(() => {
            // Clean up any created elements
            if (bannerContainer.value) {
                bannerContainer.value.innerHTML = ''
            }
        })

        return {
            bannerContainer,
            isLoading,
            error,
            bannerId,
            shouldShowBanner,
            checkAdLoaded
        }
    }
}
</script>

<style scoped>
.dashboard-banner-ad {
    max-width: 100%;
    overflow: hidden;
}

.banner-ad-container {
    width: 100%;
    max-width: 728px;
    margin: 0 auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .banner-ad-container {
        max-width: 320px;
        min-height: 50px;
    }
}

/* Ensure iframe fits properly */
.banner-ad-container iframe {
    max-width: 100%;
    height: auto;
}
</style>
