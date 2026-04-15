<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import FileUploader from '@/components/upload/FileUploader.vue';
import { editCollec, listeCollec } from '@/routes/collections';
import PictureUploader from '@/components/upload/PictureUploader.vue';

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    collect: {
        id: number;
        name?: string;
        description?: string;
        club_id?: number | null;
        club_user_id?: number | null;
        user_id?: number;
        slug: string;
        image?: string | null;
        categories?: Category[];
    };
    clubs: Array<{ id: number; name: string }>;
    club_users: Array<{ id: number; user?: { id: number; name: string } }>;
    categories: Category[];
    isUser: boolean;
    isClubManager: boolean;
}>();

const form = useForm({
    name: props.collect.name,
    description: props.collect.description,
    club_id: props.collect.club_id,
    club_user_id: props.collect.club_user_id,
    image: null,
    delete_image: false,
    categories: props.collect.categories?.map((c) => c.id) || [],
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
    form.post(route('collections.updateCollec', props.collect.slug), {
        _method: 'patch',
        forceFormData: true,
    });
}

const breadcrumbs = computed(() => [
    { title: 'Accueil', href: route('home') },
    { title: 'collections', href: listeCollec().url },
    {
        title: form.name
            ? `Modification collection : ${form.name}`
            : 'Modification collection',
        href: editCollec(props.collect.slug).url,
    },
]);
</script>

<template>
    <Head title="Collection - Édition">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="mb-4 text-2xl font-bold">Modifier la collection</h1>

            <Link :href="route('collections.listeCollec')">
                <button class="rounded bg-blue-600 px-4 py-2 text-white">
                    Retour
                </button>
            </Link>

            <form @submit.prevent="submit">
                <div class="mb-3">
                    <label class="font-semibold">Nouvelle image</label>
                    <PictureUploader
                        @file-selected="handleFile"
                        :existing-image="`/storage/${props.collect.image}`"
                    />
                </div>

                <div class="mb-3">
                    <label class="font-semibold">Nom</label>
                    <input
                        v-model="form.name"
                        class="w-full rounded border p-2"
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
                    <label class="font-semibold">Club</label>
                    <input
                        type="text"
                        class="w-full rounded border p-2 "
                        :value="clubs.find(c => c.id === form.club_id)?.name || 'Aucun'"
                        disabled
                    />
                    <input
                        type="hidden"
                        v-model="form.club_id"
                    />
                </div>

                <div class="mb-3">
                    <label class="font-semibold">Propriétaire</label>

                    <input
                        type="text"
                        class="w-full rounded border p-2"
                        :value="club_users.find(cu => cu.id === form.club_user_id)?.user?.name || 'Aucun'"
                        disabled
                    />

                    <input
                        type="hidden"
                        v-model="form.club_user_id"
                    />
                </div>


                <!-- Categories -->
                <div class="mb-3">
                    <label class="font-semibold">Catégories</label>
                    <div
                        class="mt-2 grid grid-cols-2 gap-2 rounded border bg-gray-50 p-2 md:grid-cols-3"
                    >
                        <div
                            v-for="category in categories"
                            :key="category.id"
                            class="flex items-center"
                        >
                            <input
                                type="checkbox"
                                :id="`cat-${category.id}`"
                                :value="category.id"
                                v-model="form.categories"
                                class="focus:ring-opacity-50 mr-2 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            />
                            <label
                                :for="`cat-${category.id}`"
                                class="cursor-pointer text-sm text-gray-700 select-none"
                            >
                                {{ category.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <button
                    type="submit"
                    class="rounded bg-orange-600 px-4 py-2 text-white"
                >
                    Sauvegarder
                </button>
            </form>
        </div>
    </AppLayout>
</template>
