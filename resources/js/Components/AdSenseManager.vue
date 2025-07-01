<template>
    <div class="adsense-manager">
        <!-- GDPR Consent Banner -->
        <div v-if="showConsentBanner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50">
            <div class="container mx-auto flex justify-between items-center">
                <p class="text-sm">We use cookies for ads. Choose your preferences:</p>
                <div class="flex gap-2">
                    <button @click="acceptAll" class="bg-blue-600 px-4 py-2 rounded text-sm">Accept All</button>
                    <button @click="rejectAll" class="bg-red-600 px-4 py-2 rounded text-sm">Reject</button>
                </div>
            </div>
        </div>

        <!-- Ad Status Indicator (for testing) -->
        <div v-if="adSenseConfig.debug && adSenseConfig.enabled" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-xs z-40">
            AdSense Active ({{ adSenseConfig.test_mode ? 'Test' : 'Live' }} Mode)
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue'

export default {
    name: 'AdSenseManager',
    props: {
        adsenseConfig: {
            type: Object,
            default: () => ({})
        },
        showOnPage: {
            type: Boolean,
            default: true
        },
        consentData: {
            type: Object,
            default: () => ({})
        }
    },
    
    setup(props) {
        const adSenseConfig = ref(props.adsenseConfig)
        const showConsentBanner = ref(false)
        const adSenseLoaded = ref(false)
        
        // Computed property to check if consent banner should be shown
        const needsConsent = computed(() => {
            return adSenseConfig.value.enabled && 
                   adSenseConfig.value.privacy?.gdpr_enabled && 
                   !props.consentData.cookie_consent
        })

        // Initialize AdSense
        const initializeAdSense = () => {
            if (!adSenseConfig.value.enabled || !props.showOnPage) {
    
                return
            }

            if (needsConsent.value) {
                showConsentBanner.value = true
                return
            }

            loadAdSense()
        }

        // Load AdSense script
        const loadAdSense = () => {
            if (adSenseLoaded.value) return

            const publisherId = adSenseConfig.value.publisher_id
            if (!publisherId) {
                console.error('AdSense Publisher ID not configured')
                return
            }

            try {
                // Load AdSense script
                const script = document.createElement('script')
                script.async = true
                script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${publisherId}`
                script.crossOrigin = 'anonymous'
                script.onload = () => {

                    initializeAutoAds()
                }
                script.onerror = () => {
                    console.error('Failed to load AdSense script')
                }
                document.head.appendChild(script)

                adSenseLoaded.value = true
            } catch (error) {
                console.error('Error loading AdSense:', error)
            }
        }

        // Initialize Auto Ads
        const initializeAutoAds = () => {
            if (!adSenseConfig.value.auto_ads?.enabled) return

            try {
                const autoAdsConfig = {
                    google_ad_client: adSenseConfig.value.publisher_id,
                    enable_page_level_ads: adSenseConfig.value.auto_ads.page_level
                }

                // Add overlays configuration
                if (adSenseConfig.value.auto_ads.anchor_ads) {
                    autoAdsConfig.overlays = { bottom: true }
                }

                // Add vignette configuration  
                if (!adSenseConfig.value.auto_ads.vignette_ads) {
                    autoAdsConfig.vignette = { enable: false }
                }

                // Push configuration to AdSense
                ;(window.adsbygoogle = window.adsbygoogle || []).push(autoAdsConfig)
                

            } catch (error) {
                console.error('Error initializing Auto Ads:', error)
            }
        }

        // Accept all cookies
        const acceptAll = () => {
            showConsentBanner.value = false
            loadAdSense()
        }

        // Reject all cookies
        const rejectAll = () => {
            showConsentBanner.value = false
        }

        // Watch for config changes
        watch(() => props.adsenseConfig, (newConfig) => {
            adSenseConfig.value = newConfig
            if (newConfig.enabled && props.showOnPage) {
                initializeAdSense()
            }
        }, { immediate: true })

        onMounted(() => {
            initializeAdSense()
        })

        return {
            adSenseConfig,
            showConsentBanner,
            adSenseLoaded,
            needsConsent,
            acceptAll,
            rejectAll
        }
    }
}
</script>

<style scoped>
.adsense-manager {
    /* Ensure the component doesn't interfere with page layout */
    position: relative;
    z-index: 1;
}

/* Style for consent banner */
.fixed {
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style> 