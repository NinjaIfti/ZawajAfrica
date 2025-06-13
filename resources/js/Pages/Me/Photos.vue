<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import ProfileHeader from '@/Components/ProfileHeader.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Sample photos data - empty placeholders for now
const photos = ref([
    { id: 1, url: null, is_primary: true },
]);

// Function to handle file selection
const handleFileSelect = (event) => {
    // This would be implemented with actual file upload logic
    console.log('File selected:', event.target.files);
};
</script>

<template>
    <Head title="My Photos" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Profile Header Component -->
                <ProfileHeader :user="user" activeTab="photos" />

                <!-- Photos Content -->
                <div>
                    <h2 class="text-2xl font-bold mb-2">Photos</h2>
                    <p class="text-gray-600 mb-6">Add photos is best way to stand you out from other profiles.</p>
                    
                    <!-- Photo Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <!-- Main Photo (Larger) -->
                        <div class="col-span-1 sm:col-span-2 sm:row-span-2">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Additional Photos (Smaller) -->
                        <div class="col-span-1">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-span-1">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-span-1">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-span-1">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-span-1">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>
                         <div class="col-span-1">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>
                         <div class="col-span-1">
                            <div class="bg-[#6543961A] rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                <!-- Diagonal pattern background -->
                                <div class="absolute inset-0 opacity-5">
                                    <div class="absolute top-0 left-0 w-full h-full bg-primary-light transform -rotate-45"></div>
                                </div>
                                <label class="cursor-pointer w-full h-full flex items-center justify-center z-10">
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileSelect">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                </label>
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