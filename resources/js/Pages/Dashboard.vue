<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm, Link} from '@inertiajs/vue3';
import {ref} from 'vue';

const props = defineProps({
    user: Object,
    orders: Array, // Injecté depuis la route dashboard
});

// --- LOGIQUE IMPORT PHOTO e-ID ---
const fileInput = ref(null);
const isDragging = ref(false);

const form = useForm({
    photo: null,
});

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        submitEidPhoto();
    }
};

const onDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        form.photo = file;
        submitEidPhoto();
    }
};

const submitEidPhoto = () => {
    form.post(route('identity.upload'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

// --- FORMATTAGE DATE ---
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
    });
};
</script>

<template>
    <Head title="Tableau de bord"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Mon Espace Personnel
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-2xl dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800">
                    <div class="p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <img v-if="user.profile_photo_url" :src="user.profile_photo_url"
                                 class="h-20 w-20 rounded-2xl object-cover shadow-lg border-2 border-indigo-500"/>
                            <div v-else
                                 class="h-20 w-20 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold text-3xl shadow-lg shadow-indigo-500/20">
                                {{ user.firstname[0] }}{{ user.lastname[0] }}
                            </div>

                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ user.firstname }} {{ user.lastname }}
                                </h3>
                                <p class="text-gray-500 dark:text-zinc-400 font-medium">{{ user.email }}</p>
                                <p class="text-xs text-indigo-500 mt-1 font-mono">ID:
                                    #{{ user.id.toString().padStart(5, '0') }}</p>
                            </div>
                        </div>

                        <div class="w-full md:w-auto text-right">
                            <div v-if="user.eid_number" class="flex flex-col items-end">
                                <div
                                    class="flex items-center gap-2 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 px-4 py-2 rounded-xl text-sm font-bold border border-emerald-100 dark:border-emerald-800">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                                    </svg>
                                    Identité certifiée ({{ user.eid_number }})
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">Vérifié via eID Viewer</span>
                            </div>
                            <div v-else
                                 class="text-sm font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 px-4 py-2 rounded-xl border border-amber-100 dark:border-amber-800">
                                Identité non vérifiée
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!user.eid_number"
                     class="relative group"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="onDrop"
                >
                    <div :class="[
                        'transition-all duration-300 border-2 border-dashed rounded-3xl p-10 text-center flex flex-col items-center justify-center gap-4',
                        isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/10 scale-[1.01]' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'
                    ]">
                        <div
                            class="h-16 w-16 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 rounded-full flex items-center justify-center mb-2">
                            <svg v-if="form.processing" class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>

                        <div v-if="!form.processing">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">Importer votre photo
                                d'identité</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                                Glissez ici le fichier image exporté depuis le logiciel <strong>eID Viewer</strong>.
                            </p>
                            <input type="file" ref="fileInput" @change="handleFileUpload" accept="image/*"
                                   class="hidden"/>
                            <button @click="fileInput.click()"
                                    class="mt-6 bg-zinc-900 dark:bg-white dark:text-black text-white px-8 py-2.5 rounded-xl font-bold hover:scale-105 transition shadow-lg">
                                Sélectionner un fichier
                            </button>
                        </div>
                        <div v-else>
                            <h4 class="text-lg font-bold text-indigo-600 animate-pulse">Synchronisation en cours...</h4>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-zinc-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="font-bold text-lg text-gray-900 dark:text-white">Achats récents</h4>
                        <Link v-if="orders.length > 0" :href="route('orders.history')"
                              class="text-xs font-bold text-indigo-500 hover:underline uppercase tracking-wider">
                            Historique complet
                        </Link>
                    </div>

                    <div v-if="orders && orders.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                            <tr class="text-[10px] uppercase text-gray-400 font-black border-b border-gray-50 dark:border-zinc-800">
                                <th class="pb-3">Date</th>
                                <th class="pb-3">Détails</th>
                                <th class="pb-3 text-right">Total</th>
                                <th class="pb-3 text-right">Facture</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800">
                            <tr v-for="order in orders" :key="order.id"
                                class="group hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                <td class="py-4 text-sm font-medium text-gray-600 dark:text-zinc-400">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="py-4">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">
                                        {{ order.items.map(i => i.label).join(', ') }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">{{
                                            order.items.length
                                        }} objet(s)</p>
                                </td>
                                <td class="py-4 text-right font-bold text-gray-900 dark:text-white">
                                    {{ order.total_amount }}€
                                </td>
                                <td class="py-4 text-right">
                                    <a :href="route('order.invoice', order.id)" target="_blank"
                                       class="p-2 inline-flex bg-gray-100 dark:bg-zinc-800 rounded-lg text-gray-500 hover:text-indigo-600 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 9h1a1 1 0 110 2H9V9z"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else
                         class="text-center py-8 border-2 border-dashed border-gray-100 dark:border-zinc-800 rounded-2xl text-gray-400 italic text-sm">
                        Aucun achat récent.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-12">
                    <div
                        class="bg-white dark:bg-zinc-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="font-bold text-lg text-gray-900 dark:text-white">Mes Clubs</h4>
                            <span
                                class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 text-xs font-bold px-2 py-1 rounded">
                                {{ user.clubs.length }} actif(s)
                            </span>
                        </div>
                        <div v-if="user.clubs.length" class="space-y-3">
                            <div v-for="club in user.clubs" :key="club.id"
                                 class="flex justify-between items-center p-4 bg-gray-50 dark:bg-zinc-800/50 border border-gray-100 dark:border-zinc-800 rounded-xl hover:border-indigo-300 hover:shadow-sm transition-all group/club">

                                <Link :href="route('clubs.show', club.slug)" class="flex items-center gap-3 flex-1">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                        {{ club.name[0] }}
                                    </div>
                                    <span
                                        class="font-bold text-gray-700 dark:text-zinc-200 group-hover/club:text-indigo-600 transition-colors">
                                        {{ club.name }}
                                    </span>
                                    <svg
                                        class="w-4 h-4 text-indigo-400 opacity-0 group-hover/club:opacity-100 transition-all -translate-x-2 group-hover/club:translate-x-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>

                                <span
                                    :class="club.pivot.role_id === 1 ? 'bg-indigo-600 text-white' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400'"
                                                            class="text-[10px] font-black uppercase px-2 py-1 rounded">
                                    {{ club.pivot.role_id === 1 ? 'Admin' : 'Membre' }}
                                </span>
                            </div>
                        </div>
                        <div v-else
                             class="text-center py-8 border-2 border-dashed border-gray-100 dark:border-zinc-800 rounded-2xl text-gray-400 italic text-sm">
                            Vous n'êtes membre d'aucun club.
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-zinc-800">
                        <h4 class="font-bold text-lg text-gray-900 dark:text-white mb-6">Adresse de résidence</h4>
                        <div v-if="user.address"
                             class="space-y-1 text-gray-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800/30 p-4 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-indigo-500 mt-0.5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    <p class="font-bold text-gray-900 dark:text-white">{{ user.address.street }},
                                        {{ user.address.number }}</p>
                                    <p>{{ user.address.postal_code }} {{ user.address.city }}</p>
                                    <p class="uppercase tracking-widest text-[10px] mt-1 font-bold">
                                        {{ user.address.country }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else
                             class="text-center py-8 border-2 border-dashed border-gray-100 dark:border-zinc-800 rounded-2xl text-gray-400 italic text-sm">
                            Aucune adresse enregistrée.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

