<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import { ref } from 'vue';
</script>

<template>
    <Head title="Verification Completed" />

    <div class="flex min-h-screen items-center justify-center bg-white px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[600px] rounded-xl bg-white p-4 sm:p-6 lg:p-8 text-center">
            <!-- Purple Checkmark Icon -->
            <div class="mb-4 sm:mb-5 lg:mb-6 flex justify-center">
                <div
                    class="h-16 w-16 sm:h-18 sm:w-18 lg:h-20 lg:w-20 rounded-full bg-purple-100 flex items-center justify-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 sm:h-9 sm:w-9 lg:h-10 lg:w-10 text-purple-700"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
            </div>

            <h1 class="text-lg sm:text-xl lg:text-xl font-bold text-black mb-3 sm:mb-4 lg:mb-4">
                Your identification verification is completed.
            </h1>

            <p
                class="mx-auto max-w-md text-center text-gray-800 mb-6 sm:mb-7 lg:mb-8 text-sm sm:text-base lg:text-base px-2 sm:px-0"
            >
                Your identification verification is completed successfully. You can now start using all features of the
                app.
            </p>

            <Link
                :href="route('dashboard')"
                class="block w-full rounded-lg bg-purple-700 py-2.5 sm:py-3 lg:py-3 text-white transition duration-150 hover:bg-purple-800 text-sm sm:text-base lg:text-base"
            >
                Find your Match
            </Link>
        </div>
    </div>
</template>
