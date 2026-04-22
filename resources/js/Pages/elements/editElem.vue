<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import FileUploader from "@/Components/upload/FileUploader.vue"; // Assure-toi du C majuscule

// Interface pour typer les images existantes reçues de Laravel
interface ElementImage {
    id: number;
    path: string;
}

const props = defineProps<{
    collect: { id: number; name: string; slug: string };
    element: {
        id: number;
        label?: string;
        description?: string;
        year_production?: number;
        history?: string;
        condition?: string;
        price?: number;
        quantity?: number;
        slug: string;
        // Tableau d'images existantes
        images?: ElementImage[];
    }
}>();

const form = useForm({
    label: props.element.label ?? '',
    description: props.element.description ?? '',
    year_production: props.element.year_production ?? new Date().getFullYear(),
    history: props.element.history ?? '',
    condition: props.element.condition ?? '',
    price: props.element.price ?? 0,
    quantity: props.element.quantity ?? 1,
    // Nouvelles images à uploader
    images: [] as File[],
    // IDs des images existantes à supprimer
    remove_images: [] as number[],
    collection_id: props.collect.id,
});

/**
 * Met à jour le formulaire avec les nouvelles images sélectionnées via le FileUploader
 */
function handleFilesUpdate(files: File[]) {
    form.images = files;
}

/**
 * Bascule l'état de suppression d'une image existante.
 * Ajoute ou retire l'ID de l'image du tableau remove_images.
 */
function toggleImageDeletion(imageId: number) {
    if (form.remove_images.includes(imageId)) {
        // Annuler la suppression : on enlève l'ID du tableau
        form.remove_images = form.remove_images.filter(id => id !== imageId);
    } else {
        // Confirmer la suppression : on ajoute l'ID au tableau
        form.remove_images.push(imageId);
    }
}

function submit() {
    form.post(route('elements.updateElem', {
        collection: props.collect.slug,
        element: props.element.slug
    }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => console.log("Succès !"),
    });
}

const breadcrumbs = computed(() => [
    { title: 'Accueil', href: route('home') },
    { title: 'Collections', href: route('collections.listeCollec') },
    { title: props.collect.name, href: route('elements.listeElem', { collection: props.collect.slug }) },
    { title: 'Modifier' }
]);
</script>

<template>
    <Head :title="`Modifier ${form.label}`" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-5xl mx-auto">
            <div class="mb-8 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-4xl font-black text-gray-900 dark:text-white">Modifier l'objet</h1>
                    <p class="text-sm text-gray-500">Mettez à jour les informations et les photos de votre pièce de collection.</p>
                </div>
                <Link :href="route('elements.listeElem', props.collect.slug)">
                    <button class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-50 transition active:scale-95">
                        Retour
                    </button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-1 space-y-8">

                    <div v-if="props.element.images && props.element.images.length > 0" class="p-6 bg-white dark:bg-zinc-900 rounded-[2rem] border border-gray-100 dark:border-zinc-800 shadow-sm">
                        <label class="block mb-4 font-bold text-sm uppercase tracking-widest text-gray-400">Photos actuelles</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="img in props.element.images" :key="img.id" class="relative group aspect-square">
                                <img
                                    :src="`/storage/${img.path}`"
                                    class="w-full h-full object-cover rounded-2xl border-4 shadow-inner transition-all duration-300"
                                    :class="form.remove_images.includes(img.id)
                                        ? 'opacity-30 grayscale border-red-500 scale-95'
                                        : 'border-transparent group-hover:border-gray-100'"
                                />
                                <button
                                    type="button"
                                    @click="toggleImageDeletion(img.id)"
                                    class="absolute top-2 right-2 p-1.5 rounded-full shadow-lg transition-all duration-300 active:scale-90"
                                    :class="form.remove_images.includes(img.id)
                                        ? 'bg-green-500 text-white rotate-[-90deg]'
                                        : 'bg-red-500 text-white opacity-0 group-hover:opacity-100'"
                                >
                                    <span v-if="form.remove_images.includes(img.id)" class="text-lg font-bold leading-none">↺</span>
                                    <span v-else class="text-xl font-bold leading-none">&times;</span>
                                </button>

                                <div v-if="form.remove_images.includes(img.id)" class="absolute inset-0 flex items-center justify-center rounded-2xl bg-red-500/10">
                                    <span class="text-[10px] font-black text-red-600 uppercase tracking-tighter bg-white px-2 py-1 rounded-full shadow">À supprimer</span>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.remove_images.length > 0" class="mt-4 text-xs text-red-500 font-bold text-center">
                            {{ form.remove_images.length }} photo(s) seront supprimées définitivement.
                        </p>
                    </div>

                    <div class="p-6 bg-white dark:bg-zinc-900 rounded-[2rem] border border-gray-100 dark:border-zinc-800 shadow-sm">
                        <label class="block mb-4 font-bold text-sm uppercase tracking-widest text-gray-400">Ajouter de nouvelles photos</label>
                        <FileUploader @files-selected="handleFilesUpdate" />
                    </div>
                </div>

                <div class="lg:col-span-2 p-8 bg-white dark:bg-zinc-900 rounded-[2rem] border border-gray-100 dark:border-zinc-800 shadow-sm space-y-6">
                    <label class="block mb-2 font-bold text-sm uppercase tracking-widest text-gray-400">Informations détaillées</label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2 md:col-span-2">
                            <label class="font-semibold text-gray-700 dark:text-gray-200">Nom de l'objet</label>
                            <input v-model="form.label" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 p-3 focus:ring-indigo-500 dark:bg-zinc-800" required placeholder="Ex: Figurine Goldorak 1978" />
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="font-semibold text-gray-700 dark:text-gray-200">Description</label>
                            <textarea v-model="form.description" rows="4" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 p-3 dark:bg-zinc-800" required placeholder="Décrivez votre objet..."></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="font-semibold text-gray-700 dark:text-gray-200">Année de production</label>
                            <input type="number" v-model.number="form.year_production" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 p-3 dark:bg-zinc-800" required />
                        </div>

                        <div class="space-y-2">
                            <label class="font-semibold text-gray-700 dark:text-gray-200">État / Condition</label>
                            <select v-model="form.condition" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 p-3 dark:bg-zinc-800" required>
                                <option value="new">Neuf (Mint)</option>
                                <option value="used">Utilisé / Bon état</option>
                                <option value="damaged">Endommagé / Pour pièces</option>
                            </select>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="font-semibold text-gray-700 dark:text-gray-200">Histoire / Anecdote</label>
                            <textarea v-model="form.history" rows="3" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 p-3 dark:bg-zinc-800" placeholder="L'origine de cette pièce, comment vous l'avez obtenue..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="font-semibold text-gray-700 dark:text-gray-200">Estimation Prix (€)</label>
                                <input type="number" v-model.number="form.price" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 p-3 dark:bg-zinc-800" required />
                            </div>
                            <div class="space-y-2">
                                <label class="font-semibold text-gray-700 dark:text-gray-200">Quantité</label>
                                <input type="number" v-model.number="form.quantity" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 p-3 dark:bg-zinc-800" required />
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t dark:border-zinc-800">
                        <button
                            type="button"
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full rounded-2xl bg-indigo-600 px-8 py-4 font-black text-white shadow-xl hover:bg-indigo-700 disabled:opacity-50 transition-all active:scale-95"
                        >
                            {{ form.processing ? 'Enregistrement des modifications...' : 'Sauvegarder les modifications' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
