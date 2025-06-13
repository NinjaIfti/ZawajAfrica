<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Categories of hobbies and interests
const categories = ref([
    {
        id: 1,
        name: 'Sports & Activities',
        hobbies: [
            { id: 1, name: 'Football', selected: true },
            { id: 2, name: 'Basketball', selected: false },
            { id: 3, name: 'Swimming', selected: true },
            { id: 4, name: 'Hiking', selected: false },
            { id: 5, name: 'Yoga', selected: false },
            { id: 6, name: 'Running', selected: false },
        ]
    },
    {
        id: 2,
        name: 'Arts & Entertainment',
        hobbies: [
            { id: 7, name: 'Reading', selected: true },
            { id: 8, name: 'Movies', selected: true },
            { id: 9, name: 'Music', selected: false },
            { id: 10, name: 'Painting', selected: false },
            { id: 11, name: 'Photography', selected: false },
            { id: 12, name: 'Dancing', selected: false },
        ]
    },
    {
        id: 3,
        name: 'Food & Dining',
        hobbies: [
            { id: 13, name: 'Cooking', selected: false },
            { id: 14, name: 'Baking', selected: false },
            { id: 15, name: 'Food Tasting', selected: true },
            { id: 16, name: 'Wine Tasting', selected: false },
        ]
    },
    {
        id: 4,
        name: 'Travel & Adventure',
        hobbies: [
            { id: 17, name: 'Traveling', selected: true },
            { id: 18, name: 'Camping', selected: false },
            { id: 19, name: 'Road Trips', selected: false },
            { id: 20, name: 'Backpacking', selected: false },
        ]
    },
]);

// Toggle hobby selection
const toggleHobby = (categoryId, hobbyId) => {
    const category = categories.value.find(c => c.id === categoryId);
    if (category) {
        const hobby = category.hobbies.find(h => h.id === hobbyId);
        if (hobby) {
            hobby.selected = !hobby.selected;
        }
    }
};

// Get selected hobbies
const selectedHobbies = computed(() => {
    return categories.value.flatMap(category => 
        category.hobbies.filter(hobby => hobby.selected)
    );
});

// Save changes
const saveChanges = () => {
    // This would be implemented with actual API call
    console.log('Saving selected hobbies:', selectedHobbies.value);
};
</script>

<template>
    <Head title="My Hobbies & Interests" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">My Hobbies & Interests</h1>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-600">English</span>
                        <button class="rounded-full p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                        </button>
                        <div class="h-10 w-10 rounded-full overflow-hidden">
                            <img src="/images/placeholder.jpg" alt="Profile" class="h-full w-full object-cover">
                        </div>
                    </div>
                </div>

                <!-- Hobbies & Interests Section -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">Select Your Hobbies & Interests</h2>
                        <p class="text-sm text-gray-500">
                            Selected: {{ selectedHobbies.length }}/20
                        </p>
                    </div>

                    <div class="space-y-8">
                        <!-- Categories -->
                        <div v-for="category in categories" :key="category.id" class="space-y-4">
                            <h3 class="font-medium text-lg text-gray-800">{{ category.name }}</h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div 
                                    v-for="hobby in category.hobbies" 
                                    :key="hobby.id"
                                    @click="toggleHobby(category.id, hobby.id)"
                                    class="border rounded-lg p-3 cursor-pointer transition-all"
                                    :class="hobby.selected ? 'bg-primary-light border-primary-dark' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            :checked="hobby.selected" 
                                            class="rounded text-primary-dark focus:ring-primary-dark mr-3"
                                            @click.stop
                                        >
                                        <span>{{ hobby.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button 
                            @click="saveChanges" 
                            class="bg-primary-dark hover:bg-primary-dark/90 text-white px-6 py-2 rounded-md"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
                
                <!-- Personality Traits Section -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-6">My Personality Traits</h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-1" class="rounded text-primary-dark focus:ring-primary-dark mr-3">
                            <label for="trait-1" class="text-gray-700">Adventurous</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-2" class="rounded text-primary-dark focus:ring-primary-dark mr-3" checked>
                            <label for="trait-2" class="text-gray-700">Creative</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-3" class="rounded text-primary-dark focus:ring-primary-dark mr-3">
                            <label for="trait-3" class="text-gray-700">Analytical</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-4" class="rounded text-primary-dark focus:ring-primary-dark mr-3" checked>
                            <label for="trait-4" class="text-gray-700">Patient</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-5" class="rounded text-primary-dark focus:ring-primary-dark mr-3">
                            <label for="trait-5" class="text-gray-700">Outgoing</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-6" class="rounded text-primary-dark focus:ring-primary-dark mr-3" checked>
                            <label for="trait-6" class="text-gray-700">Compassionate</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-7" class="rounded text-primary-dark focus:ring-primary-dark mr-3">
                            <label for="trait-7" class="text-gray-700">Organized</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-8" class="rounded text-primary-dark focus:ring-primary-dark mr-3">
                            <label for="trait-8" class="text-gray-700">Curious</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="trait-9" class="rounded text-primary-dark focus:ring-primary-dark mr-3" checked>
                            <label for="trait-9" class="text-gray-700">Reliable</label>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button class="bg-primary-dark hover:bg-primary-dark/90 text-white px-6 py-2 rounded-md">
                            Save Personality Traits
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>