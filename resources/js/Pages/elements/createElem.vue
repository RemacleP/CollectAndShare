<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import {computed, ref} from "vue";
import FileUploader from "@/components/upload/FileUploader.vue";
import {listeCollec} from "@/routes/collections";

const props = defineProps<{
    collect: { id: number, slug: string}
}>();
const form = useForm({
    label: '',
    description: '',
    year_production: 0,
    history: '',
    condition: '',
    price: 0,
    quantity:0,
    slug: '',
    collection_id: props.collect.id,
    image: null,
});

//Gestion image
const image = ref<File | null>(null);
function handleFile(file: File) {
    image.value = file;
}
function submit() {
    if (image.value) {
        form.image = image.value;
    }

    form.post(route('elements.storeElem', props.collect.slug), {
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
        title: 'Créer un élément',
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
        <h1 class="mb-4 text-2xl font-bold">Créer un élément</h1>
        <Link :href="route('elements.listeElem', props.collect.slug)">
            <button class="rounded bg-blue-600 px-4 py-2 text-white">
                Retour
            </button>
        </Link>
        <form @submit.prevent="submit">

            <div class="mb-3">
                <label class="font-semibold">Image</label>
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
                    class="w-full rounded border p-2 "
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

            <button
                type="submit"
                class="rounded bg-green-600 px-4 py-2 text-white"
            >
                Créer l'élément
            </button>
        </form>
    </div>
    </AppLayout>
</template>


