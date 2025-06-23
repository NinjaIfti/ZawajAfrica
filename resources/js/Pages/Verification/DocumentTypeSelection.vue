<script setup>
    import { Head, Link, router } from '@inertiajs/vue3';
    import { ref } from 'vue';

    const selectedDocumentType = ref(null);

    const selectDocumentType = type => {
        selectedDocumentType.value = type;
    };

    const continueToUpload = () => {
        if (selectedDocumentType.value) {
            router.visit(route('verification.document-upload', { type: selectedDocumentType.value }));
        }
    };

    const close = () => {
        router.visit(route('dashboard'));
    };
</script>

<template>
    <Head title="Select Verification Document" />

    <div class="flex min-h-screen items-center justify-center bg-white px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-4 sm:p-6 lg:p-8">
            <div class="flex items-center justify-between mb-6 sm:mb-7 lg:mb-8">
                <h1 class="text-lg sm:text-xl lg:text-xl font-bold text-black">Select your verification process</h1>
                <button @click="close" class="text-black hover:text-gray-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 sm:h-6 sm:w-6 lg:h-6 lg:w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-3 sm:gap-4 lg:gap-4">
                <!-- National ID Card -->
                <div
                    class="flex flex-col items-center justify-center border border-gray-100 p-4 sm:p-5 lg:p-6 rounded-lg cursor-pointer transition-all duration-200 hover:border-purple-600"
                    :class="{ 'border-purple-600 border-2': selectedDocumentType === 'national_id' }"
                    @click="selectDocumentType('national_id')"
                >
                    <div
                        class="mb-3 sm:mb-4 lg:mb-4 h-[60px] w-[60px] sm:h-[65px] sm:w-[65px] lg:h-[70px] lg:w-[70px] flex items-center justify-center bg-gray-50 rounded-lg"
                    >
                        <svg
                            class="w-8 h-8 sm:w-9 sm:h-9 lg:w-10 lg:h-10"
                            viewBox="0 0 40 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <rect x="8" y="10" width="24" height="20" rx="2" stroke="#6D28D9" stroke-width="2" />
                            <circle cx="16" cy="18" r="3" stroke="#6D28D9" stroke-width="2" />
                            <path d="M22 15H28" stroke="#6D28D9" stroke-width="2" stroke-linecap="round" />
                            <path d="M22 20H28" stroke="#6D28D9" stroke-width="2" stroke-linecap="round" />
                            <path d="M12 25H28" stroke="#6D28D9" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base lg:text-base font-medium text-center">National ID card</h3>
                </div>

                <!-- Passport -->
                <div
                    class="flex flex-col items-center justify-center border border-gray-100 p-4 sm:p-5 lg:p-6 rounded-lg cursor-pointer transition-all duration-200 hover:border-purple-600"
                    :class="{ 'border-purple-600 border-2': selectedDocumentType === 'passport' }"
                    @click="selectDocumentType('passport')"
                >
                    <div
                        class="mb-3 sm:mb-4 lg:mb-4 h-[60px] w-[60px] sm:h-[65px] sm:w-[65px] lg:h-[70px] lg:w-[70px] flex items-center justify-center bg-gray-50 rounded-lg"
                    >
                        <svg
                            class="w-8 h-8 sm:w-9 sm:h-9 lg:w-10 lg:h-10"
                            viewBox="0 0 40 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <rect x="10" y="8" width="20" height="24" rx="2" stroke="#6D28D9" stroke-width="2" />
                            <circle cx="20" cy="16" r="4" stroke="#6D28D9" stroke-width="2" />
                            <path d="M15 26H25" stroke="#6D28D9" stroke-width="2" stroke-linecap="round" />
                            <circle cx="20" cy="16" r="2" stroke="#6D28D9" stroke-width="2" />
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base lg:text-base font-medium text-center">Passport</h3>
                </div>

                <!-- Driver's License -->
                <div
                    class="flex flex-col items-center justify-center border border-gray-100 p-4 sm:p-5 lg:p-6 rounded-lg cursor-pointer transition-all duration-200 hover:border-purple-600"
                    :class="{ 'border-purple-600 border-2': selectedDocumentType === 'drivers_license' }"
                    @click="selectDocumentType('drivers_license')"
                >
                    <div
                        class="mb-3 sm:mb-4 lg:mb-4 h-[60px] w-[60px] sm:h-[65px] sm:w-[65px] lg:h-[70px] lg:w-[70px] flex items-center justify-center bg-gray-50 rounded-lg"
                    >
                        <svg
                            class="w-8 h-8 sm:w-9 sm:h-9 lg:w-10 lg:h-10"
                            viewBox="0 0 40 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <rect x="8" y="12" width="24" height="16" rx="2" stroke="#6D28D9" stroke-width="2" />
                            <path d="M12 20H16" stroke="#6D28D9" stroke-width="2" stroke-linecap="round" />
                            <path d="M20 20H28" stroke="#6D28D9" stroke-width="2" stroke-linecap="round" />
                            <path d="M8 16H32" stroke="#6D28D9" stroke-width="2" stroke-linecap="round" />
                            <circle cx="16" cy="24" r="2" stroke="#6D28D9" stroke-width="2" />
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base lg:text-base font-medium text-center">Driver's License</h3>
                </div>

                <!-- Voters Register -->
                <div
                    class="flex flex-col items-center justify-center border border-gray-100 p-4 sm:p-5 lg:p-6 rounded-lg cursor-pointer transition-all duration-200 hover:border-purple-600"
                    :class="{ 'border-purple-600 border-2': selectedDocumentType === 'voters_register' }"
                    @click="selectDocumentType('voters_register')"
                >
                    <div
                        class="mb-3 sm:mb-4 lg:mb-4 h-[60px] w-[60px] sm:h-[65px] sm:w-[65px] lg:h-[70px] lg:w-[70px] flex items-center justify-center bg-gray-50 rounded-lg"
                    >
                        <svg
                            class="w-8 h-8 sm:w-9 sm:h-9 lg:w-10 lg:h-10"
                            viewBox="0 0 40 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <rect x="8" y="14" width="24" height="16" rx="2" stroke="#6D28D9" stroke-width="2" />
                            <path d="M8 20H32" stroke="#6D28D9" stroke-width="2" />
                            <path
                                d="M20 14L24 10L28 14"
                                stroke="#6D28D9"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base lg:text-base font-medium text-center">Voters Register</h3>
                </div>
            </div>

            <div class="mt-6 sm:mt-7 lg:mt-8">
                <button
                    @click="continueToUpload"
                    class="w-full rounded-lg bg-purple-700 py-2.5 sm:py-3 lg:py-3 text-white transition duration-150 hover:bg-purple-800 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base lg:text-base"
                    :disabled="!selectedDocumentType"
                >
                    Continue
                </button>
            </div>
        </div>
    </div>
</template>
