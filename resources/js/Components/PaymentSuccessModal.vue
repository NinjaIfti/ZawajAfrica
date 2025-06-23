<script setup>
    import Modal from './Modal.vue';
    import PrimaryButton from './PrimaryButton.vue';
    import { router } from '@inertiajs/vue3';

    const props = defineProps({
        show: {
            type: Boolean,
            default: false,
        },
        paymentType: {
            type: String,
            default: 'subscription', // 'subscription' or 'therapist_booking'
        },
    });

    const emit = defineEmits(['close']);

    const closeModal = () => {
        emit('close');
    };

    const findYourMatch = () => {
        emit('close');
        router.visit('/dashboard');
    };
</script>

<template>
    <Modal :show="show" @close="closeModal" maxWidth="md">
        <div class="p-8 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-purple-100 mb-6">
                <svg
                    class="h-8 w-8 text-white bg-purple-600 rounded-full p-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Success Title -->
            <h3 class="text-2xl font-semibold text-gray-900 mb-4">Payment Successful</h3>

            <!-- Success Message -->
            <p class="text-gray-600 mb-8">Thank you! Your payment has been successfully processed.</p>

            <!-- Action Button -->
            <div class="flex justify-center">
                <PrimaryButton
                    @click="findYourMatch"
                    class="bg-purple-600 hover:bg-purple-700 px-8 py-3 text-lg rounded-lg"
                >
                    Find Your Match
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
