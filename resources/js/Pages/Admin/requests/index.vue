<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Check, X, Mail, User, Building2, Clock } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

// On définit une valeur par défaut vide pour éviter l'erreur .length
const props = defineProps({
    requests: {
        type: Array,
        default: () => []
    }
});

const handleAction = (id, action) => {
    router.post(route('admin.requests.action', { id, action }));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-black uppercase tracking-tight text-zinc-900 dark:text-white">
                Demandes d'adhésion
            </h2>
        </template>

        <div class="py-12 px-4">
            <div class="max-w-5xl mx-auto space-y-4">

                <div v-for="request in requests" :key="request.id"
                     class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-[2rem] flex flex-col md:flex-row justify-between items-center gap-6 shadow-sm">

                </div>

                <div v-if="requests.length === 0" class="text-center py-20 bg-zinc-50 dark:bg-zinc-900/50 rounded-[3rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                    <Clock class="w-12 h-12 text-zinc-300 mx-auto mb-4" />
                    <p class="text-zinc-500 font-bold uppercase tracking-widest text-sm">Aucune demande en attente</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
