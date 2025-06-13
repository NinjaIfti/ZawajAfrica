<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Sample FAQs
const generalFaqs = ref([
    {
        id: 1,
        question: 'What is ZawajAfrica?',
        answer: 'ZawajAfrica is the first halal matchmaking app designed for people of color, combining cultural sensitivity, exclusivity, and modern features to help you find meaningful connections.'
    },
    {
        id: 2,
        question: 'Who can join ZawajAfrica?',
        answer: 'ZawajAfrica is open to men, women, and elders who are serious about finding meaningful, halal relationships. Some features are tailored for specific user groups, such as privacy add-ons for young women and sponsor options for elder women.'
    }
]);

const subscriptionFaqs = ref([
    {
        id: 1,
        question: 'What are the subscription plans available?',
        answer: 'We offer monthly, quarterly, and yearly subscription plans with discounts for longer commitments. Plans are tailored for men, elder women, and young women (privacy add-ons).'
    },
    {
        id: 2,
        question: 'How can I pay for a subscription?',
        answer: 'You can pay with any acceptable credit card.'
    }
]);
</script>

<template>
    <Head title="My FAQs" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Profile Header Component -->
                <ProfileHeader :user="user" activeTab="faqs" />

                <!-- FAQs Content -->
                <div>
                    <h2 class="text-2xl font-bold mb-2">FAQs</h2>
                    
                    <!-- General Questions Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-6">General Questions</h3>
                        
                        <div class="space-y-6">
                            <div v-for="faq in generalFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium">Q: {{ faq.question }}</div>
                                <div class="text-gray-600">A: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Subscription and Payment Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-xl font-semibold mb-6">Subscription and Payment</h3>
                        
                        <div class="space-y-6">
                            <div v-for="faq in subscriptionFaqs" :key="faq.id" class="space-y-2">
                                <div class="font-medium">Q: {{ faq.question }}</div>
                                <div class="text-gray-600">A: {{ faq.answer }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style> 