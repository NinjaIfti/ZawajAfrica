<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="4xl">
        <div class="bg-white rounded-lg max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-[#654396] text-white">
                <h2 class="text-2xl font-bold">About ZawajAfrica</h2>
                <button
                    @click="closeModal"
                    class="text-white hover:text-gray-200 text-2xl font-bold"
                    type="button"
                >
                    ×
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[70vh] space-y-6">
                <!-- Header Info -->
                <div class="bg-gradient-to-r from-[#654396] to-[#E6A157] text-white p-6 rounded-lg">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-3">🌍 ZawajAfrica</h3>
                        <p class="text-lg leading-relaxed mb-4">
                            The First Halal Matchmaking App for People of Color
                        </p>
                        <p class="text-base opacity-90">
                            A premium, faith-based matchmaking platform designed exclusively for African Muslims in Africa and the diaspora.
                        </p>
                    </div>
                </div>

                <!-- About Us Content -->
                <div class="prose prose-lg max-w-none">
                    <div class="space-y-6">
                        <!-- Vision & Mission -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="text-xl font-semibold text-[#654396] mb-3">🌟 Our Vision</h4>
                                <p class="text-gray-700">
                                    To be the most trusted halal matchmaking platform for African Muslims — connecting hearts across continents through love, faith, and purpose.
                                </p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="text-xl font-semibold text-[#654396] mb-3">🤲 Our Mission</h4>
                                <p class="text-gray-700">
                                    To guide African Muslims into meaningful unions through safe, verified, value-oriented processes rooted in Islamic principles.
                                </p>
                            </div>
                        </div>

                        <!-- What We Do -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">What We Do</h4>
                            <p class="text-gray-700 mb-4">
                                ZawajAfrica is committed to creating a safe, respectful, and spiritually guided space where African Muslims can find meaningful, marriage-focused connections. We honor Islamic values, celebrate African heritage, and support our members with personalized tools and sincere guidance on the path to love and companionship.
                            </p>
                        </div>

                        <!-- Our Features -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">✨ Our Features</h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">💝</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Halal Matchmaking</h5>
                                            <p class="text-sm text-gray-600">Faith-based connections with Islamic principles</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">🔍</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Advanced Matching</h5>
                                            <p class="text-sm text-gray-600">Compatibility based on values and preferences</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">✅</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Verified Profiles</h5>
                                            <p class="text-sm text-gray-600">Safe and authentic user verification</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">🗣️</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Therapy Services</h5>
                                            <p class="text-sm text-gray-600">Professional counseling and life coaching</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">🤖</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">AI Assistant</h5>
                                            <p class="text-sm text-gray-600">Personalized guidance and support</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="text-[#E6A157] mr-3 mt-1">🌍</div>
                                        <div>
                                            <h5 class="font-medium text-gray-800">Global Community</h5>
                                            <p class="text-sm text-gray-600">Connect across Africa and the diaspora</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Our Values -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">🎯 Our Values</h4>
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl mb-2">🤲</div>
                                    <h5 class="font-medium text-gray-800 mb-2">Islamic Principles</h5>
                                    <p class="text-sm text-gray-600">Rooted in faith and Islamic values</p>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl mb-2">🌍</div>
                                    <h5 class="font-medium text-gray-800 mb-2">African Heritage</h5>
                                    <p class="text-sm text-gray-600">Celebrating our rich cultural diversity</p>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl mb-2">💎</div>
                                    <h5 class="font-medium text-gray-800 mb-2">Authenticity</h5>
                                    <p class="text-sm text-gray-600">Genuine connections and real relationships</p>
                                </div>
                            </div>
                        </div>

                        <!-- Who We Serve -->
                        <div>
                            <h4 class="text-xl font-semibold text-[#654396] mb-3">👥 Who We Serve</h4>
                            <p class="text-gray-700 mb-4">
                                ZawajAfrica caters to African Muslims both in Africa and in the diaspora. We welcome all individuals aged 18 and above who are serious about finding meaningful, marriage-focused relationships while maintaining their Islamic faith and African cultural values.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
                <button
                    @click="closeModal"
                    class="px-6 py-2 bg-[#654396] text-white rounded-lg hover:bg-[#553082] font-medium"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template> 