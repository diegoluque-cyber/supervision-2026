<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 blur-2xl rounded-full"></div>
                <h2
                    class="relative text-xl font-semibold leading-tight text-gray-800"
                >
                    Profile
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div
                    class="relative group overflow-hidden rounded-2xl bg-white/80 backdrop-blur-xl p-6 shadow-xl transition-all duration-300 hover:shadow-2xl hover:bg-white"
                >
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/30 to-teal-500/30 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>
                </div>

                <div
                    class="relative group overflow-hidden rounded-2xl bg-white/80 backdrop-blur-xl p-6 shadow-xl transition-all duration-300 hover:shadow-2xl hover:bg-white"
                >
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500/30 to-cyan-500/30 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>
                </div>

                <div
                    class="relative group overflow-hidden rounded-2xl bg-white/80 backdrop-blur-xl p-6 shadow-xl transition-all duration-300 hover:shadow-2xl hover:bg-white"
                >
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-red-500/30 to-pink-500/30 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
