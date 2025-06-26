<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const props = defineProps({
    admin: Object,
});

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordRef = ref();

const updatePassword = () => {
    form.patch(route('admin.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordRef.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
            }
        },
    });
};
</script>

<template>
    <Head title="Admin Settings" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Admin Settings</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Admin Info Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Admin Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <p class="mt-1 text-sm text-gray-600">{{ admin.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-600">{{ admin.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Card -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Change Password</h3>
                        <form @submit.prevent="updatePassword" class="space-y-6">
                            <div>
                                <InputLabel for="current_password" value="Current Password" />
                                <TextInput
                                    id="current_password"
                                    v-model="form.current_password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="current-password"
                                />
                                <InputError :message="form.errors.current_password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password" value="New Password" />
                                <TextInput
                                    id="password"
                                    ref="passwordRef"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update Password</PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Password updated successfully.</p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Information -->
                <div class="mt-6 rounded-lg bg-blue-50 p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Security Tips</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Use a strong password with at least 8 characters</li>
                                    <li>Include uppercase and lowercase letters, numbers, and symbols</li>
                                    <li>Don't reuse passwords from other accounts</li>
                                    <li>Consider using a password manager</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 