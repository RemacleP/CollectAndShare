<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

// États réactifs
const theme = ref(localStorage.getItem('theme') || 'system');
const viewDensity = ref(localStorage.getItem('viewDensity') || 'normal');
const showSidebar = ref(JSON.parse(localStorage.getItem('showSidebar')) ?? true);

/**
 * Applique le thème visuel au document
 */
const applyThemeLogic = (selectedTheme) => {
    const html = document.documentElement;

    if (selectedTheme === 'dark') {
        html.classList.add('dark');
    } else if (selectedTheme === 'light') {
        html.classList.remove('dark');
    } else {
        // Mode Système : on vérifie la préférence du PC
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }
};

/**
 * Enregistre et applique tous les réglages
 */
const applySettings = () => {
    // 1. Appliquer et sauvegarder le thème
    applyThemeLogic(theme.value);
    localStorage.setItem('theme', theme.value);

    // 2. Sauvegarder les autres préférences
    localStorage.setItem('viewDensity', viewDensity.value);
    localStorage.setItem('showSidebar', JSON.stringify(showSidebar.value));

    // Feedback utilisateur
    alert('Paramètres enregistrés !');
};

onMounted(() => {
    // Initialisation au chargement
    theme.value = localStorage.getItem('theme') || 'system';

    // Écouteur pour le changement de thème système en temps réel
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (theme.value === 'system') {
            if (e.matches) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        }
    });
});
</script>

<template>
    <Head title="Paramètres d'interface" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
                Paramètres d'interface
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="p-6 bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4">Apparence</h3>
                    <p class="text-sm text-zinc-500 mb-6">Choisissez comment COLLECT&SHARE s'affiche sur votre écran.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button
                            @click="theme = 'light'"
                            :class="theme === 'light' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-zinc-200 dark:border-zinc-700'"
                            class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition text-center"
                        >
                            <span class="text-2xl">☀️</span>
                            <div>
                                <div class="font-bold dark:text-white text-sm text-nowrap">Mode Clair</div>
                            </div>
                        </button>

                        <button
                            @click="theme = 'dark'"
                            :class="theme === 'dark' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-zinc-200 dark:border-zinc-700'"
                            class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition text-center"
                        >
                            <span class="text-2xl">🌙</span>
                            <div>
                                <div class="font-bold dark:text-white text-sm text-nowrap">Mode Sombre</div>
                            </div>
                        </button>

                        <button
                            @click="theme = 'system'"
                            :class="theme === 'system' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-zinc-200 dark:border-zinc-700'"
                            class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition text-center"
                        >
                            <span class="text-2xl">💻</span>
                            <div>
                                <div class="font-bold dark:text-white text-sm text-nowrap">Système</div>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="p-6 bg-white dark:bg-zinc-900 shadow rounded-2xl border border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4">Affichage des listes</h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium dark:text-white">Densité de l'affichage</div>
                                <div class="text-sm text-zinc-500">Nombre d'éléments visibles sans défiler</div>
                            </div>
                            <select v-model="viewDensity" class="rounded-lg border-zinc-300 dark:bg-zinc-800 dark:text-white dark:border-zinc-700 focus:ring-indigo-500">
                                <option value="compact">Compact</option>
                                <option value="normal">Normal</option>
                                <option value="large">Aéré</option>
                            </select>
                        </div>

                        <div class="pt-4 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800">
                            <div>
                                <div class="font-medium dark:text-white">Afficher la barre latérale</div>
                                <div class="text-sm text-zinc-500">Gagner de l'espace horizontal sur les collections</div>
                            </div>
                            <input type="checkbox" v-model="showSidebar" class="rounded text-indigo-600 focus:ring-indigo-500 h-5 w-5 border-zinc-300 dark:bg-zinc-800">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        @click="applySettings"
                        class="px-10 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-lg shadow-indigo-200 dark:shadow-none active:scale-95"
                    >
                        Appliquer les changements
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
