<script setup>
    import { ref, computed } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import AppHeader from '@/Components/AppHeader.vue';
    import TherapistWidget from '@/Components/TherapistWidget.vue';
    import MessagesWidget from '@/Components/MessagesWidget.vue';
    import DisplayAd from '@/Components/DisplayAd.vue';

    const props = defineProps({
        user: Object,
        therapists: {
            type: Array,
            default: () => [],
        },
        messages: {
            type: Array,
            default: () => [],
        },
    });

    const page = usePage();

    // Helper function to get user tier
    const getUserTier = () => {
        const user = props.user || page.props.auth?.user;
        if (!user) return 'free';

        // Check if user has an active subscription
        if (user.subscription_status === 'active' && user.subscription_plan) {
            // Check if subscription hasn't expired
            if (!user.subscription_expires_at || new Date(user.subscription_expires_at) > new Date()) {
                return user.subscription_plan.toLowerCase();
            }
        }

        return 'free';
    };
</script>

<template>
    <div class="w-50 p-6">
        <!-- Profile and Language at the top -->
        <AppHeader :user="user" />

        <!-- Therapists Widget Component -->
        <TherapistWidget :therapists="therapists" />

        <!-- Sidebar Display Ad -->
        <DisplayAd :userTier="getUserTier()" placement="sidebar" />

        <!-- Messages Widget Component -->
        <MessagesWidget :messages="messages" />
    </div>
</template>
