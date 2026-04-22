<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateAddressForm from './Partials/UpdateAddressForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object, // Pour passer l'adresse au formulaire
});
</script>

<template>
    <Head title="Mon Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Paramètres du compte
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">

                <div class="bg-white dark:bg-zinc-900 p-6 sm:p-10 shadow-sm sm:rounded-3xl border border-gray-100 dark:border-zinc-800">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>
                <div class="bg-gray-800 text-green-400 p-2 text-xs font-mono rounded">
                    DEBUG: {{ user?.address?.type || 'type non trouvée' }}
                    DEBUG: {{ user?.address?.street || 'Rue non trouvée' }}
                    DEBUG: {{ user?.address?.number || 'number non trouvée' }}
                    DEBUG: {{ user?.address?.box || 'box non trouvée' }}
                    DEBUG: {{ user?.address?.postal_code || 'postal_code non trouvée' }}
                    DEBUG: {{ user?.address?.city || 'city non trouvée' }}
                    DEBUG: {{ user?.address?.country || 'country non trouvée' }}
                    DEBUG: {{ user?.address?.id || 'id non trouvée' }}

                </div>
                <div class="bg-white dark:bg-zinc-900 p-6 sm:p-10 shadow-sm sm:rounded-3xl border border-gray-100 dark:border-zinc-800">
                    <UpdateAddressForm :address="user.address" class="max-w-xl" />
                </div>
                <div class="bg-gray-800 text-green-400 p-2 text-xs font-mono rounded">
                    DEBUG: {{ user?.address?.type || 'type non trouvée' }}
                    DEBUG: {{ user?.address?.street || 'Rue non trouvée' }}
                    DEBUG: {{ user?.address?.number || 'number non trouvée' }}
                    DEBUG: {{ user?.address?.box || 'box non trouvée' }}
                    DEBUG: {{ user?.address?.postal_code || 'postal_code non trouvée' }}
                    DEBUG: {{ user?.address?.city || 'city non trouvée' }}
                    DEBUG: {{ user?.address?.country || 'country non trouvée' }}
                    DEBUG: {{ user?.address?.id || 'id non trouvée' }}

                </div>

                <div class="bg-white dark:bg-zinc-900 p-6 sm:p-10 shadow-sm sm:rounded-3xl border border-gray-100 dark:border-zinc-800">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-red-50/30 dark:bg-red-900/10 p-6 sm:p-10 shadow-sm sm:rounded-3xl border border-red-100 dark:border-red-900/20">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
