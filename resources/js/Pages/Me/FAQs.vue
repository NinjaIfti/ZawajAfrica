<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

const props = defineProps({
    auth: Object,
    user: Object,
});

// Sample FAQs
const faqs = ref([
    {
        id: 1,
        question: 'What are you looking for in a relationship?',
        answer: 'I am looking for a serious, long-term relationship with someone who shares my values and goals.',
        isEditing: false
    },
    {
        id: 2,
        question: 'How important is religion in your life?',
        answer: 'Religion is very important to me. I practice regularly and seek a partner who values faith as well.',
        isEditing: false
    },
    {
        id: 3,
        question: 'What are your views on having children?',
        answer: 'I would like to have children in the future, God willing. Family is important to me.',
        isEditing: false
    },
    {
        id: 4,
        question: 'What do you do for fun?',
        answer: 'I enjoy reading, traveling, spending time with family and friends, and outdoor activities.',
        isEditing: false
    },
    {
        id: 5,
        question: 'How would your friends describe you?',
        answer: 'My friends would describe me as loyal, thoughtful, and someone who always makes time for the people I care about.',
        isEditing: false
    }
]);

// Toggle edit mode
const toggleEdit = (faqId) => {
    const faq = faqs.value.find(f => f.id === faqId);
    if (faq) {
        faq.isEditing = !faq.isEditing;
    }
};

// Save FAQ answer
const saveAnswer = (faqId, event) => {
    const faq = faqs.value.find(f => f.id === faqId);
    if (faq) {
        faq.answer = event.target.value;
        faq.isEditing = false;
    }
};

// Add new FAQ
const newQuestion = ref('');
const newAnswer = ref('');

const addNewFAQ = () => {
    if (newQuestion.value.trim() && newAnswer.value.trim()) {
        faqs.value.push({
            id: faqs.value.length + 1,
            question: newQuestion.value,
            answer: newAnswer.value,
            isEditing: false
        });
        
        // Reset form
        newQuestion.value = '';
        newAnswer.value = '';
    }
};

// Delete FAQ
const deleteFAQ = (faqId) => {
    faqs.value = faqs.value.filter(faq => faq.id !== faqId);
};
</script>

<template>
    <Head title="My FAQs" />

    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Sidebar Component -->
        <Sidebar :user="$page.props.auth.user" />
        
        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="container mx-auto max-w-6xl">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">My FAQs</h1>
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

                <!-- FAQs Explanation -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-3">About FAQs</h2>
                    <p class="text-gray-600">
                        FAQs (Frequently Asked Questions) help potential matches learn more about you. 
                        Answer these common questions to give others a better understanding of who you are and what you're looking for.
                    </p>
                </div>
                
                <!-- FAQs List -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-6">My Answers</h2>
                    
                    <div class="space-y-6">
                        <div v-for="faq in faqs" :key="faq.id" class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                            <div class="flex justify-between items-start">
                                <h3 class="font-medium text-lg text-gray-800">{{ faq.question }}</h3>
                                <div class="flex space-x-2">
                                    <button 
                                        @click="toggleEdit(faq.id)" 
                                        class="text-gray-500 hover:text-primary-dark"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button 
                                        @click="deleteFAQ(faq.id)" 
                                        class="text-gray-500 hover:text-red-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="!faq.isEditing" class="mt-2 text-gray-600">
                                {{ faq.answer }}
                            </div>
                            
                            <div v-else class="mt-2">
                                <textarea 
                                    :value="faq.answer" 
                                    @blur="saveAnswer(faq.id, $event)"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    rows="3"
                                ></textarea>
                                <div class="mt-2 text-sm text-gray-500">
                                    Click outside the text area to save your answer.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Add New FAQ -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-6">Add Custom FAQ</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Question
                            </label>
                            <input 
                                v-model="newQuestion" 
                                type="text" 
                                placeholder="Enter your custom question" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Answer
                            </label>
                            <textarea 
                                v-model="newAnswer" 
                                placeholder="Enter your answer" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                rows="3"
                            ></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button 
                                @click="addNewFAQ" 
                                class="bg-primary-dark hover:bg-primary-dark/90 text-white px-6 py-2 rounded-md"
                                :disabled="!newQuestion.trim() || !newAnswer.trim()"
                                :class="{ 'opacity-50 cursor-not-allowed': !newQuestion.trim() || !newAnswer.trim() }"
                            >
                                Add FAQ
                            </button>
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