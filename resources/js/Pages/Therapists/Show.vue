<template>
    <Head :title="`${therapist.name} - Therapist Profile`" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <div class="flex h-screen bg-gray-50">
        <!-- Mobile Sidebar Overlay -->
        <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileMenuOpen = false"></div>
            <div class="fixed top-0 left-0 h-full w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out">
                <Sidebar :user="$page.props.auth.user" />
            </div>
        </div>

        <!-- Left Sidebar - Hidden on mobile/tablet -->
        <div class="hidden lg:block">
            <Sidebar :user="$page.props.auth.user" />
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header with AppHeader component -->
            <div class=" border-b border-gray-200 px-4 lg:px-6 py-4">
                <AppHeader :user="$page.props.auth.user">
                    <template #title>
                        <div class="flex items-center">
                            <!-- Mobile Menu Button -->
                            <button 
                                @click="mobileMenuOpen = true"
                                class="lg:hidden mr-3 p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            
                            <!-- Back Button -->
                            <button @click="goBack" class="mr-2 lg:mr-3 p-1 hover:bg-gray-100 rounded-full">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <h1 class="text-lg lg:text-2xl font-bold text-gray-900">Therapist Profile</h1>
                        </div>
                    </template>
                </AppHeader>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-10">
                <div class="max-w-4xl mx-auto">
                    <!-- Therapist Profile Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <!-- Therapist Photo -->
                            <div class="md:w-1/3 bg-white p-4 lg:p-6 flex items-center justify-center">
                                <div class="w-full max-w-xs">
                                    <div class="w-full h-40 md:h-48 lg:h-56 rounded-lg overflow-hidden bg-gray-200 shadow-lg">
                                        <img 
                                            v-if="therapist.photo_url" 
                                            :src="therapist.photo_url" 
                                            :alt="therapist.name"
                                            class="w-full h-full object-cover object-center"
                                        />
                                        <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center">
                                            <svg class="w-12 h-12 lg:w-16 lg:h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Therapist Details -->
                            <div class="md:w-2/3 p-4 lg:p-6">
                                <div class="mb-4 lg:mb-6">
                                    <h2 class="text-xl lg:text-2xl font-bold text-gray-900 mb-2">{{ therapist.name }}</h2>
                                    <p class="text-sm lg:text-base text-gray-600 mb-4">
                                        {{ Array.isArray(therapist.specializations) 
                                            ? therapist.specializations.join(', ') 
                                            : therapist.specializations }}
                                    </p>
                                    
                                    <!-- Stats Row -->
                                    <div class="grid grid-cols-3 gap-2 lg:gap-4 mb-4 lg:mb-6">
                                        <div class="text-center bg-gray-100 rounded-lg p-2 lg:p-3">
                                            <span class="block text-sm lg:text-lg font-semibold text-purple-600">{{ therapist.years_of_experience }} years</span>
                                            <span class="text-xs lg:text-sm text-gray-500">Experience</span>
                                        </div>
                                        <div class="text-center bg-gray-100 rounded-lg p-2 lg:p-3">
                                            <span class="block text-sm lg:text-lg font-semibold text-purple-600">{{ therapist.session_duration || '90 min' }}</span>
                                            <span class="text-xs lg:text-sm text-grey-500">Session</span>
                                        </div>
                                        <div class="text-center bg-gray-100 rounded-lg p-2 lg:p-3">
                                            <div class="flex items-center justify-center mb-1">
                                                <span class="text-sm lg:text-lg font-semibold text-gray-900 mr-1">{{ therapist.avg_rating?.toFixed(1) || '4.5' }}</span>
                                                <div class="flex">
                                                    <svg v-for="star in 5" :key="star" class="w-3 h-3 lg:w-4 lg:h-4" 
                                                         :class="star <= Math.floor(therapist.avg_rating || 4.5) ? 'text-yellow-400' : 'text-gray-300'" 
                                                         fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <span class="text-xs lg:text-sm text-gray-500">Reviews</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Consultation Fee -->
                                    <div class="text-center mb-4 lg:mb-6 bg-gray-100 rounded-lg p-2 lg:p-3">
                                        <span class="text-xs lg:text-sm text-gray-600">Online Consultation Fee:</span>
                                        <p class="text-lg lg:text-2xl font-bold text-gray-900">₦{{ Number(therapist.hourly_rate).toLocaleString() }}</p>
                                    </div>
                                    <!-- Book Appointment Button -->
                                    <button 
                                        @click="showBookingSlider = true"
                                        class="w-full bg-violet-600 hover:bg-violet-700 text-white font-medium py-2.5 lg:py-3 px-4 rounded-xl transition-colors duration-200 text-sm lg:text-base"
                                    >
                                        Book Appointment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Available Hours Section -->
                    <div class="mt-4 lg:mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-4 lg:p-6">
                            <button 
                                @click="showAvailableHours = !showAvailableHours"
                                class="w-full flex items-center justify-between text-left"
                            >
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Available Hours</h3>
                                    <span class="ml-2 text-xs lg:text-sm text-green-600">Available Now</span>
                                </div>
                                <svg 
                                    class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': showAvailableHours }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Available Hours Content -->
                            <div v-if="showAvailableHours" class="mt-4 border-t pt-4">
                                <div v-if="therapist.schedule_by_day && Object.keys(therapist.schedule_by_day).length > 0" 
                                     class="space-y-3">
                                    <div v-for="(slots, day) in therapist.schedule_by_day" :key="day" class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                        <span class="font-medium text-gray-900 capitalize text-sm lg:text-base">{{ day }}</span>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="slot in slots" :key="slot.start + slot.end" 
                                                  class="text-xs lg:text-sm bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                                {{ slot.start }} - {{ slot.end }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-gray-500 text-center py-4">
                                    <p class="text-sm lg:text-base">{{ therapist.working_hours || 'Schedule information not available' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bio Section (if available) -->
                    <div v-if="therapist.bio" class="mt-4 lg:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-3">About</h3>
                        <p class="text-sm lg:text-base text-gray-600 leading-relaxed">{{ therapist.bio }}</p>
                    </div>
                    
                    <!-- Additional Info (if available) -->
                    <div v-if="therapist.additional_info" class="mt-4 lg:mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-3">Additional Information</h3>
                        <p class="text-sm lg:text-base text-gray-600 leading-relaxed">{{ therapist.additional_info }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Slot Selection Slider -->
        <div v-if="showBookingSlider" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-end z-50">
            <div class="bg-white w-full max-w-md h-full overflow-y-auto shadow-xl transform transition-transform duration-300 ease-in-out"
                 :class="showBookingSlider ? 'translate-x-0' : 'translate-x-full'">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h2 class="text-xl font-semibold">Select a time slot</h2>
                    <button @click="closeBookingSlider" class="p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4">
                <!-- Therapist Info -->
                    <div class="flex items-center space-x-3 mb-6">
                        <img :src="therapist.photo_url || '/images/male.png'" 
                            :alt="therapist.name"
                             class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ therapist.name }}</h3>
                            <p class="text-sm text-gray-600">{{ therapist.degree }}</p>
                            <p class="text-sm text-green-600 font-medium">Online Consultation Fee: ₦{{ therapist.hourly_rate?.toLocaleString() }}</p>
                            <div class="flex items-center mt-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-xs text-gray-500">Available</span>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Selection -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Select Platform</h4>
                        <div class="space-y-3">
                            <!-- Google Meet -->
                            <div @click="selectedPlatform = 'google_meet'" 
                                 :class="['flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all',
                                         selectedPlatform === 'google_meet' ? 'border-blue-500 bg-blue-50' : 'border-gray-200']">
                                <div class="flex items-center space-x-3 flex-1">
  <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
    <i class="fas fa-video text-white text-sm"></i>
                    </div>
  <span class="font-medium">Google Meet</span>
                </div>

                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center">
                                    <div v-if="selectedPlatform === 'google_meet'" class="w-3 h-3 bg-green-500 rounded-full"></div>
                                </div>
                            </div>

                            <!-- WhatsApp -->
                            <div @click="selectedPlatform = 'whatsapp'" 
                                 :class="['flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all',
                                         selectedPlatform === 'whatsapp' ? 'border-green-500 bg-green-50' : 'border-gray-200']">
                                <div class="flex items-center space-x-3 flex-1">
  <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
    <i class="fab fa-whatsapp text-white text-lg"></i>
  </div>
  <span class="font-medium text-gray-800">Whatsapp</span>
