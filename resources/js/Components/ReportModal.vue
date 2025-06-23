<script setup>
    import { ref } from 'vue';
    import { router } from '@inertiajs/vue3';
    import Modal from './Modal.vue';
    import SecondaryButton from './SecondaryButton.vue';
    import PrimaryButton from './PrimaryButton.vue';

    const props = defineProps({
        show: Boolean,
        userId: [Number, String],
        userName: String,
    });

    const emit = defineEmits(['close']);

    const isSubmitting = ref(false);
    const selectedReason = ref('');
    const otherReason = ref('');
    const isBlocked = ref(true);

    const reasons = [
        { id: 'harassment', label: 'Harassment' },
        { id: 'spam', label: 'Spam' },
        { id: 'inappropriate', label: 'Inappropriate Content' },
        { id: 'fake', label: 'Fake Profile' },
        { id: 'other', label: 'Other Reason' },
    ];

    const closeModal = () => {
        emit('close');
        // Reset form state
        selectedReason.value = '';
        otherReason.value = '';
        isBlocked.value = true;
        isSubmitting.value = false;
    };

    const submitReport = () => {
        if (!selectedReason.value) {
            return;
        }

        isSubmitting.value = true;

        // Format the reason
        const reason =
            selectedReason.value === 'other' && otherReason.value
                ? otherReason.value
                : reasons.find(r => r.id === selectedReason.value)?.label || selectedReason.value;

        // Submit to backend
        router.post(
            route('reports.store'),
            {
                reported_user_id: props.userId,
                reason: reason,
                details: selectedReason.value === 'other' ? otherReason.value : '',
                is_blocked: isBlocked.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeModal();
                    // Display success message
                    alert('Report submitted successfully');
                },
                onError: errors => {
                    console.error(errors);
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            }
        );
    };
</script>

<template>
    <Modal :show="show" @close="closeModal" maxWidth="md">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-medium text-gray-900">Block & Report</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="mb-6">
                <h3 class="font-medium mb-4">Select a Reason for Reporting</h3>

                <div class="space-y-3">
                    <label v-for="reason in reasons" :key="reason.id" class="flex items-center">
                        <input
                            type="radio"
                            :value="reason.id"
                            v-model="selectedReason"
                            class="h-5 w-5 text-purple-600"
                        />
                        <span class="ml-2">{{ reason.label }}</span>
                    </label>

                    <div v-if="selectedReason === 'other'" class="mt-3">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Other Reason</label>
                        <textarea
                            v-model="otherReason"
                            class="w-full px-3 py-2 border rounded-md"
                            rows="3"
                            placeholder="Add other reason"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" v-model="isBlocked" class="h-5 w-5 text-purple-600" />
                    <span class="ml-2">Block this user</span>
                </label>
            </div>

            <div class="mt-6 flex justify-end space-x-2">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton
                    :disabled="!selectedReason || isSubmitting || (selectedReason === 'other' && !otherReason)"
                    @click="submitReport"
                    :class="{ 'opacity-75 cursor-not-allowed': isSubmitting }"
                >
                    <span v-if="isSubmitting">Submitting...</span>
                    <span v-else>Submit Report</span>
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
