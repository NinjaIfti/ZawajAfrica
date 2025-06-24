<template>
    <AppLayout title="KYC Verification">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="text-center">
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">KYC Verification</h1>
                            <p class="text-lg text-gray-600 mb-6">
                                Complete your identity verification to unlock higher transaction limits and enhanced security features.
                            </p>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
                                <div 
                                    class="bg-gradient-to-r from-purple-500 to-indigo-600 h-3 rounded-full transition-all duration-500"
                                    :style="{ width: user.kyc_completion_percentage + '%' }"
                                ></div>
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ user.kyc_completion_percentage }}% Complete
                            </p>
                        </div>
                    </div>

                    <!-- Status Cards -->
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- BVN Status -->
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <div class="flex justify-center items-center mb-4">
                                <div :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center',
                                    user.kyc_bvn_verified ? 'bg-green-100' : 'bg-gray-100'
                                ]">
                                    <svg v-if="user.kyc_bvn_verified" class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="font-semibold text-gray-900">BVN Verification</h3>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ user.kyc_bvn_verified ? 'Verified' : 'Pending' }}
                            </p>
                            <p v-if="user.masked_bvn" class="text-xs text-gray-500 mt-1">
                                {{ user.masked_bvn }}
                            </p>
                        </div>

                        <!-- NIN Status -->
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <div class="flex justify-center items-center mb-4">
                                <div :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center',
                                    user.kyc_nin_verified ? 'bg-green-100' : 'bg-gray-100'
                                ]">
                                    <svg v-if="user.kyc_nin_verified" class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="font-semibold text-gray-900">NIN Verification</h3>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ user.kyc_nin_verified ? 'Verified' : 'Pending' }}
                            </p>
                            <p v-if="user.masked_nin" class="text-xs text-gray-500 mt-1">
                                {{ user.masked_nin }}
                            </p>
                        </div>

                        <!-- Overall Status -->
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <div class="flex justify-center items-center mb-4">
                                <div :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center',
                                    user.kyc_status === 'verified' ? 'bg-green-100' : 
                                    user.kyc_status === 'failed' ? 'bg-red-100' : 'bg-yellow-100'
                                ]">
                                    <svg v-if="user.kyc_status === 'verified'" class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="user.kyc_status === 'failed'" class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="font-semibold text-gray-900">KYC Status</h3>
                            <p class="text-sm text-gray-600 mt-2 capitalize">
                                {{ user.kyc_status }}
                            </p>
                            <p v-if="user.is_eligible_for_higher_limits" class="text-xs text-green-600 mt-1">
                                ✓ Higher limits enabled
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="$page.props.flash.success" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-green-700 font-medium">{{ $page.props.flash.success }}</p>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="user.kyc_failure_reason" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-red-700 font-medium">Verification Failed</p>
                            <p class="text-red-600 text-sm mt-1">{{ user.kyc_failure_reason }}</p>
                        </div>
                    </div>
                </div>

                <!-- KYC Forms -->
                <div v-if="user.kyc_status !== 'verified'" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- BVN Form -->
                    <div v-if="!user.kyc_bvn_verified" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-blue-100 rounded-lg p-3 mr-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">BVN Verification</h3>
                                    <p class="text-sm text-gray-600">Verify your Bank Verification Number</p>
                                </div>
                            </div>

                            <form @submit.prevent="submitBvn">
                                <div class="mb-6">
                                    <label for="bvn" class="block text-sm font-medium text-gray-700 mb-2">
                                        Bank Verification Number (BVN)
                                    </label>
                                    <input
                                        type="text"
                                        id="bvn"
                                        v-model="bvnForm.bvn"
                                        placeholder="Enter your 11-digit BVN"
                                        maxlength="11"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': $page.props.errors.bvn }"
                                    />
                                    <p v-if="$page.props.errors.bvn" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.bvn }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Your BVN is used for identity verification and will be kept secure.
                                    </p>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="bvnForm.processing || !bvnForm.bvn || bvnForm.bvn.length !== 11"
                                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                                >
                                    <span v-if="bvnForm.processing">Verifying BVN...</span>
                                    <span v-else>Verify BVN</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- NIN Form -->
                    <div v-if="!user.kyc_nin_verified" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="bg-green-100 rounded-lg p-3 mr-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">NIN Verification</h3>
                                    <p class="text-sm text-gray-600">Verify your National Identification Number</p>
                                </div>
                            </div>

                            <form @submit.prevent="submitNin">
                                <div class="mb-6">
                                    <label for="nin" class="block text-sm font-medium text-gray-700 mb-2">
                                        National Identification Number (NIN)
                                    </label>
                                    <input
                                        type="text"
                                        id="nin"
                                        v-model="ninForm.nin"
                                        placeholder="Enter your 11-digit NIN"
                                        maxlength="11"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        :class="{ 'border-red-500': $page.props.errors.nin }"
                                    />
                                    <p v-if="$page.props.errors.nin" class="text-red-500 text-sm mt-1">
                                        {{ $page.props.errors.nin }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Your NIN is used for identity verification and will be kept secure.
                                    </p>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="ninForm.processing || !ninForm.nin || ninForm.nin.length !== 11"
                                    class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                                >
                                    <span v-if="ninForm.processing">Verifying NIN...</span>
                                    <span v-else>Verify NIN</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Complete Both Form -->
                    <div v-if="!user.kyc_bvn_verified && !user.kyc_nin_verified" class="lg:col-span-2">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg p-1">
                            <div class="bg-white rounded-lg p-6">
                                <div class="text-center mb-6">
                                    <div class="bg-purple-100 rounded-lg p-3 inline-block mb-4">
                                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Complete Full Verification</h3>
                                    <p class="text-gray-600">Submit both BVN and NIN together for instant verification</p>
                                </div>

                                <form @submit.prevent="submitBoth" class="max-w-md mx-auto">
                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        <div>
                                            <label for="both-bvn" class="block text-sm font-medium text-gray-700 mb-2">BVN</label>
                                            <input
                                                type="text"
                                                id="both-bvn"
                                                v-model="bothForm.bvn"
                                                placeholder="11-digit BVN"
                                                maxlength="11"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                :class="{ 'border-red-500': $page.props.errors.bvn }"
                                            />
                                        </div>
                                        <div>
                                            <label for="both-nin" class="block text-sm font-medium text-gray-700 mb-2">NIN</label>
                                            <input
                                                type="text"
                                                id="both-nin"
                                                v-model="bothForm.nin"
                                                placeholder="11-digit NIN"
                                                maxlength="11"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                :class="{ 'border-red-500': $page.props.errors.nin }"
                                            />
                                        </div>
                                    </div>

                                    <div v-if="$page.props.errors.error" class="mb-4">
                                        <p class="text-red-500 text-sm">{{ $page.props.errors.error }}</p>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="bothForm.processing || !bothForm.bvn || bothForm.bvn.length !== 11 || !bothForm.nin || bothForm.nin.length !== 11"
                                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                                    >
                                        <span v-if="bothForm.processing">Verifying Both...</span>
                                        <span v-else>Complete Full Verification</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Section -->
                <div v-if="user.kyc_status === 'verified'" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="bg-green-100 rounded-full p-6 inline-block mb-6">
                            <svg class="w-16 h-16 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">KYC Verification Complete!</h2>
                        <p class="text-gray-600 mb-6">
                            Your identity has been successfully verified. You now have access to higher transaction limits and enhanced security features.
                        </p>
                        <p v-if="user.kyc_verified_at" class="text-sm text-gray-500">
                            Verified on {{ new Date(user.kyc_verified_at).toLocaleDateString() }}
                        </p>
                        
                        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                            <div class="bg-green-50 rounded-lg p-4">
                                <h4 class="font-semibold text-green-900 mb-2">✓ Higher Transaction Limits</h4>
                                <p class="text-sm text-green-700">Enjoy increased limits for payments and transfers</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <h4 class="font-semibold text-green-900 mb-2">✓ Enhanced Security</h4>
                                <p class="text-sm text-green-700">Your account now has additional security protection</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mr-3 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="font-semibold text-blue-900 mb-2">Why is KYC Required?</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Compliance with Nigerian financial regulations</li>
                                <li>• Enhanced security for your account and transactions</li>
                                <li>• Access to higher transaction limits and premium features</li>
                                <li>• Protection against fraud and identity theft</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object
})

// Individual forms
const bvnForm = useForm({
    bvn: ''
})

const ninForm = useForm({
    nin: ''
})

const bothForm = useForm({
    bvn: '',
    nin: ''
})

const submitBvn = () => {
    bvnForm.post(route('kyc.submit.bvn'), {
        preserveScroll: true,
        onSuccess: () => {
            bvnForm.reset()
        }
    })
}

const submitNin = () => {
    ninForm.post(route('kyc.submit.nin'), {
        preserveScroll: true,
        onSuccess: () => {
            ninForm.reset()
        }
    })
}

const submitBoth = () => {
    bothForm.post(route('kyc.submit.both'), {
        preserveScroll: true,
        onSuccess: () => {
            bothForm.reset()
        }
    })
}
</script> 