</div>

                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center">
                                    <div v-if="selectedPlatform === 'whatsapp'" class="w-3 h-3 bg-green-500 rounded-full"></div>
                                </div>
                            </div>

                            <!-- Zoom -->
                            <div @click="selectedPlatform = 'zoom'" 
                                 :class="['flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all',
                                         selectedPlatform === 'zoom' ? 'border-blue-600 bg-blue-50' : 'border-gray-200']">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                                        <i class="fas fa-video text-white text-sm"></i>
                                    </div>
                                    <span class="font-medium">Zoom</span>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center">
                                    <div v-if="selectedPlatform === 'zoom'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                </div>

                <!-- Date Selection -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Select date and time</h4>
                        <div class="grid grid-cols-7 gap-2 mb-4">
                            <div v-for="date in availableDates" :key="date.value"
                                @click="selectedDate = date.value"
                                 :class="['text-center p-3 rounded-lg cursor-pointer transition-all border',
                                         selectedDate === date.value ? 'bg-purple-600 text-white border-purple-600' : 'border-gray-200 hover:border-gray-300']">
                                <div class="text-xs font-medium">{{ date.day }}</div>
                                <div class="text-lg font-bold">{{ date.date }}</div>
                    </div>
                </div>
                    </div>
                    
                    <!-- Time Slots -->
                    <div v-if="selectedDate" class="mb-6">
                        <!-- Afternoon Slots -->
                        <div v-if="afternoonSlots.length > 0" class="mb-4">
                            <h5 class="font-medium text-gray-700 mb-2">Afternoon Slots</h5>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="slot in afternoonSlots" :key="'afternoon-' + slot"
                                        @click="selectedTimeSlot = slot"
                                        :class="['p-2 text-sm rounded-lg border transition-all',
                                               selectedTimeSlot === slot ? 'bg-purple-600 text-white border-purple-600' : 'border-gray-200 hover:border-gray-300']">
                                    {{ slot }}
                                </button>
                            </div>
                        </div>

                        <!-- Evening Slots -->
                        <div v-if="eveningSlots.length > 0">
                            <h5 class="font-medium text-gray-700 mb-2">Evening Slots</h5>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="slot in eveningSlots" :key="'evening-' + slot"
                                        @click="selectedTimeSlot = slot"
                                        :class="['p-2 text-sm rounded-lg border transition-all',
                                               selectedTimeSlot === slot ? 'bg-purple-600 text-white border-purple-600' : 'border-gray-200 hover:border-gray-300']">
                                    {{ slot }}
                                </button>
                        </div>
                    </div>
                </div>

                <!-- Confirm Button -->
                <button @click="proceedToPayment" 
                        :disabled="!selectedPlatform || !selectedDate || !selectedTimeSlot"
                            :class="['w-full py-4 rounded-lg font-medium text-white transition-all mt-6',
                                   selectedPlatform && selectedDate && selectedTimeSlot 
                                     ? 'bg-purple-600 hover:bg-purple-700' 
                                     : 'bg-gray-300 cursor-not-allowed']">
                    Confirm Booking
                </button>
                </div>
            </div>
        </div>

        <!-- Payment Gateway Selection Slider -->
        <div v-if="showPaymentSlider" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-end z-50">
            <div class="bg-white w-full max-w-md h-full overflow-y-auto shadow-xl transform transition-transform duration-300 ease-in-out"
                 :class="showPaymentSlider ? 'translate-x-0' : 'translate-x-full'">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h2 class="text-xl font-semibold">Select Payment Process</h2>
                    <button @click="closePaymentSlider" class="p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4">
                    <!-- Gateway Options -->
                    <div class="space-y-4 mb-8">
                        <!-- Monnify Option -->
                        <div @click="selectedPaymentGateway = 'monnify'"
                             :class="['flex items-center justify-between p-4 border-2 rounded-lg cursor-pointer transition-all',
                                     selectedPaymentGateway === 'monnify' ? 'border-orange-500 bg-orange-50' : 'border-gray-200']">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">MN</span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Monnify</h4>
                                    <p class="text-sm text-gray-600">Payments within Nigeria</p>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        <!-- Paystack Option -->
                        <div @click="selectedPaymentGateway = 'paystack'"
                             :class="['flex items-center justify-between p-4 border-2 rounded-lg cursor-pointer transition-all',
                                     selectedPaymentGateway === 'paystack' ? 'border-cyan-500 bg-cyan-50' : 'border-gray-200']">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-cyan-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xs font-bold">PS</span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Paystack</h4>
                                    <p class="text-sm text-gray-600">Payments outside Nigeria</p>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Booking Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ therapist.name }}</h4>
                                <p class="text-sm text-gray-600">{{ selectedDate }}, {{ selectedTimeSlot }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Fee:</p>
                                <p class="text-lg font-bold text-purple-600">₦{{ therapist.hourly_rate?.toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <!-- Back Button -->
                        <button @click="backToTimeSelection"
                                class="flex-1 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Back
                        </button>
                        
                        <!-- Continue Button -->
                        <button @click="proceedWithSelectedGateway"
                                :disabled="!selectedPaymentGateway"
                                :class="['flex-1 py-3 rounded-lg font-medium transition-colors',
                                       selectedPaymentGateway ? 'bg-purple-600 text-white hover:bg-purple-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed']">
                            Continue
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Details Slider (for Monnify) -->
        <div v-if="showCardDetailsSlider" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-end z-50">
            <div class="bg-white w-full max-w-md h-full overflow-y-auto shadow-xl transform transition-transform duration-300 ease-in-out"
                 :class="showCardDetailsSlider ? 'translate-x-0' : 'translate-x-full'">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h2 class="text-xl font-semibold">Payment Details</h2>
                    <button @click="closeCardDetailsSlider" class="p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4">
                    <!-- Card Type Selection -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                        <div 
                            v-for="cardType in cardTypes" 
                            :key="cardType.id"
                            @click="selectedCardType = cardType.id"
                            :class="[
                                'border-2 rounded-lg p-3 cursor-pointer transition-all duration-200 flex items-center justify-center',
                                selectedCardType === cardType.id 
                                    ? 'border-purple-500 bg-purple-50' 
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <div class="text-center">
                                <div class="w-8 h-6 mx-auto mb-1 flex items-center justify-center">
                                    <img 
                                        :src="cardType.logo" 
                                        :alt="cardType.name"
                                        class="max-w-full max-h-full object-contain"
                                    >
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ cardType.name }}</span>
                                <div v-if="selectedCardType === cardType.id" class="w-3 h-3 bg-purple-500 rounded-full mx-auto mt-1"></div>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Enter Card Details</h3>
                    
                    <!-- Card Details Form -->
                    <div class="space-y-4 mb-6">
                        <!-- Cardholder Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cardholder Name
                            </label>
                            <input
                                v-model="cardDetails.holderName"
                                type="text"
                                placeholder="Enter Name"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                        </div>

                        <!-- Card Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Card Number
                            </label>
                            <input
                                v-model="cardDetails.number"
                                type="text"
                                placeholder="Enter Card Number"
                                maxlength="19"
                                @input="formatCardNumber"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                        </div>

                        <!-- Expiry Date and CVV -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Expiry Date
                                </label>
                                <input
                                    v-model="cardDetails.expiry"
                                    type="text"
                                    placeholder="MM/YY"
                                    maxlength="5"
                                    @input="formatExpiry"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    CVV
                                </label>
                                <input
                                    v-model="cardDetails.cvv"
                                    type="text"
                                    placeholder="***"
                                    maxlength="4"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                >
                            </div>
                    </div>
                </div>

                <!-- Booking Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ therapist.name }}</h4>
                                <p class="text-sm text-gray-600">{{ selectedDate }}, {{ selectedTimeSlot }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Fee:</p>
                                <p class="text-lg font-bold text-purple-600">₦{{ therapist.hourly_rate?.toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <!-- Back Button -->
                        <button @click="backToPaymentSelection"
                                class="flex-1 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Back
                        </button>
                        
                        <!-- Pay Now Button -->
                        <button @click="processPayment"
                                :disabled="!isCardDetailsValid"
                                :class="['flex-1 py-3 rounded-lg font-medium transition-colors',
                                       isCardDetailsValid ? 'bg-purple-600 text-white hover:bg-purple-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed']">
                            <span v-if="isProcessingPayment">Processing...</span>
                            <span v-else>Pay Now</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Success Modal -->
        <PaymentSuccessModal 
            :show="showPaymentSuccessModal" 
            :payment-type="page.props.flash?.payment_type || 'therapist_booking'"
            @close="closePaymentSuccessModal" 
        />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router, Head, usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'
import AppHeader from '@/Components/AppHeader.vue'
import PaymentSuccessModal from '@/Components/PaymentSuccessModal.vue'
import PaymentGatewaySelector from '@/Components/PaymentGatewaySelector.vue'
import axios from 'axios'

const page = usePage()
const showPaymentSuccessModal = ref(false)

const props = defineProps({
    therapist: Object,
    userBookings: Array,
})

const showAvailableHours = ref(false)
const showBookingSlider = ref(false)
const showPaymentSlider = ref(false)
const showCardDetailsSlider = ref(false)
const selectedPlatform = ref('')
const selectedDate = ref('')
const selectedTimeSlot = ref('')
const selectedPaymentGateway = ref('')
const selectedCardType = ref('mastercard')
const mobileMenuOpen = ref(false)
const isProcessingPayment = ref(false)

// Card details for Monnify
const cardDetails = ref({
    holderName: '',
    number: '',
    expiry: '',
    cvv: ''
})

const cardTypes = ref([
    {
        id: 'mastercard',
        name: 'Mastercard',
        logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iI0VCMDAxQiIvPgo8Y2lyY2xlIGN4PSIxNSIgY3k9IjEyIiByPSI3IiBmaWxsPSIjRkY1RjAwIi8+CjxjaXJjbGUgY3g9IjI1IiBjeT0iMTIiIHI9IjciIGZpbGw9IiNGRkY1RjAiLz4KPC9zdmc+'
    },
    {
        id: 'visa',
        name: 'Visa',
        logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzFFNzNBQSIvPgo8dGV4dCB4PSI1IiB5PSIxNiIgZmlsbD0id2hpdGUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZm9udC13ZWlnaHQ9ImJvbGQiPlZJU0E8L3RleHQ+Cjwvc3ZnPg=='
    },
    {
        id: 'verve',
        name: 'Verve Card',
        logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzAwNjUzOSIvPgo8dGV4dCB4PSI4IiB5PSIxNiIgZmlsbD0id2hpdGUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSI4IiBmb250LXdlaWdodD0iYm9sZCI+VkVSVkU8L3RleHQ+Cjwvc3ZnPg=='
    },
    {
        id: 'googlepay',
        name: 'G Pay',
        logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzRDODVGRiIvPgo8dGV4dCB4PSIxMCIgeT0iMTYiIGZpbGw9IndoaXRlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iOCIgZm9udC13ZWlnaHQ9ImJvbGQiPkdQYXk8L3RleHQ+Cjwvc3ZnPg=='
    }
])

// Generate available dates for the next 7 days
const availableDates = ref([])
const generateDates = () => {
    const dates = []
    const today = new Date()
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(today)
        date.setDate(today.getDate() + i)
        
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
        const day = i === 0 ? 'Today' : dayNames[date.getDay()]
        const dateNum = date.getDate()
        const fullDayName = date.toLocaleDateString('en-US', { weekday: 'long' })
        
        dates.push({
            day: day,
            date: dateNum,
            fullDayName: fullDayName,
            value: date.toLocaleDateString('en-GB', { 
                weekday: 'short', 
                day: 'numeric', 
                month: 'short' 
            })
        })
    }
    
    availableDates.value = dates
}

// Initialize dates when component loads
generateDates()

// Reset selected time slot when date changes
watch(selectedDate, () => {
    selectedTimeSlot.value = ''
})

// Get time slots for the selected date from real therapist availability
const getSlotsForSelectedDate = computed(() => {
    if (!selectedDate.value || !props.therapist.slots_by_day) {
        return []
    }
    
    // Find the selected date's full day name
    const selectedDateObj = availableDates.value.find(date => date.value === selectedDate.value)
    if (!selectedDateObj) {
        return []
    }
    
    // Get slots for this day from therapist's real availability
    const daySlots = props.therapist.slots_by_day[selectedDateObj.fullDayName] || []
    
    // Convert to display format and sort
    return daySlots.map(slot => {
        // Convert 24-hour format to 12-hour format
        const time = new Date(`2000-01-01T${slot}:00`)
        return time.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        })
    }).sort((a, b) => {
        // Sort by time
        const timeA = new Date(`2000-01-01 ${a}`)
        const timeB = new Date(`2000-01-01 ${b}`)
        return timeA - timeB
    })
})

