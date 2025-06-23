<script setup>
    import { Head } from '@inertiajs/vue3';
    import { ref, computed, onMounted, onUnmounted } from 'vue';
    import { router } from '@inertiajs/vue3';
    import Sidebar from '@/Components/Sidebar.vue';
    import ProfileHeader from '@/Components/ProfileHeader.vue';
    import { Link } from '@inertiajs/vue3';

    const props = defineProps({
        auth: Object,
        user: Object,
    });

    // Mobile menu state
    const isMobileMenuOpen = ref(false);

    // Toggle mobile menu
    const toggleMobileMenu = () => {
        isMobileMenuOpen.value = !isMobileMenuOpen.value;

        // Prevent body scrolling when menu is open
        if (isMobileMenuOpen.value) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Close mobile menu when clicking outside
    const closeMobileMenu = e => {
        if (isMobileMenuOpen.value && !e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-toggle')) {
            isMobileMenuOpen.value = false;
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Add click event listener when component is mounted
    onMounted(() => {
        document.addEventListener('click', closeMobileMenu);
    });

    // Remove event listener when component is unmounted
    onUnmounted(() => {
        document.removeEventListener('click', closeMobileMenu);
        document.body.classList.remove('overflow-hidden');
    });

    // Active tab state
    const activeTab = ref('profile');

    // Track which sections are currently being edited
    const editingSections = ref({
        basicInfo: false,
        personal: false,
        appearance: false,
        lifestyle: false,
        background: false,
        about: false,
        overview: false,
        partnerPreferences: false,
        others: false,
    });

    // Add success/error message state
    const successMessage = ref('');
    const errorMessage = ref('');

    // Initialize userData with user data from props (with fallbacks for missing data)
    const userData = ref(
        props.user
            ? {
                  name: props.user.name || '',
                  country: props.user.country || '',
                  state: props.user.state || '',
                  city: props.user.city || '',
                  profile_photo: props.user.profile_photo || '/images/placeholder.jpg',
                  user: {
                      name: props.user.name || '',
                      gender: props.user.gender || '',
                      dob_day: props.user.dob_day || '',
                      dob_month: props.user.dob_month || '',
                      dob_year: props.user.dob_year || '',
                      country: props.user.country || '',
                      state: props.user.state || '',
                      city: props.user.city || '',
                  },
                  appearance: props.user.appearance || {},
                  lifestyle: props.user.lifestyle || {},
                  background: props.user.background || {},
                  about: props.user.about || {},
                  overview: props.user.overview || {},
                  others: props.user.others || {},
              }
            : {
                  name: '',
                  country: '',
                  state: '',
                  city: '',
                  profile_photo: '/images/placeholder.jpg',
                  user: {
                      name: '',
                      gender: '',
                      dob_day: '',
                      dob_month: '',
                      dob_year: '',
                      country: '',
                      state: '',
                      city: '',
                  },
                  appearance: {},
                  lifestyle: {},
                  background: {},
                  about: {},
                  overview: {},
                  others: {},
              }
    );

    // Function to change active tab
    const setActiveTab = tab => {
        activeTab.value = tab;
    };

    // Function to toggle edit mode for a section
    const toggleEditMode = section => {
        // Close any other open edit sections
        Object.keys(editingSections.value).forEach(key => {
            if (key !== section) editingSections.value[key] = false;
        });

        // Toggle the requested section
        editingSections.value[section] = !editingSections.value[section];
    };

    // Function to save changes for a specific section
    const saveSection = section => {
        const dataToUpdate = {};

        switch (section) {
            case 'basicInfo':
                dataToUpdate.name = userData.value.name;
                dataToUpdate.city = userData.value.city;
                dataToUpdate.state = userData.value.state;
                dataToUpdate.country = userData.value.country;

                break;
            case 'appearance':
                dataToUpdate.appearance = userData.value.appearance;
                break;
            case 'lifestyle':
                dataToUpdate.lifestyle = userData.value.lifestyle;
                break;
            case 'background':
                dataToUpdate.background = userData.value.background;
                break;
            case 'personal':
                dataToUpdate.name = userData.value.user.name;
                dataToUpdate.gender = userData.value.user.gender;
                dataToUpdate.dob_day = userData.value.user.dob_day;
                dataToUpdate.dob_month = userData.value.user.dob_month;
                dataToUpdate.dob_year = userData.value.user.dob_year;
                dataToUpdate.city = userData.value.user.city;
                dataToUpdate.state = userData.value.user.state;
                dataToUpdate.country = userData.value.user.country;
                break;
            case 'about':
                dataToUpdate.about = userData.value.about;
                break;
            case 'overview':
                dataToUpdate.overview = userData.value.overview;
                break;
            case 'partnerPreferences':
                dataToUpdate.about = userData.value.about;
                break;
            case 'others':
                dataToUpdate.others = userData.value.others;
                break;
        }

        // Immediately set edit mode to false
        editingSections.value[section] = false;

        // Show a saving message
        successMessage.value = 'Saving changes...';

        // Use Fetch API directly for more control
        fetch('/profile/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(dataToUpdate),
        })
            .then(response => response.json())
            .then(data => {


                // Set success message
                successMessage.value = 'Changes saved successfully!';

                // Give time for the user to see the success message, then reload
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(error => {
                console.error('Save error:', error);
                errorMessage.value = 'Failed to save changes: ' + error.message;
                setTimeout(() => {
                    errorMessage.value = '';
                }, 3000);

                // If there's an error, allow editing again
                editingSections.value[section] = true;
            });
    };

    // Function to cancel editing and revert changes
    const cancelEdit = section => {
        // Reset to original values from props
        switch (section) {
            case 'basicInfo':
                userData.value.name = props.user?.name || '';
                userData.value.city = props.user?.city || '';
                userData.value.state = props.user?.state || '';
                userData.value.country = props.user?.country || '';
                break;
            case 'appearance':
                userData.value.appearance = { ...(props.user?.appearance || {}) };
                break;
            case 'lifestyle':
                userData.value.lifestyle = { ...(props.user?.lifestyle || {}) };
                break;
            case 'background':
                userData.value.background = { ...(props.user?.background || {}) };
                break;
            case 'personal':
                userData.value.user.name = props.user?.name || '';
                userData.value.user.gender = props.user?.gender || '';
                userData.value.user.dob_day = props.user?.dob_day || '';
                userData.value.user.dob_month = props.user?.dob_month || '';
                userData.value.user.dob_year = props.user?.dob_year || '';
                userData.value.user.city = props.user?.city || '';
                userData.value.user.state = props.user?.state || '';
                userData.value.user.country = props.user?.country || '';
                break;
            case 'about':
                userData.value.about = { ...(props.user?.about || {}) };
                break;
            case 'overview':
                userData.value.overview = { ...(props.user?.overview || {}) };
                break;
            case 'partnerPreferences':
                userData.value.about = { ...(props.user?.about || {}) };
                break;
            case 'others':
                userData.value.others = { ...(props.user?.others || {}) };
                break;
        }

        // Close edit mode
        editingSections.value[section] = false;
    };

    // Function to update profile photo
    const updateProfilePhoto = event => {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('profile_photo', file);

        // Use direct URL instead of named route
        router.post('/profile/photo-update', formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: page => {
                // Update the profile photo in the UI immediately using the page props
                if (page.props.flash?.user && page.props.flash.user.profile_photo) {
                    userData.value.profile_photo = page.props.flash.user.profile_photo;
                } else {
                    // Create a temporary URL for the uploaded file to display immediately
                    userData.value.profile_photo = URL.createObjectURL(file);
                }
                // Use flash message from the backend
                successMessage.value = page.props.flash?.success || 'Photo updated successfully';
                setTimeout(() => {
                    successMessage.value = '';
                }, 3000);
            },
            onError: errors => {
                errorMessage.value = 'Failed to update photo: ' + (Object.values(errors)[0] || 'Unknown error');
                setTimeout(() => {
                    errorMessage.value = '';
                }, 3000);
            },
        });
    };

    // Function to generate year range for date of birth
    const getYearRange = () => {
        const currentYear = new Date().getFullYear();
        const startYear = currentYear - 70; // 70 years ago
        const endYear = currentYear - 18; // 18 years ago (minimum age)
        const years = [];

        for (let year = endYear; year >= startYear; year--) {
            years.push(year);
        }

        return years;
    };
</script>

<template>
    <Head title="My Profile" />

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 relative">
        <!-- Mobile header with hamburger menu - Only visible on mobile -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-4 flex items-center md:hidden">
            <button @click="toggleMobileMenu" class="mobile-menu-toggle p-1 mr-3" aria-label="Toggle menu">
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg
                    class="h-6 w-6 text-gray-700"
                    :class="{ hidden: !isMobileMenuOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Page title on mobile -->
            <h1 class="text-lg font-bold">My Profile</h1>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Left Sidebar Component - Fixed position -->
        <aside
            class="mobile-menu fixed inset-y-0 left-0 w-64 transform transition-transform duration-300 ease-in-out z-50 md:translate-x-0"
            :class="{ 'translate-x-0': isMobileMenuOpen, '-translate-x-full': !isMobileMenuOpen }"
        >
            <Sidebar :user="$page.props.auth.user" />
        </aside>

        <!-- Main Content - Add left margin on desktop to account for fixed sidebar -->
        <div class="flex-1 px-4 py-4 md:p-8 mt-16 md:mt-0 md:ml-64">
            <div class="container mx-auto max-w-6xl">
                <!-- Profile Header Component - Only visible on desktop -->
                <ProfileHeader :user="userData" activeTab="profile" class="hidden md:block" />

                <!-- Mobile Profile Navigation - Only visible on mobile -->
                <div class="md:hidden mb-4 overflow-x-auto">
                    <div class="flex rounded-lg shadow-sm">
                        <Link
                            :href="route('me.profile')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium bg-primary-dark text-white"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            Profile
                        </Link>

                        <Link
                            :href="route('me.photos')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            Photos
                        </Link>

                        <Link
                            :href="route('me.hobbies')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            Hobbies
                        </Link>

                        <Link
                            :href="route('me.personality')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                />
                            </svg>
                            Personality
                        </Link>

                        <Link
                            :href="route('me.faqs')"
                            class="py-3 px-4 flex items-center gap-1 whitespace-nowrap font-medium text-gray-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            FAQs
                        </Link>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <div
                    v-if="successMessage"
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"
                >
                    {{ successMessage }}
                </div>
                <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ errorMessage }}
                </div>

                <!-- Profile Content Section -->
                <div v-if="activeTab === 'profile'">
                    <!-- Profile Picture Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-8">
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-4">
                                <div class="text-center">
                                    <div class="relative inline-block">
                                        <div class="aspect-square w-48 mx-auto rounded-2xl overflow-hidden shadow-md">
                                            <img
                                                :src="userData.profile_photo"
                                                alt="Profile"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <label
                                            class="absolute -bottom-2 -right-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-3 shadow-lg cursor-pointer transition-all duration-200 hover:scale-105"
                                        >
                                            <input
                                                type="file"
                                                class="hidden"
                                                accept="image/*"
                                                @change="updateProfilePhoto"
                                            />
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                            </svg>
                                        </label>
                                    </div>
                                    <div class="mt-4">
                                        <h2 class="text-xl font-bold text-gray-900">{{ userData.name }}</h2>
                                        <p class="text-gray-500 text-sm mt-1">
                                            {{
                                                [userData.city, userData.state, userData.country]
                                                    .filter(Boolean)
                                                    .join(', ')
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Info Section -->
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <!-- Personal Information Section -->
                            <div
                                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full relative flex flex-col"
                            >
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                        Personal Information
                                    </h3>
                                </div>
                                <div class="p-6 flex-1">
                                    <div v-if="!editingSections.personal" class="space-y-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Full Name</p>
                                            <p class="font-medium">{{ userData.user.name }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500">Gender</p>
                                            <p class="font-medium">{{ userData.user.gender }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500">Date of Birth</p>
                                            <p class="font-medium">
                                                {{ userData.user.dob_day }} {{ userData.user.dob_month }},
                                                {{ userData.user.dob_year }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500">Location</p>
                                            <p class="font-medium">
                                                {{ userData.user.city }}, {{ userData.user.state }},
                                                {{ userData.user.country }}
                                            </p>
                                        </div>
                                    </div>

                                    <div v-else class="space-y-4">
                                        <div>
                                            <label class="text-sm text-gray-500">Full Name</label>
                                            <input
                                                type="text"
                                                v-model="userData.user.name"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            />
                                        </div>

                                        <div>
                                            <label class="text-sm text-gray-500">Gender</label>
                                            <select
                                                v-model="userData.user.gender"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            >
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-3 gap-3">
                                            <div>
                                                <label class="text-sm text-gray-500">Day</label>
                                                <select
                                                    v-model="userData.user.dob_day"
                                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                                >
                                                    <option v-for="day in 31" :key="day" :value="day">{{ day }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-sm text-gray-500">Month</label>
                                                <select
                                                    v-model="userData.user.dob_month"
                                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                                >
                                                    <option value="Jan">January</option>
                                                    <option value="Feb">February</option>
                                                    <option value="Mar">March</option>
                                                    <option value="Apr">April</option>
                                                    <option value="May">May</option>
                                                    <option value="Jun">June</option>
                                                    <option value="Jul">July</option>
                                                    <option value="Aug">August</option>
                                                    <option value="Sep">September</option>
                                                    <option value="Oct">October</option>
                                                    <option value="Nov">November</option>
                                                    <option value="Dec">December</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-sm text-gray-500">Year</label>
                                                <select
                                                    v-model="userData.user.dob_year"
                                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                                >
                                                    <option v-for="year in getYearRange()" :key="year" :value="year">
                                                        {{ year }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-3">
                                            <div>
                                                <label class="text-sm text-gray-500">Country</label>
                                                <input
                                                    type="text"
                                                    v-model="userData.user.country"
                                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                                />
                                            </div>
                                            <div>
                                                <label class="text-sm text-gray-500">State</label>
                                                <input
                                                    type="text"
                                                    v-model="userData.user.state"
                                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                                />
                                            </div>
                                            <div>
                                                <label class="text-sm text-gray-500">City</label>
                                                <input
                                                    type="text"
                                                    v-model="userData.user.city"
                                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                                />
                                            </div>
                                        </div>

                                        <div class="flex space-x-3">
                                            <button
                                                @click="saveSection('personal')"
                                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                            >
                                                Save
                                            </button>
                                            <button
                                                @click="cancelEdit('personal')"
                                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                
                            </div>
                            <!-- Appearance Section -->
                            <div
                                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full relative flex flex-col"
                            >
                                <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                        Appearance
                                    </h3>
                                </div>
                                <div class="p-6 flex-1">
                                    <div v-if="!editingSections.appearance" class="space-y-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Hair Color</p>
                                            <p class="font-medium">{{ userData.appearance.hair_color }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500">Eye Color</p>
                                            <p class="font-medium">{{ userData.appearance.eye_color }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500">Height</p>
                                            <p class="font-medium">{{ userData.appearance.height }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500">Weight</p>
                                            <p class="font-medium">{{ userData.appearance.weight }}</p>
                                        </div>
                                    </div>

                                    <div v-else class="space-y-4">
                                        <div>
                                            <label class="text-sm text-gray-500">Hair Color</label>
                                            <select
                                                v-model="userData.appearance.hair_color"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            >
                                                <option value="">Select Hair Color</option>
                                                <option value="Black">Black</option>
                                                <option value="Dark Brown">Dark Brown</option>
                                                <option value="Brown">Brown</option>
                                                <option value="Light Brown">Light Brown</option>
                                                <option value="Blonde">Blonde</option>
                                                <option value="Red">Red</option>
                                                <option value="Gray">Gray</option>
                                                <option value="White">White</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-sm text-gray-500">Eye Color</label>
                                            <select
                                                v-model="userData.appearance.eye_color"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            >
                                                <option value="">Select Eye Color</option>
                                                <option value="Brown">Brown</option>
                                                <option value="Dark Brown">Dark Brown</option>
                                                <option value="Black">Black</option>
                                                <option value="Blue">Blue</option>
                                                <option value="Green">Green</option>
                                                <option value="Gray">Gray</option>
                                                <option value="Hazel">Hazel</option>
                                                <option value="Amber">Amber</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-sm text-gray-500">Height (in cm)</label>
                                            <select
                                                v-model="userData.appearance.height"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            >
                                                <option value="">Select Height</option>
                                                <option
                                                    v-for="h in Array.from({ length: 73 }, (_, i) => 140 + i + ' cm')"
                                                    :key="h"
                                                    :value="h"
                                                >
                                                    {{ h }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-sm text-gray-500">Weight (in kg)</label>
                                            <input
                                                type="text"
                                                v-model="userData.appearance.weight"
                                                placeholder="Enter weight in kg"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            />
                                        </div>

                                        <div class="flex space-x-3">
                                            <button
                                                @click="saveSection('appearance')"
                                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                            >
                                                Save
                                            </button>
                                            <button
                                                @click="cancelEdit('appearance')"
                                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    v-if="!editingSections.appearance"
                                    @click="toggleEditMode('appearance')"
                                    class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Lifestyle Section -->
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full relative flex flex-col"
                        >
                            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4">
                                <h3 class="text-lg font-semibold text-white flex items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    Life Style
                                </h3>
                            </div>
                            <div class="p-6 flex-1">
                                <div v-if="!editingSections.lifestyle" class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Do you drink?</p>
                                        <p class="font-medium">{{ userData.lifestyle.drinks }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Do you smoke?</p>
                                        <p class="font-medium">{{ userData.lifestyle.smokes }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Do you have children?</p>
                                        <p class="font-medium">{{ userData.lifestyle.has_children }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Number of children?</p>
                                        <p class="font-medium">{{ userData.lifestyle.number_of_children }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Do you want to have children?</p>
                                        <p class="font-medium">{{ userData.lifestyle.want_children }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Occupation?</p>
                                        <p class="font-medium">{{ userData.lifestyle.occupation }}</p>
                                    </div>
                                </div>

                                <div v-else class="space-y-4">
                                    <div>
                                        <label class="text-sm text-gray-500">Do you drink?</label>
                                        <select
                                            v-model="userData.lifestyle.drinks"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                            <option value="Occasionally">Occasionally</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Do you smoke?</label>
                                        <select
                                            v-model="userData.lifestyle.smokes"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                            <option value="Occasionally">Occasionally</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Do you have children?</label>
                                        <select
                                            v-model="userData.lifestyle.has_children"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="No">No</option>
                                            <option value="Yes – living with me">Yes – living with me</option>
                                            <option value="Yes – not living with me">Yes – not living with me</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Number of children?</label>
                                        <select
                                            v-model="userData.lifestyle.number_of_children"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select number</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4+">4+</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Do you want to have children?</label>
                                        <select
                                            v-model="userData.lifestyle.want_children"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select preference</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                            <option value="Maybe">Maybe</option>
                                            <option value="Undecided">Undecided</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Occupation?</label>
                                        <input
                                            type="text"
                                            v-model="userData.lifestyle.occupation"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        />
                                    </div>

                                    <div class="flex space-x-3">
                                        <button
                                            @click="saveSection('lifestyle')"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                        >
                                            Save
                                        </button>
                                        <button
                                            @click="cancelEdit('lifestyle')"
                                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button
                                v-if="!editingSections.lifestyle"
                                @click="toggleEditMode('lifestyle')"
                                class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Overview Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden relative mb-6">
                        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 mr-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                Overview
                            </h3>
                        </div>
                        <div class="p-6">
                            <div v-if="!editingSections.overview" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Education</p>
                                        <p class="font-medium">{{ userData.overview?.education_level }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Employment Status</p>
                                        <p class="font-medium">{{ userData.overview?.employment_status }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Annual Income</p>
                                        <p class="font-medium">{{ userData.overview?.income_range }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Religion</p>
                                        <p class="font-medium">{{ userData.overview?.religion }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Marital Status</p>
                                        <p class="font-medium">{{ userData.overview?.marital_status }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-500">Education</label>
                                        <select
                                            v-model="userData.overview.education_level"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select Education Level</option>
                                            <option value="High School">High School</option>
                                            <option value="Some College">Some College</option>
                                            <option value="Associate's Degree">Associate's Degree</option>
                                            <option value="Bachelor's Degree">Bachelor's Degree</option>
                                            <option value="Master's Degree">Master's Degree</option>
                                            <option value="Doctorate">Doctorate</option>
                                            <option value="Professional Degree">Professional Degree</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Employment Status</label>
                                        <select
                                            v-model="userData.overview.employment_status"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select Employment Status</option>
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Self-employed">Self-employed</option>
                                            <option value="Freelance">Freelance</option>
                                            <option value="Student">Student</option>
                                            <option value="Retired">Retired</option>
                                            <option value="Not employed">Not employed</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Annual Income</label>
                                        <select
                                            v-model="userData.overview.income_range"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select Income Range</option>
                                            <option value="Less than ₦2,400,000">Less than ₦2,400,000</option>
                                            <option value="₦2,400,000 - ₦4,000,000">₦2,400,000 - ₦4,000,000</option>
                                            <option value="₦4,000,000 - ₦6,000,000">₦4,000,000 - ₦6,000,000</option>
                                            <option value="₦6,000,000 - ₦8,000,000">₦6,000,000 - ₦8,000,000</option>
                                            <option value="₦8,000,000 - ₦12,000,000">₦8,000,000 - ₦12,000,000</option>
                                            <option value="More than ₦12,000,000">More than ₦12,000,000</option>
                                            <option value="Prefer not to say">Prefer not to say</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Religion</label>
                                        <select
                                            v-model="userData.overview.religion"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select Religion</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Islam - Sunni">Islam - Sunni</option>
                                            <option value="Islam - Shia">Islam - Shia</option>
                                            <option value="Islam - Sufi">Islam - Sufi</option>
                                            <option value="Islam - Other">Islam - Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Marital Status</label>
                                        <select
                                            v-model="userData.overview.marital_status"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select Marital Status</option>
                                            <option value="Single, never married">Single, never married</option>
                                            <option value="Divorced">Divorced</option>
                                            <option value="Widowed">Widowed</option>
                                            <option value="Separated">Separated</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex space-x-3">
                                    <button
                                        @click="saveSection('overview')"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    >
                                        Save
                                    </button>
                                    <button
                                        @click="cancelEdit('overview')"
                                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="!editingSections.overview"
                            @click="toggleEditMode('overview')"
                            class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                />
                            </svg>
                        </button>
                    </div>
                    <!-- Three Column Info Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 items-start">
                        <!-- Background / Culture Section -->
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full relative flex flex-col"
                        >
                            <div class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4">
                                <h3 class="text-lg font-semibold text-white flex items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    Background & Culture
                                </h3>
                            </div>
                            <div class="p-6 flex-1">
                                <div v-if="!editingSections.background" class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Nationality</p>
                                        <p class="font-medium">{{ userData.background.nationality }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Ethnic Group / Tribe</p>
                                        <p class="font-medium">{{ userData.background.ethnic_group }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Islamic Affiliation</p>
                                        <p class="font-medium">{{ userData.background.islamic_affiliation }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Education</p>
                                        <p class="font-medium">{{ userData.background.education }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Language spoken</p>
                                        <p class="font-medium">{{ userData.background.language_spoken }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Born / Reverted</p>
                                        <p class="font-medium">{{ userData.background.born_reverted }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Read Quran</p>
                                        <p class="font-medium">{{ userData.background.read_quran }}</p>
                                    </div>
                                </div>

                                <div v-else class="space-y-4">
                                    <div>
                                        <label class="text-sm text-gray-500">Nationality</label>
                                        <input
                                            type="text"
                                            v-model="userData.background.nationality"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        />
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Ethnic Group / Tribe</label>
                                        <select
                                            v-model="userData.background.ethnic_group"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select Ethnic Group</option>
                                            <option value="Hausa">Hausa</option>
                                            <option value="Yoruba">Yoruba</option>
                                            <option value="Igbo">Igbo</option>
                                            <option value="Fulani">Fulani</option>
                                            <option value="Kanuri">Kanuri</option>
                                            <option value="Tiv">Tiv</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <input
                                            v-if="userData.background.ethnic_group === 'Other'"
                                            type="text"
                                            v-model="userData.background.ethnic_group_other"
                                            placeholder="Please specify"
                                            class="w-full mt-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        />
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">
                                            Islamic Affiliation (Dariqa or Sect)
                                        </label>
                                        <select
                                            v-model="userData.background.islamic_affiliation"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select Islamic Affiliation</option>
                                            <option value="Sunni">Sunni</option>
                                            <option value="Shia">Shia</option>
                                            <option value="Tijaniyya">Tijaniyya</option>
                                            <option value="Qadiriyya">Qadiriyya</option>
                                            <option value="Izala">Izala</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <input
                                            v-if="userData.background.islamic_affiliation === 'Other'"
                                            type="text"
                                            v-model="userData.background.islamic_affiliation_other"
                                            placeholder="Please specify"
                                            class="w-full mt-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        />
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Education</label>
                                        <input
                                            type="text"
                                            v-model="userData.background.education"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        />
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Language spoken</label>
                                        <input
                                            type="text"
                                            v-model="userData.background.language_spoken"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        />
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Born / Reverted</label>
                                        <select
                                            v-model="userData.background.born_reverted"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="Born Muslim">Born Muslim</option>
                                            <option value="Reverted">Reverted</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Read Quran</label>
                                        <select
                                            v-model="userData.background.read_quran"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                            <option value="Learning">Learning</option>
                                        </select>
                                    </div>

                                    <div class="flex space-x-3">
                                        <button
                                            @click="saveSection('background')"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                        >
                                            Save
                                        </button>
                                        <button
                                            @click="cancelEdit('background')"
                                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button
                                v-if="!editingSections.background"
                                @click="toggleEditMode('background')"
                                class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    />
                                </svg>
                            </button>
                        </div>
                        <!-- Partner Preferences Section -->
                    <div class="bg-white h-full rounded-xl shadow-sm border border-gray-100 overflow-hidden relative mb-6">
                        <div class="bg-gradient-to-r from-pink-500 to-rose-600 px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 mr-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                    />
                                </svg>
                                What I'm Looking For
                            </h3>
                        </div>
                        <div class="p-6">
                            <div v-if="!editingSections.partnerPreferences" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Age Range</p>
                                        <p class="font-medium">
                                            {{
                                                userData.about.looking_for_age_min && userData.about.looking_for_age_max
                                                    ? `${userData.about.looking_for_age_min} - ${userData.about.looking_for_age_max} years`
                                                    : 'Any age'
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Education Level</p>
                                        <p class="font-medium">{{ userData.about.looking_for_education || 'Any' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Religious Practice</p>
                                        <p class="font-medium">
                                            {{ userData.about.looking_for_religious_practice || 'Any' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Marital Status</p>
                                        <p class="font-medium">
                                            {{ userData.about.looking_for_marital_status || 'Any' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Open to relocating for marriage?</p>
                                        <p class="font-medium">{{ userData.about.looking_for_relocate || 'Not specified' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Want to have children?</p>
                                        <p class="font-medium">{{ userData.about.looking_for_children || 'Not specified' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Preferred Tribe or Ethnic Group</p>
                                        <p class="font-medium">{{ userData.about.looking_for_tribe || 'Open to all' }}</p>
                                    </div>
                                </div>
                                <div v-if="userData.about.looking_for">
                                    <p class="text-sm text-gray-500 mb-1">Additional Preferences</p>
                                    <p class="font-medium">{{ userData.about.looking_for }}</p>
                                </div>
                            </div>

                            <div v-else class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-500">Age Range</label>
                                        <div class="flex items-center space-x-2">
                                            <select
                                                v-model="userData.about.looking_for_age_min"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            >
                                                <option value="">Min Age</option>
                                                <option
                                                    v-for="age in Array.from({ length: 43 }, (_, i) => i + 18)"
                                                    :key="age"
                                                    :value="age"
                                                >
                                                    {{ age }}
                                                </option>
                                            </select>
                                            <span>to</span>
                                            <select
                                                v-model="userData.about.looking_for_age_max"
                                                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                            >
                                                <option value="">Max Age</option>
                                                <option
                                                    v-for="age in Array.from({ length: 43 }, (_, i) => i + 18)"
                                                    :key="age"
                                                    :value="age"
                                                >
                                                    {{ age }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Education Level</label>
                                        <select
                                            v-model="userData.about.looking_for_education"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Any</option>
                                            <option value="High School">High School</option>
                                            <option value="Some College">Some College</option>
                                            <option value="Associate's Degree">Associate's Degree</option>
                                            <option value="Bachelor's Degree">Bachelor's Degree</option>
                                            <option value="Master's Degree">Master's Degree</option>
                                            <option value="Doctorate">Doctorate</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Religious Practice</label>
                                        <select
                                            v-model="userData.about.looking_for_religious_practice"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Any</option>
                                            <option value="Very Religious">Very Religious</option>
                                            <option value="Religious">Religious</option>
                                            <option value="Moderately Religious">Moderately Religious</option>
                                            <option value="Not Very Religious">Not Very Religious</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Marital Status</label>
                                        <select
                                            v-model="userData.about.looking_for_marital_status"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Any</option>
                                            <option value="Single, never married">Single, never married</option>
                                            <option value="Divorced">Divorced</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Open to relocating for marriage?</label>
                                        <select
                                            v-model="userData.about.looking_for_relocate"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select preference</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                            <option value="Maybe">Maybe</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Want to have children?</label>
                                        <select
                                            v-model="userData.about.looking_for_children"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select preference</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                            <option value="Maybe / Open to discuss">Maybe / Open to discuss</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-500">Preferred Tribe or Ethnic Group (optional)</label>
                                        <select
                                            v-model="userData.about.looking_for_tribe"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        >
                                            <option value="">Select preference</option>
                                            <option value="Hausa">Hausa</option>
                                            <option value="Yoruba">Yoruba</option>
                                            <option value="Igbo">Igbo</option>
                                            <option value="Fulani">Fulani</option>
                                            <option value="Kanuri">Kanuri</option>
                                            <option value="Open to all">Open to all</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-500">Additional Preferences (optional)</label>
                                    <textarea
                                        v-model="userData.about.looking_for"
                                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                        rows="3"
                                        placeholder="Any other preferences you have for a partner..."
                                    ></textarea>
                                </div>

                                <div class="flex space-x-3">
                                    <button
                                        @click="saveSection('partnerPreferences')"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    >
                                        Save
                                    </button>
                                    <button
                                        @click="cancelEdit('partnerPreferences')"
                                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="!editingSections.partnerPreferences"
                            @click="toggleEditMode('partnerPreferences')"
                            class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                />
                            </svg>
                        </button>
                    </div>
                                                 <!-- Others Section -->
                         <div
                             class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full relative flex flex-col"
                         >
                             <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                                 <h3 class="text-lg font-semibold text-white flex items-center">
                                     <svg
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5 mr-2"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                     >
                                         <path
                                             stroke-linecap="round"
                                             stroke-linejoin="round"
                                             stroke-width="2"
                                             d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                         />
                                     </svg>
                                     Others
                                 </h3>
                             </div>
                             <div class="p-6 flex-1">

                                 <div v-if="!editingSections.others" class="space-y-4">
                                     <div>
                                         <p class="text-sm text-gray-500">Are you open to polygamy?</p>
                                         <p class="font-medium">{{ userData.others?.open_to_polygamy }}</p>
                                     </div>

                                     <div>
                                         <p class="text-sm text-gray-500">Do you own your business or have financial stability?</p>
                                         <p class="font-medium">{{ userData.others?.financial_stability }}</p>
                                     </div>

                                     <div>
                                         <p class="text-sm text-gray-500">When are you ready to get married?</p>
                                         <p class="font-medium">{{ userData.others?.ready_to_marry }}</p>
                                     </div>

                                     <div>
                                         <p class="text-sm text-gray-500">Genotype</p>
                                         <p class="font-medium">{{ userData.others?.genotype }}</p>
                                     </div>
                                 </div>
                              
                                 <div v-else class="space-y-4">
                                     <div>
                                         <label class="text-sm text-gray-500">Are you open to polygamy?</label>
                                         <select
                                             v-model="userData.others.open_to_polygamy"
                                             class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                         >
                                             <option value="">Select preference</option>
                                             <option value="Yes">Yes</option>
                                             <option value="No">No</option>
                                             <option value="Maybe / Open to discuss">Maybe / Open to discuss</option>
                                         </select>
                                     </div>
                                     
                                     <div>
                                         <label class="text-sm text-gray-500">Do you own your business or have financial stability?</label>
                                         <select
                                             v-model="userData.others.financial_stability"
                                             class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                         >
                                             <option value="">Select option</option>
                                             <option value="Yes">Yes</option>
                                             <option value="No">No</option>
                                             <option value="Prefer not to say">Prefer not to say</option>
                                         </select>
                                     </div>
                                     
                                     <div>
                                         <label class="text-sm text-gray-500">When are you ready to get married?</label>
                                         <select
                                             v-model="userData.others.ready_to_marry"
                                             class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                         >
                                             <option value="">Select timeframe</option>
                                             <option value="Immediately">Immediately</option>
                                             <option value="In 3–6 months">In 3–6 months</option>
                                             <option value="In 6–12 months">In 6–12 months</option>
                                             <option value="Not sure yet">Not sure yet</option>
                                         </select>
                                     </div>
                                     
                                     <div>
                                         <label class="text-sm text-gray-500">Genotype</label>
                                         <select
                                             v-model="userData.others.genotype"
                                             class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                         >
                                             <option value="">Select genotype</option>
                                             <option value="AA">AA</option>
                                             <option value="AS">AS</option>
                                             <option value="SS">SS</option>
                                             <option value="AC">AC</option>
                                             <option value="SC">SC</option>
                                             <option value="I don't know">I don't know</option>
                                         </select>
                                     </div>
                                     
                                     <div class="flex space-x-3">
                                         <button
                                             @click="saveSection('others')"
                                             class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                         >
                                             Save
                                         </button>
                                         <button
                                             @click="cancelEdit('others')"
                                             class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                         >
                                             Cancel
                                         </button>
                                     </div>
                                 </div>
                             </div>

                             <button
                                 v-if="!editingSections.others"
                                 @click="toggleEditMode('others')"
                                 class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
                             >
                                 <svg
                                     xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                 >
                                     <path
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         stroke-width="2"
                                         d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                     />
                                 </svg>
                             </button>
                         </div>
                    </div>

                    
                </div>
                <!-- About Me Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden relative mb-6">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            More About Me
                        </h3>
                    </div>
                    <div class="p-6">
                        <div v-if="!editingSections.about" class="space-y-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Your profile heading</p>
                                <p class="font-medium">{{ userData.about.heading }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 mb-1">A little about yourself</p>
                                <p class="font-medium">{{ userData.about.about_me }}</p>
                            </div>
                        </div>

                        <div v-else class="space-y-6">
                            <div>
                                <label class="text-sm text-gray-500 mb-1">Your profile heading</label>
                                <input
                                    type="text"
                                    v-model="userData.about.heading"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                />
                            </div>

                            <div>
                                <label class="text-sm text-gray-500 mb-1">A little about yourself</label>
                                <textarea
                                    v-model="userData.about.about_me"
                                    class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                    rows="4"
                                ></textarea>
                            </div>

                            <div class="flex space-x-3">
                                <button
                                    @click="saveSection('about')"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Save
                                </button>
                                <button
                                    @click="cancelEdit('about')"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                    <button
                        v-if="!editingSections.about"
                        @click="toggleEditMode('about')"
                        class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Placeholder for other tabs -->
                <div v-if="activeTab === 'photos'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Photos</h3>
                    <p class="text-gray-500">Upload your photos here.</p>
                </div>

                <div v-if="activeTab === 'hobbies'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Hobbies & Interests</h3>
                    <p class="text-gray-500">Add your hobbies and interests here.</p>
                </div>

                <div v-if="activeTab === 'personality'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">Personality</h3>
                    <p class="text-gray-500">Describe your personality traits here.</p>
                </div>

                <div v-if="activeTab === 'faqs'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4">FAQs</h3>
                    <p class="text-gray-500">Frequently asked questions about your profile.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    /* Ensure proper stacking on mobile */
    @media (max-width: 768px) {
        .min-h-screen {
            padding-top: 1rem;
        }
    }

    /* Prevent scrolling when mobile menu is open */
    :global(.overflow-hidden) {
        overflow: hidden;
    }

    /* Form element styling */
    input[type='file'] {
        display: none;
    }

    select,
    input,
    textarea {
        font-size: 0.9rem;
    }

    button {
        transition: all 0.2s;
    }

    .edit-button:hover {
        transform: scale(1.1);
    }
</style>
