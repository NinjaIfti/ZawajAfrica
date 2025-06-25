# 🚀 Google AdSense Integration - ZawajAfrica

## 📋 Overview

Successfully integrated **Google AdSense Auto Ads** for ZawajAfrica dating platform with tier-based ad display:
- ✅ **Free users** see auto ads
- ✅ **Paid users** (Basic, Gold, Platinum) see no ads
- ✅ GDPR compliance with consent management
- ✅ Real-time tier validation
- ✅ Analytics and impression tracking

---

## 🏗️ Architecture

### **Backend Services**
- **`AdSenseService`** - Core ad management logic
- **`UserTierService`** - Existing tier validation (enhanced)
- **`HandleInertiaRequests`** - Middleware integration

### **Frontend Components**
- **`AdSenseManager.vue`** - Auto ads initialization & GDPR
- **`AdSenseNotice.vue`** - Upgrade prompts for free users

### **Configuration**
- **`config/adsense.php`** - Centralized settings
- **`.env`** - Environment-specific variables

---

## ⚡ Quick Setup

### **1. Environment Configuration**

Add to your `.env` file:

```env
# Google AdSense Configuration
ADSENSE_ENABLED=true
ADSENSE_TEST_MODE=true
ADSENSE_PUBLISHER_ID=ca-pub-YOUR-ACTUAL-ID
ADSENSE_TEST_PUBLISHER_ID=ca-pub-3940256099942544

# Auto Ads Settings
ADSENSE_AUTO_ADS_ENABLED=true
ADSENSE_PAGE_LEVEL_ADS=true
ADSENSE_ANCHOR_ADS=true
ADSENSE_VIGNETTE_ADS=false

# Targeting
ADSENSE_ALLOWED_COUNTRIES=NG,US,GB,CA,AU
ADSENSE_PROFILE_FREQUENCY=10

# Privacy & GDPR
ADSENSE_GDPR_ENABLED=true
ADSENSE_COOKIE_CONSENT_REQUIRED=true
```

### **2. Get Your AdSense Publisher ID**

From your client, you need:
```
1. AdSense Publisher ID (ca-pub-XXXXXXXXXX)
2. AdSense account approval status
3. Preferred countries for ad targeting
4. GDPR compliance requirements
```

### **3. Update Environment Variables**

**Development:**
```env
ADSENSE_TEST_MODE=true
ADSENSE_DEBUG_MODE=true
```

**Production:**
```env
ADSENSE_TEST_MODE=false
ADSENSE_DEBUG_MODE=false
ADSENSE_PUBLISHER_ID=ca-pub-YOUR-REAL-ID
```

### **4. Run Migration**

```bash
php artisan migrate
```

---

## 🎯 How It Works

### **Tier-Based Ad Display**

```php
// Free users see ads
if ($userTier === 'free') {
    // Auto ads load automatically
    // Ads shown every 10 profile views
}

// Paid users see no ads
if ($userTier !== 'free') {
    // AdSense disabled
    // Clean experience
}
```

### **Auto Ads Configuration**

```javascript
{
  google_ad_client: "ca-pub-YOUR-ID",
  enable_page_level_ads: true,
  overlays: { bottom: true },  // Anchor ads
  vignette: { enable: false }  // Full-page ads
}
```

### **GDPR Compliance**

- Consent banner for EU users
- Cookie preferences management
- Personalized vs non-personalized ads
- Data processing consent

---

## 🛠️ Key Features

### **1. Smart Ad Display**
- Only free users see ads
- Respects page restrictions (no ads on payment/admin pages)
- Country-based targeting
- Mobile-responsive

### **2. User Experience**
- Non-intrusive consent management
- Upgrade prompts with context-aware messaging
- Smooth ad loading with lazy loading
- Debug mode for development

### **3. Analytics & Tracking**
- Ad impression logging
- User tier analysis
- Daily activity tracking
- Revenue analytics ready

### **4. Performance Optimized**
- Async script loading
- Conditional rendering
- Cached configurations
- Error handling

---

