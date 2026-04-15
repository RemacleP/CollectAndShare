<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import {computed, ref} from "vue";
import FileUploader from "@/components/upload/FileUploader.vue";
import {listeCollec} from "@/routes/collections";

const props = defineProps<{
    collect: { id: number; slug: string}
    element: { id: number; label?: string; description?: string; year_production?:number; history?:string ;condition?: string; price?: number; quantity?:number; collection_id?: number; slug?: string; image?: string | null  }
}>();
const form = useForm({
    label: props.element.label,
    description: props.element.description,
    year_production: props.element.year_production,
    history: props.element.history,
    condition: props.element.condition,
    price: props.element.price,
    quantity:props.element.quantity,
    slug: props.element.slug,
    collection_id: props.collect.id,
    image: null,
    delete_image: false,
});
//Gestion image
const newImage = ref<File | null>(null);
function handleFile(file: File) {
    newImage.value = file;
}
function deleteImage() {
    form.delete_image = true;
    newImage.value = null;
    form.image = null;
}
function submit() {
    if (newImage.value) {
        form.image = newImage.value;
        form.delete_image = false;
    }
    form.post(route('elements.updateElem', [props.collect.slug, props.element.slug]),
        {
            _method: 'patch',
            forceFormData: true
        });
}


const breadcrumbs = computed(() => [
    { title: 'Accueil', href: route('home') },
    {
        title: props.collect.name
            ? `Collection  : ${props.collect.name}`
            : 'Collection :',
        href: listeCollec().url
    },
    { title: 'Elements', href: route ('elements.listeElem',props.collect.slug) },
    {
        title: form.label
            ? `Modification élément : ${form.label}`
            : 'Modification élément',
        href: route('elements.editElem', [props.collect.slug, props.element.slug]),
    },
]);
</script>
<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
        <h1 class="mb-4 text-2xl font-bold">Modifier un élément</h1>
        <Link :href="route('elements.listeElem', props.collect.slug)">
            <button class="rounded bg-blue-600 px-4 py-2 text-white">
                Retour
            </button>
        </Link>
        <form @submit.prevent="submit">

            <div v-if="props.element.image && !form.delete_image" class="mb-3">
                <label class="font-semibold">Image actuelle</label>
                <img
                    :src="`/storage/${props.element.image}`"
                    class="w-32 h-32 object-cover rounded"
                />
                <button
                    type="button"
                    @click="deleteImage"
                    class="rounded bg-red-500 px-3 py-1 text-sm text-white hover:bg-red-600 transition"
                >
                    Supprimer l'image actuelle
                </button>
            </div>

            <div v-if="form.delete_image" class="text-red-500 font-medium my-2">
                L'image actuelle sera supprimée lors de la mise à jour.
            </div>

            <div class="mb-3">
                <label class="font-semibold">Nouvelle image</label>
                <FileUploader @file-selected="handleFile" />
            </div>

            <div class="mb-3">
                <label class="font-semibold">Nom</label>
                <input v-model="form.label" class="w-full rounded border p-2"
                       required
                />
            </div>

            <div class="mb-3">
                <label class="font-semibold">Description</label>
                <textarea
                    v-model="form.description"
                    class="w-full rounded border p-2"
                    required
                ></textarea>
            </div>

            <div class="mb-3">
                <label class="font-semibold">Année de production</label>
                <input
                    type="number"
                    v-model.number="form.year_production"
                    class="w-full rounded border p-2"
                    required
                />
            </div>

            <div class="mb-3">
                <label class="font-semibold">Histoire</label>
                <textarea
                    v-model="form.history"
                    class="w-full rounded border p-2"
                ></textarea>
            </div>

            <div class="mb-3">
                <label class="font-semibold">Condition</label>
                <select
                    v-model="form.condition"
                    class="w-full rounded border p-2"
                    required
                >
                    <option class="text-gray-700" value="" disabled>Choisir une condition</option>
                    <option class="text-gray-700" value="new">Neuf</option>
                    <option class="text-gray-700" value="used">Utilisé</option>
                    <option class="text-gray-700" value="damaged">Endommagé</option>
                </select>
            </div>


            <div class="mb-3">
                <label class="font-semibold">Prix (€)</label>
                <input
                    type="number"
                    v-model.number="form.price"
                    class="w-full rounded border p-2"
                    required
                />
            </div>

            <div class="mb-3">
                <label class="font-semibold">Quantité</label>
                <input
                    type="number"
                    v-model.number="form.quantity"
                    class="w-full rounded border p-2"
                    required
                />
            </div>

<!--            <div class="mb-3">-->
<!--                <label class="font-semibold">Slug</label>-->
<!--                <input-->
<!--                    type="text"-->
<!--                    v-model.number="form.slug"-->
<!--                    class="w-full rounded border p-2"-->
<!--                />-->
<!--            </div>-->

            <button
                type="submit"
                class="rounded bg-orange-600 px-4 py-2 text-white"
            >
                Modifier l'élément
            </button>
        </form>
    </div>
    </AppLayout>
</template>


