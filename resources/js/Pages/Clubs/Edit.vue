<script setup>
import {Head, router, useForm} from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import * as LucideIcons from 'lucide-vue-next';
import {Globe, X, Plus, GripVertical, ChevronLeft} from 'lucide-vue-next';
import {route} from "ziggy-js";

const props = defineProps({
    club: Object,
    social_platforms: Array
});

/**
 * INITIALISATION : On transforme les liens existants en un tableau d'objets modifiable
 */
const initialLinks = props.club.social_links?.map(link => ({
    platform_id: link.social_platform_id,
    identifier: link.identifier
})) || [];

const form = useForm({
    _method: 'put',
    name: props.club.name,
    description: props.club.description || '',
    email: props.club.email || '',
    phone: props.club.phone || '',
    logo: null,
    street: props.club.address?.street || '',
    number: props.club.address?.number || '',
    box: props.club.address?.box || '',
    postal_code: props.club.address?.postal_code || '',
    city: props.club.address?.city || '',
    country: props.club.address?.country || 'Belgique',
    // On envoie maintenant un tableau d'objets
    social_links: initialLinks,
});

/**
 * AJOUT : Ajoute un nouvel objet vide au tableau
 */
const addLink = (platformId) => {
    form.social_links.push({
        platform_id: Number(platformId),
        identifier: ''
    });
};

/**
 * SUPPRESSION : Retire l'élément du tableau par son index
 */
const removeLink = (index) => {
    form.social_links.splice(index, 1);
};

const getPlatform = (id) => props.social_platforms.find(p => p.id === id);
const getIcon = (name) => LucideIcons[name] || Globe;

const submit = () => {
    form.post(route('clubs.update', props.club.slug), {
        forceFormData: true,
        preserveScroll: true,
    });
};
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('clubs.index'));
    }
};
</script>

<template>
    <Head :title="`Modifier ${club.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifier le club : {{ club.name }}
                </h2>
                <button
                    @click="goBack"
                    class="group flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors"
                >
                    <ChevronLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Retour
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white p-8 shadow sm:rounded-lg space-y-10">

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-6 uppercase text-xs tracking-widest">Informations Générales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="name" value="Nom du club" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                            </div>
                            <div>
                                <InputLabel for="logo" value="Logo" />
                                <input id="logo" type="file" @input="form.logo = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="description" value="Description" />
                                <textarea id="description" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm" v-model="form.description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <div class="space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-6 uppercase text-xs tracking-widest">Contact Direct</h3>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" />
                                </div>
                                <div>
                                    <InputLabel for="phone" value="Téléphone" />
                                    <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center justify-between border-b pb-2 mb-6">
                                <h3 class="text-lg font-bold text-gray-900 uppercase text-xs tracking-widest">Présence en ligne</h3>

                                <select
                                    @change="addLink($event.target.value); $event.target.value = ''"
                                    class="text-[10px] font-black uppercase tracking-widest border-zinc-200 rounded-lg bg-zinc-50 py-1 pl-2 pr-8 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                                >
                                    <option value="" disabled selected>+ Ajouter un lien</option>
                                    <option v-for="p in social_platforms" :key="p.id" :value="p.id">
                                        {{ p.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(link, index) in form.social_links" :key="index"
                                     class="relative flex flex-col p-4 bg-zinc-50 rounded-2xl border border-zinc-100 group animate-in fade-in slide-in-from-top-1 duration-200"
                                >
                                    <button
                                        type="button"
                                        @click="removeLink(index)"
                                        class="absolute -right-2 -top-2 bg-white text-zinc-400 hover:text-red-500 rounded-full shadow-sm border border-zinc-200 p-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <X class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="flex items-center gap-2 mb-3">
                                        <component :is="getIcon(getPlatform(link.platform_id).icon)" class="w-4 h-4 text-indigo-600" />
                                        <span class="text-xs font-black uppercase text-zinc-700">{{ getPlatform(link.platform_id).name }}</span>
                                    </div>

                                    <div class="flex shadow-sm rounded-xl overflow-hidden bg-white">
                                        <span v-if="getPlatform(link.platform_id).base_url"
                                              class="inline-flex items-center px-3 bg-zinc-100 border border-r-0 border-zinc-300 text-zinc-500 text-[10px] font-medium">
                                            {{ getPlatform(link.platform_id).base_url.replace('https://', '').replace('http://', '') }}
                                        </span>
                                        <input
                                            type="text"
                                            v-model="link.identifier"
                                            class="flex-1 block w-full border-zinc-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            :class="getPlatform(link.platform_id).base_url ? 'rounded-r-xl' : 'rounded-xl'"
                                            placeholder="Identifiant ou URL..."
                                        />
                                    </div>
                                    <InputError :message="form.errors[`social_links.${index}.identifier`]" class="mt-1" />
                                </div>

                                <div v-if="form.social_links.length === 0" class="text-center py-10 border-2 border-dashed border-zinc-100 rounded-2xl">
                                    <p class="text-xs text-zinc-400 font-bold uppercase tracking-widest">Aucun lien social</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-6 uppercase text-xs tracking-widest">Localisation</h3>
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                            <div class="md:col-span-4"><InputLabel value="Rue" /><TextInput class="mt-1 block w-full" v-model="form.street" required /></div>
                            <div class="md:col-span-1"><InputLabel value="N°" /><TextInput class="mt-1 block w-full" v-model="form.number" required /></div>
                            <div class="md:col-span-1"><InputLabel value="Boîte" /><TextInput class="mt-1 block w-full" v-model="form.box" /></div>
                            <div class="md:col-span-2"><InputLabel value="Code Postal" /><TextInput class="mt-1 block w-full" v-model="form.postal_code" required /></div>
                            <div class="md:col-span-2"><InputLabel value="Ville" /><TextInput class="mt-1 block w-full" v-model="form.city" required /></div>
                            <div class="md:col-span-2"><InputLabel value="Pays" /><TextInput class="mt-1 block w-full" v-model="form.country" required /></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-10 pt-6 border-t border-zinc-100">
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Enregistrer les modifications
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
