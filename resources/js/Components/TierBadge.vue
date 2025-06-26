<script setup>
const props = defineProps({
    tier: {
        type: String,
        default: 'free',
        validator: value => ['free', 'basic', 'gold', 'platinum'].includes(value)
    },
    size: {
        type: String,
        default: 'sm',
        validator: value => ['xs', 'sm', 'md', 'lg'].includes(value)
    },
    showLabel: {
        type: Boolean,
        default: false
    }
});

// Tier configurations with icons and styling
const tierConfig = {
    free: {
        name: 'Free',
        icon: '', // No icon for free users
        bgColor: 'bg-gray-100',
        textColor: 'text-gray-600',
        borderColor: 'border-gray-200'
    },
    basic: {
        name: 'Basic',
        icon: '🥉', // Bronze medal emoji
        bgColor: 'bg-amber-50',
        textColor: 'text-amber-700',
        borderColor: 'border-amber-200'
    },
    gold: {
        name: 'Gold',
        icon: '🥇', // Gold medal emoji
        bgColor: 'bg-yellow-50',
        textColor: 'text-yellow-700',
        borderColor: 'border-yellow-200'
    },
    platinum: {
        name: 'Platinum',
        icon: '💎👑', // Diamond and crown emojis
        bgColor: 'bg-purple-50',
        textColor: 'text-purple-700',
        borderColor: 'border-purple-200'
    }
};

// Size configurations
const sizeConfig = {
    xs: {
        padding: 'px-1.5 py-0.5',
        textSize: 'text-xs',
        iconSize: 'text-xs'
    },
    sm: {
        padding: 'px-2 py-1',
        textSize: 'text-xs',
        iconSize: 'text-sm'
    },
    md: {
        padding: 'px-3 py-1.5',
        textSize: 'text-sm',
        iconSize: 'text-base'
    },
    lg: {
        padding: 'px-4 py-2',
        textSize: 'text-base',
        iconSize: 'text-lg'
    }
};

const currentTier = tierConfig[props.tier] || tierConfig.free;
const currentSize = sizeConfig[props.size] || sizeConfig.sm;
</script>

<template>
    <!-- Only render if not free tier or if explicitly showing free tier -->
    <span 
        v-if="tier !== 'free' || showLabel"
        :class="[
            'inline-flex items-center gap-1 rounded-full border font-medium',
            currentTier.bgColor,
            currentTier.textColor,
            currentTier.borderColor,
            currentSize.padding,
            currentSize.textSize
        ]"
    >
        <!-- Tier icon -->
        <span 
            v-if="currentTier.icon"
            :class="currentSize.iconSize"
            class="flex-shrink-0"
        >
            {{ currentTier.icon }}
        </span>
        
        <!-- Tier name (optional) -->
        <span v-if="showLabel" class="font-semibold">
            {{ currentTier.name }}
        </span>
    </span>
</template> 