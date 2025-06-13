<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Personality traits categories
const categories = ref([
    {
        id: 1,
        name: 'Communication Style',
        traits: [
            { id: 1, name: 'Direct', selected: true },
            { id: 2, name: 'Diplomatic', selected: false },
            { id: 3, name: 'Expressive', selected: false },
            { id: 4, name: 'Reserved', selected: true },
        ]
    },
    {
        id: 2,
        name: 'Social Preferences',
        traits: [
            { id: 5, name: 'Extroverted', selected: false },
            { id: 6, name: 'Introverted', selected: true },
            { id: 7, name: 'Ambivert', selected: false },
        ]
    },
    {
        id: 3,
        name: 'Decision Making',
        traits: [
            { id: 8, name: 'Analytical', selected: true },
            { id: 9, name: 'Intuitive', selected: false },
            { id: 10, name: 'Cautious', selected: false },
            { id: 11, name: 'Spontaneous', selected: false },
        ]
    },
    {
        id: 4,
        name: 'Core Values',
        traits: [
            { id: 12, name: 'Family-oriented', selected: true },
            { id: 13, name: 'Career-focused', selected: false },
            { id: 14, name: 'Spirituality', selected: true },
            { id: 15, name: 'Personal Growth', selected: false },
        ]
    },
]);

// Personality test questions
const questions = ref([
    {
        id: 1,
        text: 'I prefer spending time with a few close friends rather than at large social gatherings.',
        answer: 4 // Scale of 1-5, where 1 is strongly disagree and 5 is strongly agree
    },
    {
        id: 2,
        text: 'I like to plan things ahead rather than be spontaneous.',
        answer: 3
    },
    {
        id: 3,
        text: 'I often reflect on my feelings and emotions.',
        answer: 5
    },
    {
        id: 4,
        text: 'I enjoy deep conversations about philosophy and spirituality.',
        answer: 4
    },
    {
        id: 5,
        text: 'I am comfortable expressing my opinions even when they differ from others.',
        answer: 3
    }
]);

// Toggle trait selection
const toggleTrait = (categoryId, traitId) => {
    const category = categories.value.find(c => c.id === categoryId);
    if (category) {
        const trait = category.traits.find(t => t.id === traitId);
        if (trait) {
            trait.selected = !trait.selected;
        }
    }
};

// Update question answer
const updateAnswer = (questionId, value) => {
    const question = questions.value.find(q => q.id === questionId);
    if (question) {
        question.answer = value;
    }
};

// Get selected traits
const selectedTraits = computed(() => {
    return categories.value.flatMap(category => 
        category.traits.filter(trait => trait.selected)
    );
});

// Save changes
const saveChanges = () => {
    // This would be implemented with actual API call
    console.log('Saving selected traits:', selectedTraits.value);
    console.log('Saving question answers:', questions.value);
};
</script>

<template>
    <Head title="My Personality" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">My Personality</h1>
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

                <!-- Personality Traits Section -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">My Personality Traits</h2>
                        <p class="text-sm text-gray-500">
                            Selected: {{ selectedTraits.length }}/15
                        </p>
                    </div>

                    <div class="space-y-8">
                        <!-- Categories -->
                        <div v-for="category in categories" :key="category.id" class="space-y-4">
                            <h3 class="font-medium text-lg text-gray-800">{{ category.name }}</h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div 
                                    v-for="trait in category.traits" 
                                    :key="trait.id"
                                    @click="toggleTrait(category.id, trait.id)"
                                    class="border rounded-lg p-3 cursor-pointer transition-all"
                                    :class="trait.selected ? 'bg-primary-light border-primary-dark' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            :checked="trait.selected" 
                                            class="rounded text-primary-dark focus:ring-primary-dark mr-3"
                                            @click.stop
                                        >
                                        <span>{{ trait.name }}</span>
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
                            Save Traits
                        </button>
                    </div>
                </div>
                
                <!-- Personality Test Section -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-6">Personality Assessment</h2>
                    <p class="text-gray-600 mb-6">
                        Answer these questions to help us better understand your personality and find compatible matches.
                    </p>
                    
                    <div class="space-y-8">
                        <div v-for="question in questions" :key="question.id" class="space-y-3">
                            <p class="font-medium">{{ question.text }}</p>
                            
                            <div class="flex items-center space-x-1">
                                <span class="text-xs text-gray-500">Strongly Disagree</span>
                                <div class="flex-1 flex justify-between">
                                    <button 
                                        v-for="value in 5" 
                                        :key="value" 
                                        @click="updateAnswer(question.id, value)"
                                        class="w-10 h-10 rounded-full flex items-center justify-center"
                                        :class="question.answer === value ? 'bg-primary-dark text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                                    >
                                        {{ value }}
                                    </button>
                                </div>
                                <span class="text-xs text-gray-500">Strongly Agree</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button 
                            @click="saveChanges" 
                            class="bg-primary-dark hover:bg-primary-dark/90 text-white px-6 py-2 rounded-md"
                        >
                            Save Answers
                        </button>
                    </div>
                </div>
                
                <!-- Compatibility Preferences -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-6">Compatibility Preferences</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                How important is it that your match shares your personality traits?
                            </label>
                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option>Very important</option>
                                <option>Somewhat important</option>
                                <option>Not important</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                I prefer a partner who is:
                            </label>
                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option>Similar to me</option>
                                <option>Complementary to me</option>
                                <option>No preference</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Top personality trait I value in a partner:
                            </label>
                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option>Honesty</option>
                                <option>Kindness</option>
                                <option>Ambition</option>
                                <option>Humor</option>
                                <option>Intelligence</option>
                                <option>Patience</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button class="bg-primary-dark hover:bg-primary-dark/90 text-white px-6 py-2 rounded-md">
                            Save Preferences
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