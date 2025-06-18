<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    reports: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
};

const getStatusBadge = (report) => {
    if (report.reviewed) {
        return 'bg-green-100 text-green-800';
    }
    return 'bg-yellow-100 text-yellow-800';
};

const getStatusText = (report) => {
    if (report.reviewed) {
        return 'Reviewed';
    }
    return 'Pending';
};
</script>

<template>
    <Head title="User Reports" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Reports
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4 flex justify-between items-center">
                            <h3 class="text-lg font-bold">All Reports</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reporter
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reported User
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reason
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Blocked
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="report in (reports?.data || [])" :key="report.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ report.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" v-if="report.reporter">
                                                {{ report.reporter.name }}
                                            </div>
                                            <div class="text-sm text-gray-900" v-else>
                                                [Deleted User]
                                            </div>
                                            <div class="text-xs text-gray-500" v-if="report.reporter">
                                                ID: {{ report.reporter.id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" v-if="report.reportedUser">
                                                {{ report.reportedUser.name }}
                                            </div>
                                            <div class="text-sm text-gray-900" v-else>
                                                [Deleted User]
                                            </div>
                                            <div class="text-xs text-gray-500" v-if="report.reportedUser">
                                                ID: {{ report.reportedUser.id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ report.reason }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                  :class="report.is_blocked ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'">
                                                {{ report.is_blocked ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadge(report)">
                                                {{ getStatusText(report) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(report.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('admin.reports.show', report.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="reports.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            No reports found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <nav class="flex items-center justify-between">
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing
                                            <span class="font-medium">{{ reports.from || 0 }}</span>
                                            to
                                            <span class="font-medium">{{ reports.to || 0 }}</span>
                                            of
                                            <span class="font-medium">{{ reports.total }}</span>
                                            results
                                        </p>
                                    </div>
                                    <div>
                                        <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                            <template v-if="reports.links && reports.links.length > 0">
                                                <template v-for="(link, i) in reports.links" :key="i">
                                                    <Link v-if="link && link.url" :href="link.url"
                                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium"
                                                    :class="{'text-indigo-600 z-10': link.active, 'text-gray-500': !link.active}">
                                                    <span v-html="link.label"></span>
                                                </Link>
                                                    <span v-else-if="link && !link.url"
                                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-200 text-sm font-medium text-gray-400 cursor-not-allowed">
                                                        <span v-html="link.label"></span>
                                                    </span>
                                                </template>
                                            </template>
                                        </span>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>