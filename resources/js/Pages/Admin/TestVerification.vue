<script setup>
    import { Head } from '@inertiajs/vue3';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
    <Head title="Test Verification" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Test Verification Page</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Test Content</h3>
                        <p>This is a test page to diagnose rendering issues.</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
