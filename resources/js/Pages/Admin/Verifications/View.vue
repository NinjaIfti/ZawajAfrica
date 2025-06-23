<script setup>
    import { Head, Link, router } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps({
        user: Object,
        verification: Object,
    });

    const rejectionReason = ref('');
    const showRejectionModal = ref(false);

    const documentTypes = {
        national_id: 'National ID',
        passport: 'Passport',
        drivers_license: "Driver's License",
        voters_register: 'Voters Register',
    };

    const formatDate = dateString => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const approveVerification = () => {
        router.post(
            route('admin.verifications.approve', { userId: props.user.id }),
            {},
            {
                onSuccess: () => {
                    // Success notification could be added here
                },
            }
        );
    };

    const openRejectionModal = () => {
        showRejectionModal.value = true;
    };

    const closeRejectionModal = () => {
        showRejectionModal.value = false;
        rejectionReason.value = '';
    };

    const rejectVerification = () => {
        if (!rejectionReason.value) {
            alert('Please provide a reason for rejection');
            return;
        }

        router.post(
            route('admin.verifications.reject', { userId: props.user.id }),
            {
                reason: rejectionReason.value,
            },
            {
                onSuccess: () => {
                    closeRejectionModal();
                    // Success notification could be added here
                },
            }
        );
    };
</script>

<template>
    <Head title="Verification Details" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Verification Details</h2>
                <Link
                    :href="route('admin.verifications')"
                    class="rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- User Information -->
                        <div class="mb-8 border-b border-gray-200 pb-6">
                            <h3 class="mb-4 text-lg font-medium">User Information</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <p class="text-sm text-gray-500">Name</p>
                                    <p class="font-medium">{{ props.user.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium">{{ props.user.email }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Gender</p>
                                    <p class="font-medium">{{ props.user.gender || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Registered On</p>
                                    <p class="font-medium">{{ formatDate(props.user.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Information -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-medium">Verification Information</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <p class="text-sm text-gray-500">Document Type</p>
                                    <p class="font-medium">
                                        {{
                                            documentTypes[props.verification.document_type] ||
                                            props.verification.document_type
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    <p class="font-medium">
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="{
                                                'bg-amber-100 text-amber-800': props.verification.status === 'pending',
                                                'bg-green-100 text-green-800': props.verification.status === 'approved',
                                                'bg-red-100 text-red-800': props.verification.status === 'rejected',
                                            }"
                                        >
                                            {{ props.verification.status }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Submitted On</p>
                                    <p class="font-medium">{{ formatDate(props.verification.created_at) }}</p>
                                </div>
                                <div v-if="props.verification.verified_at">
                                    <p class="text-sm text-gray-500">Verified On</p>
                                    <p class="font-medium">{{ formatDate(props.verification.verified_at) }}</p>
                                </div>
                                <div v-if="props.verification.rejection_reason" class="col-span-2">
                                    <p class="text-sm text-gray-500">Rejection Reason</p>
                                    <p class="font-medium text-red-600">{{ props.verification.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Document Images -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-medium">Document Images</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Front Image -->
                                <div>
                                    <p class="mb-2 text-sm font-medium">Front Side</p>
                                    <div class="overflow-hidden rounded-lg border border-gray-200">
                                        <img
                                            :src="props.verification.front_image"
                                            alt="Front Document"
                                            class="w-full object-contain"
                                            style="max-height: 400px"
                                        />
                                    </div>
                                </div>

                                <!-- Back Image -->
                                <div v-if="props.verification.back_image">
                                    <p class="mb-2 text-sm font-medium">Back Side</p>
                                    <div class="overflow-hidden rounded-lg border border-gray-200">
                                        <img
                                            :src="props.verification.back_image"
                                            alt="Back Document"
                                            class="w-full object-contain"
                                            style="max-height: 400px"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div v-if="props.verification.status === 'pending'" class="flex space-x-4">
                            <button
                                @click="approveVerification"
                                class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                Approve Verification
                            </button>
                            <button
                                @click="openRejectionModal"
                                class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            >
                                Reject Verification
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div v-if="showRejectionModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <!-- Modal panel -->
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                            >
                                <svg
                                    class="h-6 w-6 text-red-600"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Reject Verification</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Please provide a reason for rejecting this verification. This will be shown to
                                        the user.
                                    </p>
                                    <textarea
                                        v-model="rejectionReason"
                                        rows="4"
                                        class="mt-2 block w-full rounded-md border border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                        placeholder="Enter rejection reason..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button
                            @click="rejectVerification"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Reject
                        </button>
                        <button
                            @click="closeRejectionModal"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