// Split slots into afternoon and evening based on time
const afternoonSlots = computed(() => {
    return getSlotsForSelectedDate.value.filter(slot => {
        const hour = parseInt(slot.split(':')[0])
        const isPM = slot.includes('PM')
        const hour24 = isPM && hour !== 12 ? hour + 12 : (hour === 12 && !isPM ? 0 : hour)
        return hour24 >= 12 && hour24 < 17 // 12 PM to 5 PM
    })
})

const eveningSlots = computed(() => {
    return getSlotsForSelectedDate.value.filter(slot => {
        const hour = parseInt(slot.split(':')[0])
        const isPM = slot.includes('PM')
        const hour24 = isPM && hour !== 12 ? hour + 12 : (hour === 12 && !isPM ? 0 : hour)
        return hour24 >= 17 || hour24 < 12 // 5 PM onwards or morning slots
    })
})

const goBack = () => {
    router.visit(route('therapists.index'))
}

const closeBookingSlider = () => {
    showBookingSlider.value = false
    // Reset selections
    selectedPlatform.value = ''
    selectedDate.value = ''
    selectedTimeSlot.value = ''
}

const closePaymentSlider = () => {
    showPaymentSlider.value = false
    selectedPaymentGateway.value = ''
}

const closeCardDetailsSlider = () => {
    showCardDetailsSlider.value = false
    // Reset card details
    cardDetails.value = {
        holderName: '',
        number: '',
        expiry: '',
        cvv: ''
    }
    selectedCardType.value = 'mastercard'
}

