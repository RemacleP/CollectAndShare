<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import * as LucideIcons from 'lucide-vue-next';
import { Globe, X, Plus } from 'lucide-vue-next';
import {route} from "ziggy-js";

const props = defineProps({
    user: Object,
    social_platforms: Array
});

// Initialisation identique à ton ClubController
const initialLinks = props.user.social_links?.map(link => ({
    platform_id: link.social_platform_id,
    identifier: link.identifier
})) || [];

const form = useForm({
    social_links: initialLinks,
});

const addLink = (platformId) => {
    form.social_links.push({
        platform_id: Number(platformId),
        identifier: ''
    });
};

const removeLink = (index) => {
    form.social_links.splice(index, 1);
};

const getPlatform = (id) => props.social_platforms.find(p => p.id === id);
const getIcon = (name) => LucideIcons[name] || Globe;

const submit = () => {
    form.patch(route('profile.socials.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Optionnel : console.log('Succès !')
        },
        onError: (errors) => {
            // Si ça échoue encore, tu verras pourquoi ici :
            console.error(errors);
        }
    });
};
</script>

<template>
    <section class="space-y-6">
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black uppercase tracking-tight text-zinc-900 dark:text-white">Réseaux Sociaux</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Ajoutez vos liens pour que la communauté puisse vous retrouver.</p>
            </div>

            <select
                @change="addLink($event.target.value); $event.target.value = ''"
                class="text-[10px] font-black uppercase tracking-widest border-zinc-200 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800 py-2 pl-3 pr-10 focus:ring-indigo-500 cursor-pointer"
            >
                <option value="" disabled selected>+ Ajouter un réseau</option>
                <option v-for="p in social_platforms" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="(link, index) in form.social_links" :key="index"
                 class="relative flex flex-col p-4 bg-zinc-50 dark:bg-zinc-800/40 rounded-[1.5rem] border border-zinc-100 dark:border-zinc-700 group animate-in fade-in slide-in-from-top-1"
            >
                <button type="button" @click="removeLink(index)"
                        class="absolute -right-2 -top-2 bg-white dark:bg-zinc-900 text-zinc-400 hover:text-red-500 rounded-full shadow-sm border border-zinc-200 dark:border-zinc-700 p-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                    <X class="w-3.5 h-3.5" />
                </button>

                <div class="flex items-center gap-2 mb-3">
                    <component :is="getIcon(getPlatform(link.platform_id).icon)" class="w-4 h-4 text-indigo-600" />
                    <span class="text-xs font-black uppercase text-zinc-700 dark:text-zinc-300">{{ getPlatform(link.platform_id).name }}</span>
                </div>

                <div class="flex shadow-sm rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
                    <span v-if="getPlatform(link.platform_id).base_url"
                          class="hidden sm:inline-flex items-center px-3 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 text-[10px] border-r border-zinc-200 dark:border-zinc-700">
                        {{ getPlatform(link.platform_id).base_url.replace('https://', '') }}
                    </span>
                    <input
                        type="text"
                        v-model="link.identifier"
                        class="flex-1 block w-full border-none focus:ring-indigo-500 text-sm dark:bg-zinc-900 dark:text-white"
                        placeholder="Identifiant..."
                    />
                </div>
                <InputError :message="form.errors[`social_links.${index}.identifier`]" class="mt-1" />
            </div>
        </div>

        <div v-if="form.social_links.length === 0" class="text-center py-12 border-2 border-dashed border-zinc-100 dark:border-zinc-800 rounded-[2rem]">
            <p class="text-xs text-zinc-400 font-bold uppercase tracking-widest">Aucun lien configuré</p>
        </div>

        <div class="flex items-center gap-4 mt-6">
            <button
                @click="submit"
                :disabled="form.processing"
                class="px-6 py-2 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 rounded-xl font-black text-xs uppercase tracking-widest hover:opacity-80 transition disabled:opacity-25"
            >
                Sauvegarder les liens
            </button>
            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-bold">Enregistré.</p>
            </Transition>
        </div>
    </section>
</template>
