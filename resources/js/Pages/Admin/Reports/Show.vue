<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    report: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
};

const markAsReviewed = () => {
    router.put(route('admin.reports.update', props.report.id), {
        reviewed: true,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Report Details" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Report Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('admin.reports.index')" class="text-indigo-600 hover:text-indigo-900">
                        &larr; Back to Reports
                    </Link>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold mb-4">Report #{{ report.id }}</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="border rounded-lg p-4">
                                    <h4 class="text-lg font-semibold mb-2">Report Information</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-gray-600 font-medium">Date Submitted:</span>
                                            <span class="ml-2">{{ formatDate(report.created_at) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 font-medium">Reason:</span>
                                            <span class="ml-2">{{ report.reason }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 font-medium">Details:</span>
                                            <p class="mt-1">{{ report.details || 'No details provided' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 font-medium">Block Status:</span>
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                  :class="report.is_blocked ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'">
                                                {{ report.is_blocked ? 'User Blocked' : 'Not Blocked' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 font-medium">Review Status:</span>
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                  :class="report.reviewed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                                {{ report.reviewed ? 'Reviewed' : 'Pending Review' }}
                                            </span>
                                        </div>
                                        <div v-if="report.reviewed">
                                            <span class="text-gray-600 font-medium">Reviewed At:</span>
                                            <span class="ml-2">{{ formatDate(report.reviewed_at) }}</span>
                                        </div>
                                        <div v-if="report.reviewed && report.reviewer">
                                            <span class="text-gray-600 font-medium">Reviewed By:</span>
                                            <span class="ml-2">{{ report.reviewer.name }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border rounded-lg p-4">
                                    <h4 class="text-lg font-semibold mb-2">User Information</h4>
                                    <div class="space-y-4">
                                        <div v-if="report.reporter">
                                            <h5 class="font-medium text-gray-700">Reporter:</h5>
                                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ report.reporter.name }}</div>
                                                        <div class="text-sm text-gray-500">ID: {{ report.reporter.id }}</div>
                                                        <div class="text-sm text-gray-500">Email: {{ report.reporter.email }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <h5 class="font-medium text-gray-700">Reporter:</h5>
                                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                                <div class="text-sm text-gray-500">[Deleted User]</div>
                                            </div>
                                        </div>
                                        
                                        <div v-if="report.reportedUser">
                                            <h5 class="font-medium text-gray-700">Reported User:</h5>
                                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ report.reportedUser.name }}</div>
                                                        <div class="text-sm text-gray-500">ID: {{ report.reportedUser.id }}</div>
                                                        <div class="text-sm text-gray-500">Email: {{ report.reportedUser.email }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <h5 class="font-medium text-gray-700">Reported User:</h5>
                                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                                <div class="text-sm text-gray-500">[Deleted User]</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6" v-if="!report.reviewed">
                                <PrimaryButton @click="markAsReviewed">
                                    Mark as Reviewed
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 