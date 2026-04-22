<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import ThemeSelector from "@/Components/ThemeSelector.vue";
import { Layout, Eye, Save, CheckCircle } from 'lucide-vue-next';
import {route} from "ziggy-js";

const page = usePage();

// Thème : Récupéré depuis les props auth (base de données)
const theme = ref(page.props.auth.user.theme || 'system');

// Autres réglages : On garde le localStorage car ils sont propres au confort de la machine actuelle
const viewDensity = ref(localStorage.getItem('viewDensity') || 'normal');
const showSidebar = ref(JSON.parse(localStorage.getItem('showSidebar')) ?? true);

const savedFeedback = ref(false);

/**
 * Enregistre les réglages :
 * 1. Thème envoyé au serveur (Persistance compte)
 * 2. Interface enregistrée en local (Confort machine)
 */
const saveSettings = () => {
    // 1. Mise à jour du thème via Inertia (appel vers ProfileController)
    router.patch(route('profile.update-theme'), {
        theme: theme.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // 2. Mise à jour des préférences locales (densité, sidebar)
            localStorage.setItem('viewDensity', viewDensity.value);
            localStorage.setItem('showSidebar', JSON.stringify(showSidebar.value));

            // Petit feedback visuel
            savedFeedback.value = true;
            setTimeout(() => savedFeedback.value = false, 3000);
        }
    });
};
</script>

<template>
    <Head title="Apparence & Interface" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Layout class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                <h2 class="font-black text-xl text-zinc-800 dark:text-zinc-100 uppercase tracking-widest truncate">
                    Interface
                </h2>
            </div>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">

                <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] shadow-xl shadow-zinc-200/50 dark:shadow-none border border-zinc-100 dark:border-zinc-800 transition-colors duration-500 overflow-hidden">

                    <div class="p-8 border-b border-zinc-50 dark:border-zinc-800/50">
                        <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tighter">
                            Personnalisation
                        </h2>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium mt-1">
                            Ces réglages seront appliqués à votre compte sur tous vos appareils.
                        </p>
                    </div>

                    <div class="p-8 space-y-10">

                        <section>
                            <label class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-6">
                                <Eye class="w-4 h-4" /> Thème Visuel (Cloud)
                            </label>
                            <ThemeSelector v-model="theme" />
                        </section>

                        <section class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-zinc-50 dark:border-zinc-800/50">
                            <div class="space-y-4">
                                <label class="text-xs font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Densité de vue</label>
                                <select v-model="viewDensity" class="w-full rounded-2xl border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 dark:text-white font-bold focus:ring-indigo-500 transition-all">
                                    <option value="compact">Compacte</option>
                                    <option value="normal">Standard</option>
                                    <option value="large">Spacieuse</option>
                                </select>
                            </div>

                            <div class="space-y-4">
                                <label class="text-xs font-black uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Navigation</label>
                                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800 rounded-2xl border border-transparent dark:border-zinc-700/50">
                                    <span class="text-sm font-bold dark:text-zinc-200">Afficher la barre latérale</span>
                                    <input type="checkbox" v-model="showSidebar" class="w-5 h-5 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="p-8 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 transition-all duration-500" :class="savedFeedback ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4'">
                            <CheckCircle class="w-5 h-5" />
                            <span class="text-sm font-black uppercase tracking-wide">Préférences synchronisées</span>
                        </div>

                        <button @click="saveSettings"
                                class="w-full md:w-auto flex items-center justify-center gap-3 bg-zinc-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-500 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all active:scale-95 shadow-lg shadow-indigo-500/20">
                            <Save class="w-4 h-4" />
                            Enregistrer
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