const proceedToPayment = () => {
    if (selectedPlatform.value && selectedDate.value && selectedTimeSlot.value) {
        showBookingSlider.value = false
        showPaymentSlider.value = true
    }
}

const backToTimeSelection = () => {
    showPaymentSlider.value = false
    showBookingSlider.value = true
    selectedPaymentGateway.value = ''
}

const backToPaymentSelection = () => {
    showCardDetailsSlider.value = false
    showPaymentSlider.value = true
}

const proceedWithSelectedGateway = () => {
    if (!selectedPaymentGateway.value) return
    
    // Both Monnify and Paystack redirect to their hosted payment pages
    processPayment()
}

// Card formatting functions
const formatCardNumber = (event) => {
    let value = event.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '')
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value
    cardDetails.value.number = formattedValue
}

const formatExpiry = (event) => {
    let value = event.target.value.replace(/[^0-9]/g, '')
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4)
    }
    cardDetails.value.expiry = value
}

// Computed property to check if card details are valid
const isCardDetailsValid = computed(() => {
    return cardDetails.value.holderName.trim() !== '' &&
           cardDetails.value.number.replace(/\s/g, '').length >= 15 &&
           cardDetails.value.expiry.length === 5 &&
           cardDetails.value.cvv.length >= 3
})

// Function to handle payment processing
const processPayment = async () => {
    isProcessingPayment.value = true

    try {
        // Prepare payment data
        const appointmentDateTime = convertToBookingDate(selectedDate.value) + ' ' + selectedTimeSlot.value
        
        const paymentData = {
            therapist_id: props.therapist.id,
            appointment_datetime: appointmentDateTime,
            session_type: 'online',
            platform: selectedPlatform.value,
            user_message: '',
            payment_gateway: selectedPaymentGateway.value
        }

        // No card details needed - both gateways handle payment on their secure pages

        // Make AJAX request to initialize payment and get redirect URL
        const response = await axios.post(route('payment.therapist.initialize'), {
            therapist_id: props.therapist.id,
            booking_date: convertToBookingDate(selectedDate.value),
            booking_time: selectedTimeSlot.value,
            notes: '',
            platform: selectedPlatform.value,
            payment_gateway: selectedPaymentGateway.value
        })

        if (response.data.status && response.data.authorization_url) {
            // Redirect to payment gateway
            window.location.href = response.data.authorization_url
        } else {
            alert('Payment initialization failed: ' + (response.data.message || 'Unknown error'))
        }
    } catch (error) {
        console.error('Payment error:', error)
        if (error.response && error.response.data && error.response.data.message) {
            alert('Payment error: ' + error.response.data.message)
        } else {
            alert('An error occurred while processing payment. Please try again.')
        }
    } finally {
        isProcessingPayment.value = false
    }
}

// Convert display date to booking date format
const convertToBookingDate = (displayDate) => {
    // Find the corresponding date object
    const dateObj = availableDates.value.find(date => date.value === displayDate);
    if (!dateObj) return '';
    
    // Create a proper date string
    const today = new Date();
    const targetDate = new Date(today);
    
    // Find the day index
    const dayIndex = availableDates.value.findIndex(date => date.value === displayDate);
    targetDate.setDate(today.getDate() + dayIndex);
    
    return targetDate.toISOString().split('T')[0]; // Format: YYYY-MM-DD
};

// Check for payment success on page load
onMounted(() => {
    if (page.props.flash?.payment_success) {
        showPaymentSuccessModal.value = true;
    }
});

const closePaymentSuccessModal = () => {
    showPaymentSuccessModal.value = false;
};
</script> 