<script setup>
    import { Head, Link, router, useForm } from '@inertiajs/vue3';
    import { ref, watch, computed } from 'vue';

    const props = defineProps({
        documentType: {
            type: String,
            default: 'passport',
        },
    });

    const documentTitles = {
        national_id: 'National ID Card',
        passport: 'Passport',
        drivers_license: "Driver's License",
        voters_register: 'Voters Register',
    };

    const documentTitle = computed(() => {
        return documentTitles[props.documentType] || 'Document';
    });

    const form = useForm({
        document_type: props.documentType,
        front_image: null,
        back_image: null,
    });

    const frontImageUrl = ref(null);
    const backImageUrl = ref(null);
    const showFrontPreview = ref(false);
    const showBackPreview = ref(false);

    // Check if the form can be submitted
    const canSubmit = computed(() => {
        // Always require front image
        if (!form.front_image) return false;

        // For all documents except voters_register, require back image too
        if (props.documentType !== 'voters_register' && !form.back_image) return false;

        return true;
    });

    const handleFrontImageUpload = e => {
        const file = e.target.files[0];
        if (!file) return;

        form.front_image = file;
        frontImageUrl.value = URL.createObjectURL(file);
        showFrontPreview.value = true;
    };

    const handleBackImageUpload = e => {
        const file = e.target.files[0];
        if (!file) return;

        form.back_image = file;
        backImageUrl.value = URL.createObjectURL(file);
        showBackPreview.value = true;
    };

    const clearFrontImage = () => {
        form.front_image = null;
        frontImageUrl.value = null;
        showFrontPreview.value = false;
    };

    const clearBackImage = () => {
        form.back_image = null;
        backImageUrl.value = null;
        showBackPreview.value = false;
    };

    const submitForm = () => {
        form.post(route('verification.store'), {
            onSuccess: () => {
                router.visit(route('verification.complete'));
            },
        });
    };

    const close = () => {
        router.visit(route('verification.document-type'));
    };
</script>

<template>
    <Head :title="`Upload ${documentTitle}`" />

    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-4 sm:p-6 lg:p-8 shadow-md">
            <div class="mb-8 sm:mb-10 lg:mb-12 flex items-center justify-between">
                <div class="flex flex-col">
                    <h1 class="text-lg sm:text-xl lg:text-xl font-bold text-black">Upload {{ documentTitle }}</h1>
                    <p class="text-xs sm:text-sm lg:text-sm text-gray-600">
                        Upload both sides of your {{ documentTitle.toLowerCase() }}
                    </p>
                </div>
                <button @click="close" class="rounded p-1 text-gray-600 hover:bg-gray-100">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 sm:h-5 sm:w-5 lg:h-5 lg:w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitForm">
                <div class="space-y-4">
                    <!-- Front Image -->
                    <div class="mb-6 sm:mb-7 lg:mb-8">
                        <label class="mb-2 block text-sm sm:text-base lg:text-base font-medium text-gray-900">
                            Upload Front Picture
                            <span class="text-red-600">*</span>
                        </label>
                        <div
                            class="flex items-center justify-center w-full h-[200px] sm:h-[240px] lg:h-[280px] border border-purple-600 rounded-xl overflow-hidden"
                            :class="{ 'bg-gray-50': !showFrontPreview }"
                        >
                            <div v-if="!showFrontPreview" class="text-center">
                                <input
                                    type="file"
                                    id="front-image"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleFrontImageUpload"
                                />
                                <label
                                    for="front-image"
                                    class="flex flex-col items-center justify-center cursor-pointer p-4 sm:p-5 lg:p-6"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-12 w-12 sm:h-14 sm:w-14 lg:h-16 lg:w-16 text-purple-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <p class="mt-2 text-xs sm:text-sm lg:text-sm text-gray-600 text-center px-2">
                                        Click to upload the front of your {{ documentTitle.toLowerCase() }}
                                    </p>
                                </label>
                            </div>
                            <div v-else class="relative w-full h-full">
                                <img :src="frontImageUrl" class="w-full h-full object-contain" alt="Front document" />
                                <button
                                    type="button"
                                    @click="clearFrontImage"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 sm:h-5 sm:w-5 lg:h-5 lg:w-5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="form.errors.front_image" class="mt-1 text-xs sm:text-sm lg:text-sm text-red-600">
                            {{ form.errors.front_image }}
                        </div>
                    </div>

                    <!-- Back Image -->
                    <div class="mb-6 sm:mb-7 lg:mb-8" v-if="props.documentType !== 'voters_register'">
                        <label class="mb-2 block text-sm sm:text-base lg:text-base font-medium text-gray-900">
                            Upload Back Picture
                            <span class="text-red-600">*</span>
                        </label>
                        <div
                            class="flex items-center justify-center w-full h-[200px] sm:h-[240px] lg:h-[280px] border border-purple-600 rounded-xl overflow-hidden"
                            :class="{ 'bg-gray-50': !showBackPreview }"
                        >
                            <div v-if="!showBackPreview" class="text-center">
                                <input
                                    type="file"
                                    id="back-image"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleBackImageUpload"
                                />
                                <label
                                    for="back-image"
                                    class="flex flex-col items-center justify-center cursor-pointer p-4 sm:p-5 lg:p-6"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-12 w-12 sm:h-14 sm:w-14 lg:h-16 lg:w-16 text-purple-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <p class="mt-2 text-xs sm:text-sm lg:text-sm text-gray-600 text-center px-2">
                                        Click to upload the back of your {{ documentTitle.toLowerCase() }}
                                    </p>
                                </label>
                            </div>
                            <div v-else class="relative w-full h-full">
                                <img :src="backImageUrl" class="w-full h-full object-contain" alt="Back document" />
                                <button
                                    type="button"
                                    @click="clearBackImage"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 sm:h-5 sm:w-5 lg:h-5 lg:w-5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="form.errors.back_image" class="mt-1 text-xs sm:text-sm lg:text-sm text-red-600">
                            {{ form.errors.back_image }}
                        </div>
                    </div>

                    <p class="text-center text-xs sm:text-sm lg:text-sm text-red-600 px-2 sm:px-0">
                        Make sure your document details are clear to read with no blur or glare
                    </p>

                    <div class="mt-6 sm:mt-7 lg:mt-8">
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-purple-700 py-2.5 sm:py-3 lg:py-3 text-white transition duration-150 hover:bg-purple-800 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base lg:text-base"
                            :disabled="form.processing || !canSubmit"
                        >
                            Submit for Verification
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
