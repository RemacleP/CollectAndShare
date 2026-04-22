<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { ref, computed } from "vue";
import { Button } from "@/components/ui/button";
import FileUploader from "@/components/upload/FileUploader.vue";
import { ArrowLeft, Save, Package, Tag, History, Calendar, Info } from 'lucide-vue-next';

const props = defineProps<{
    collect: {
        id: number;
        name: string;
        slug: string;
    }
}>();

const form = useForm({
    label: '',
    description: '',
    year_production: new Date().getFullYear(),
    history: '',
    condition: '',
    price: 0,
    quantity: 1,
    collection_id: props.collect.id,
    image: null as File | null,
});

// Gestion de l'image via le composant FileUploader
const handleFile = (file: File) => {
    form.image = file;
};

const submit = () => {
    form.post(route('elements.storeElem', props.collect.slug), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const breadcrumbs = computed(() => [
    { title: 'Collections', href: route('collections.listeCollec') },
    { title: props.collect.name, href: route('elements.listeElem', props.collect.slug) },
    { title: 'Nouvel élément' },
]);

const conditions = [
    { value: 'new', label: 'Neuf' },
    { value: 'used', label: 'Utilisé' },
    { value: 'damaged', label: 'Endommagé' }
];
</script>

<template>
    <Head :title="`Ajouter à ${collect.name}`" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-6">

            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white">Ajouter un objet</h1>
                    <p class="text-sm text-gray-500">Collection : <span class="text-indigo-600 font-bold">{{ collect.name }}</span></p>
                </div>
                <Link :href="route('elements.listeElem', collect.slug)">
                    <Button variant="outline" class="rounded-xl">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Retour
                    </Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white dark:bg-zinc-900 p-6 rounded-[2rem] border border-gray-100 dark:border-zinc-800 shadow-sm">
                    <label class="flex items-center gap-2 text-sm font-black uppercase tracking-widest text-gray-400 mb-4">
                        <Package class="h-4 w-4" /> Illustration de l'objet
                    </label>
                    <FileUploader @file-selected="handleFile" />
                    <div v-if="form.errors.image" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.image }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-zinc-900 p-6 rounded-[2rem] border border-gray-100 dark:border-zinc-800 shadow-sm space-y-4">
                        <label class="flex items-center gap-2 text-sm font-black uppercase tracking-widest text-gray-400">
                            <Tag class="h-4 w-4" /> Détails
                        </label>

                        <div>
                            <input
                                v-model="form.label"
                                placeholder="Nom de l'objet (ex: Pièce de 2€ commémorative)"
                                class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-4 focus:ring-2 focus:ring-indigo-500 outline-none transition"
                                required
                            />
                            <div v-if="form.errors.label" class="text-red-500 text-xs mt-1">{{ form.errors.label }}</div>
                        </div>

                        <textarea
                            v-model="form.description"
                            placeholder="Description courte..."
                            rows="4"
                            class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-4 focus:ring-2 focus:ring-indigo-500 outline-none transition"
                            required
                        ></textarea>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 p-6 rounded-[2rem] border border-gray-100 dark:border-zinc-800 shadow-sm space-y-4">
                        <label class="flex items-center gap-2 text-sm font-black uppercase tracking-widest text-gray-400">
                            <Info class="h-4 w-4" /> Caractéristiques
                        </label>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Année</label>
                                <input type="number" v-model="form.year_production" class="w-full rounded-xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-3 focus:ring-indigo-500" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">État</label>
                                <select v-model="form.condition" class="w-full rounded-xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-3 focus:ring-indigo-500">
                                    <option value="" disabled>Choisir...</option>
                                    <option v-for="c in conditions" :key="c.value" :value="c.value">{{ c.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Prix estimé (€)</label>
                                <input type="number" step="0.01" v-model="form.price" class="w-full rounded-xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-3 focus:ring-indigo-500" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Quantité</label>
                                <input type="number" v-model="form.quantity" class="w-full rounded-xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-3 focus:ring-indigo-500" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 p-6 rounded-[2rem] border border-gray-100 dark:border-zinc-800 shadow-sm">
                    <label class="flex items-center gap-2 text-sm font-black uppercase tracking-widest text-gray-400 mb-4">
                        <History class="h-4 w-4" /> Histoire / Provenance
                    </label>
                    <textarea
                        v-model="form.history"
                        placeholder="D'où vient cet objet ? Son parcours..."
                        rows="3"
                        class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-4 focus:ring-2 focus:ring-indigo-500 outline-none transition"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('elements.listeElem', collect.slug)" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition">
                        Annuler
                    </Link>
                    <Button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="rounded-2xl bg-indigo-600 px-10 py-7 font-black text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 dark:shadow-none transition-all active:scale-95"
                    >
                        <Save class="mr-2 h-5 w-5" />
                        {{ form.processing ? 'Enregistrement...' : "Sauvegarder l'objet" }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
