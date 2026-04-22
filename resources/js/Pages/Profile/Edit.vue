<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateAddressForm from './Partials/UpdateAddressForm.vue';
import UpdateSocialLinksForm from "./Partials/UpdateSocialLinksForm.vue";
import { Head } from '@inertiajs/vue3';
import ThemeSelector from "@/Components/ThemeSelector.vue";

// On déclare toutes les props envoyées par le ProfileController
defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
    social_platforms: Array, // Ajouté ici pour résoudre l'erreur "Unresolved variable"
});
</script>

<template>
    <Head title="Mon Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-black uppercase tracking-tight text-gray-800 dark:text-gray-200 leading-tight">
                Paramètres du compte
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">

                <div class="bg-white dark:bg-zinc-900 p-6 sm:p-10 shadow-sm sm:rounded-[2.5rem] border border-gray-100 dark:border-zinc-800">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-8 bg-white dark:bg-zinc-900 shadow-sm border border-zinc-100 dark:border-zinc-800 rounded-[2.5rem]">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl text-indigo-600">
                            <Palette class="w-5 h-5" />
                        </div>
                        <h3 class="text-md font-black uppercase tracking-widest text-zinc-800 dark:text-zinc-100">Apparence de la plateforme</h3>
                    </div>

                    <ThemeSelector />
                </div>

                <div class="bg-white dark:bg-zinc-900 p-6 sm:p-10 shadow-sm sm:rounded-[2.5rem] border border-gray-100 dark:border-zinc-800">
                    <UpdateSocialLinksForm
                        :user="user"
                        :social_platforms="social_platforms"
                    />
                </div>

                <div class="bg-white dark:bg-zinc-900 p-6 sm:p-10 shadow-sm sm:rounded-[2.5rem] border border-gray-100 dark:border-zinc-800">
                    <UpdateAddressForm :address="user.address" class="max-w-xl" />

                    <div v-if="user?.address" class="mt-6 bg-gray-800 text-green-400 p-3 text-[10px] font-mono rounded-xl opacity-50">
                        DEBUG ADRESSE: {{ user.address.street }} {{ user.address.number }}, {{ user.address.city }} (ID: {{ user.address.id }})
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 p-6 sm:p-10 shadow-sm sm:rounded-[2.5rem] border border-gray-100 dark:border-zinc-800">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-red-50/30 dark:bg-red-900/10 p-6 sm:p-10 shadow-sm sm:rounded-[2.5rem] border border-red-100 dark:border-red-900/20">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
