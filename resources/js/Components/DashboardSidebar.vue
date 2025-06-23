<script setup>
    import { ref } from 'vue';
    import AppHeader from '@/Components/AppHeader.vue';
    import TherapistWidget from '@/Components/TherapistWidget.vue';
    import MessagesWidget from '@/Components/MessagesWidget.vue';

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
</script>

<template>
    <div class="w-50 p-6">
        <!-- Profile and Language at the top -->
        <AppHeader :user="user" />

        <!-- Therapists Widget Component -->
        <TherapistWidget :therapists="therapists" />

        <!-- Messages Widget Component -->
        <MessagesWidget :messages="messages" />
    </div>
</template>