## 📊 API Endpoints

### **AdSense Configuration**
```
GET /api/adsense/config
```
Returns current user's ad configuration

### **Consent Management**
```
POST /api/adsense/consent
{
  "consent": true,
  "personalized_ads": true,
  "data_processing": true
}
```

### **Impression Tracking**
```
POST /api/adsense/impression
{
  "ad_type": "auto",
  "metadata": {}
}
```

---

## 🧪 Testing

### **Test with Google's Test Ads**

```env
ADSENSE_TEST_MODE=true
ADSENSE_PUBLISHER_ID=ca-pub-3940256099942544
```

### **Debug Mode**

```env
ADSENSE_DEBUG_MODE=true
```
Shows green indicator when ads are active.

### **Test Different User Tiers**

1. **Free User**: Should see ads + upgrade prompts
2. **Basic User**: No ads, clean experience  
3. **Gold/Platinum**: No ads, premium features

---

## 🔧 Customization

### **Change Ad Frequency**
```env
ADSENSE_PROFILE_FREQUENCY=5  # Show ads every 5 profiles instead of 10
```

### **Disable Specific Ad Types**
```env
ADSENSE_ANCHOR_ADS=false      # No bottom anchor ads
ADSENSE_VIGNETTE_ADS=false    # No full-page ads
```

### **Add More Restricted Pages**
```php
// config/adsense.php
'restricted_pages' => [
    'payment', 'subscription', 'verification', 'admin', 'checkout'
],
```

---

## 🎨 Frontend Integration

### **Layout Integration**
AdSense components are automatically included in `AppLayout.vue`:

```vue
<AdSenseManager 
    :adsense-config="$page.props.adsense.config"
    :show-on-page="$page.props.adsense.show_on_page"
    :consent-data="$page.props.adsense.consent"
/>

<AdSenseNotice 
    :user-tier="getUserTier()"
    :current-page="getCurrentPageName()"
/>
```

### **Custom Implementation**
For specific pages, you can manually add:

```vue
<script>
// Check if ads should show
const shouldShowAds = computed(() => {
    return $page.props.adsense.config.enabled && 
           $page.props.adsense.show_on_page
})
</script>
```

---

## 📈 Revenue Optimization

### **Upgrade Conversion**
- Context-aware upgrade prompts
- Strategic ad placement timing
- Premium feature highlighting

### **Ad Performance**
- Impression tracking
- User engagement metrics
- A/B testing ready

---

## 🚨 Important Notes

### **Before Going Live**

1. ✅ Replace test Publisher ID with real one
2. ✅ Set `ADSENSE_TEST_MODE=false`
3. ✅ Verify AdSense account approval
4. ✅ Test on staging environment
5. ✅ Update privacy policy for ad usage

### **Privacy Compliance**

- Privacy policy must mention AdSense data usage
- GDPR consent for EU users
- Cookie policy updates required

### **Performance Monitoring**

- Monitor ad loading times
- Track user experience impact
- Watch for ad blocker usage

---

## 🔍 Troubleshooting

### **Ads Not Showing**

1. Check `ADSENSE_ENABLED=true`
2. Verify Publisher ID format
3. Ensure user is free tier
4. Check browser console for errors
5. Verify page not in restricted list

### **GDPR Banner Not Appearing**

1. Check `ADSENSE_GDPR_ENABLED=true`
2. Verify user location detection
3. Clear browser cookies
4. Check consent cookie values

### **Console Errors**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check AdSense script loading
# Browser Developer Tools > Network tab
```

---

## 🚀 Next Steps

1. **Get real AdSense Publisher ID from client**
2. **Test with real ads in staging**
3. **Monitor performance and user feedback**
4. **Optimize ad placement based on analytics**
5. **A/B test different upgrade prompts**

---

## 📞 Support

For any issues or questions about the AdSense integration:

1. Check this README first
2. Review Laravel logs for errors
3. Test with Google's test Publisher ID
4. Verify environment configuration

**Integration Status: ✅ Complete and Ready for Production** 