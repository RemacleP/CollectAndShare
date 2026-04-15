<script setup lang="ts">
import {Head, Link, useForm} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import type {BreadcrumbItem} from "@/types";
import {route} from "ziggy-js";
import {Button} from "@/components/ui/button";
const breadcrumbs: BreadcrumbItem[] = [

];
const props = defineProps<{
    lienUtile: {
        id: number;
        nom: string;
        url: string;
    };
}>();

const form = useForm({
    nom: props.lienUtile.nom,
    url: props.lienUtile.url,}
);

function submit() {
    form.post(route('liensUtiles.update', props.lienUtile.id), {
        _method: 'patch',
        forceFormData: true,
    });
}

</script>

<template>
    <Head title="Modifier le lien utile">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl mx-auto p-8">

            <div class="mb-10">
                <Link :href="route('liensUtiles.index')" class="group flex items-center text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-4">
                    <span class="mr-2 transition-transform group-hover:-translate-x-1">←</span>
                    Retour à la liste
                </Link>

                <h1 class="text-4xl font-black text-gray tracking-tight uppercase">
                    Modifier le lien
                </h1>
                <p class="text-indigo-600 font-medium mt-1">Édition de : {{ form.nom }}</p>
                <div class="h-1.5 w-16 bg-amber-500 mt-2 rounded-full"></div>
            </div>

            <div class="bg-white border border-gray-100 rounded-3xl shadow-xl p-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-1 bg-amber-500"></div>

                <form @submit.prevent="submit" class="space-y-8">

                    <div>
                        <label for="nom" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2 ml-1">
                            Nom du lien
                        </label>
                        <input
                            type="text"
                            name="nom"
                            id="nom"
                            v-model="form.nom"
                            required
                            class="block w-full px-5 py-4 bg-gray-50 border border-gray-800 rounded-2xl focus:ring-2 focus:ring-amber-500 transition-all text-gray-800"
                        />
                        <div v-if="form.errors.nom" class="mt-2 text-sm text-red-500 font-medium ml-1">
                            {{ form.errors.nom }}
                        </div>
                    </div>

                    <div>
                        <label for="url" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2 ml-1">
                            URL du lien
                        </label>
                        <input
                            type="url"
                            name="url"
                            id="url"
                            v-model="form.url"
                            required
                            class="block w-full px-5 py-4 bg-gray-50 border border-gray-800 rounded-2xl focus:ring-2 focus:ring-amber-500 transition-all text-gray-800 font-mono text-sm"
                        />
                        <div v-if="form.errors.url" class="mt-2 text-sm text-red-500 font-medium ml-1">
                            {{ form.errors.url }}
                        </div>
                    </div>

                    <div class="pt-4 flex flex-col gap-3">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-7 rounded-2xl shadow-lg shadow-indigo-100 transition-all hover:scale-[1.02] active:scale-95 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Mise à jour en cours...</span>
                            <span v-else>Enregistrer les modifications</span>
                        </Button>

                        <Link :href="route('liensUtiles.index')" class="text-center text-sm text-gray-400 hover:text-gray-600 py-2">
                            Annuler les changements
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
