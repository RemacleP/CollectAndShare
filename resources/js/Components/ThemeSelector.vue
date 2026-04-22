<script setup>
import { Moon, Sun, Monitor, CheckCircle2 } from 'lucide-vue-next';

// 1. On définit le modèle pour l'utiliser avec v-model dans UISettings
const modelValue = defineModel();

// 2. On simplifie la logique : on ne manipule plus le DOM ici
// car c'est le AuthenticatedLayout qui s'en occupe via les props globales
const themes = [
    { id: 'light', label: 'Clair', icon: Sun },
    { id: 'dark', label: 'Sombre', icon: Moon },
    { id: 'system', label: 'Système', icon: Monitor },
];

const selectTheme = (id) => {
    modelValue.value = id; // Met à jour la variable 'theme' du parent (UISettings)
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
            v-for="item in themes"
            :key="item.id"
            type="button"
            @click="selectTheme(item.id)"
            :class="modelValue === item.id
                ? 'border-indigo-600 ring-4 ring-indigo-50 dark:ring-indigo-900/20'
                : 'border-zinc-100 dark:border-zinc-800'"
            class="relative flex flex-col items-center gap-4 p-6 rounded-[2rem] border-2 transition-all hover:bg-zinc-50 dark:hover:bg-zinc-800 group"
        >
            <component
                :is="item.icon"
                class="w-8 h-8 transition-colors"
                :class="modelValue === item.id ? 'text-indigo-600' : 'text-zinc-400 group-hover:text-zinc-500'"
            />

            <span class="font-bold text-sm dark:text-white transition-colors"
                  :class="modelValue === item.id ? 'text-indigo-600' : 'text-zinc-500'">
                {{ item.label }}
            </span>

            <div v-if="modelValue === item.id" class="absolute top-4 right-4">
                <CheckCircle2 class="w-5 h-5 text-indigo-600" />
            </div>
        </button>
    </div>
</template>
