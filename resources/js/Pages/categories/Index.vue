<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { route } from 'ziggy-js';
import type { BreadcrumbItem } from '@/types';
import { index } from '@/routes/categories';


interface Category {
    id: number;
    name: string;
    description: string;
}

interface PaginatedCategories {
    data: Category[];
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    categories: PaginatedCategories;
    perPage: number;
    sort: string;
    direction: 'asc' | 'desc';
    search: string;
    auth: { user: { id: number | null; is_admin: boolean } };
}>();

const isEditing = ref(false);
const showModal = ref(false);
const search = ref(props.search);
const form = useForm({
    id: null as number | null,
    name: '',
    description: ''
});

function submitSearch() {
    router.get(route('categories.index'), {
            page: 1,
            perPage: props.perPage,
            sort: props.sort,
            direction: props.direction,
            search: search.value,
        },
        { preserveScroll: true }
    );
}

function changePerPage(event: Event) {
    const value = Number((event.target as HTMLSelectElement).value);
    router.get(route('categories.index'), { perPage: value, search: search.value });
}

function changeSort(sort: string) {
    const direction = props.sort === sort && props.direction === 'asc' ? 'desc' : 'asc';
    router.get(route('categories.index'), {
        page: props.categories.current_page,
        perPage: props.perPage,
        sort,
        direction,
        search: search.value
    });
}

const openModal = (category: Category | null = null) => {
    if (category) {
        isEditing.value = true;
        form.id = category.id;
        form.name = category.name;
        form.description = category.description;
    } else {
        isEditing.value = false;
        form.reset();
    }
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value && form.id) {
        form.put(route('categories.update', form.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const deleteCategory = (id: number) => {
    if (confirm('Supprimer cette catégorie ?')) {
        router.delete(route('categories.destroy', id));
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: route('home') },
    { title: 'Catégories', href: index().url },
];
</script>

<template>
    <Head title="Catégories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-6xl mx-auto space-y-6 p-4">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold">Gestion des Catégories</h1>
                    <button @click="openModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-md text-sm">
                        Nouvelle Catégorie
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Lookup -->
                    <div class="relative">
                        <input
                            v-model="search"
                            @keyup.enter="submitSearch"
                            type="text"
                            placeholder="Rechercher..."
                            class="rounded border px-3 py-2 text-sm dark:text-white" />
                    </div>

                    <!-- Sort -->
                    <button
                        @click="changeSort('name')"
                        class="flex items-center gap-1 rounded-md border bg-white px-3 py-2 text-sm dark:text-black">
                        Nom
                        <span v-if="props.sort === 'name'">{{ props.direction === 'asc' ? '↑' : '↓' }}</span>
                    </button>

                    <!-- Per page -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600 dark:text-white">Par page</label>
                        <select class="rounded border px-2 py-1 dark:text-white" :value="props.perPage" @change="changePerPage">
                            <option v-for="n in [10, 25, 50]" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Categories table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                    <tr>
                        <th class="p-4 font-semibold dark:text-white">Nom</th>
                        <th class="p-4 font-semibold dark:text-white">Description</th>
                        <th class="p-4 text-right font-semibold dark:text-white">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="cat in props.categories.data" :key="cat.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="p-4 dark:text-gray-200 font-medium">{{ cat.name }}</td>
                        <td class="p-4 dark:text-gray-400 text-sm">
                            {{ cat.description || 'Aucune description' }}
                        </td>
                        <td class="p-4 text-right space-x-3">
                            <button @click="openModal(cat)" class="text-indigo-600 hover:text-indigo-900 font-medium">Modifier</button>
                            <button @click="deleteCategory(cat.id)" class="text-red-600 hover:text-red-900 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    <tr v-if="props.categories.data.length === 0">
                        <td colspan="3" class="p-8 text-center text-gray-500">Aucune catégorie trouvée.</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="props.categories.last_page > 1" class="flex justify-center gap-2 mt-6">
                <Link
                    v-for="page in props.categories.last_page"
                    :key="page"
                    :href="route('categories.index', {
                        page,
                        perPage: props.perPage,
                        sort: props.sort,
                        direction: props.direction,
                        search: search
                    })"
                    preserve-scroll
                    class="px-3 py-1 rounded border text-sm bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    :class="page === props.categories.current_page ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : ''">
                    {{ page }}
                </Link>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl w-full max-w-md border dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4 dark:text-white">
                        {{ isEditing ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                    </h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-200">Nom</label>
                            <input v-model="form.name" class="w-full border rounded-lg p-2 dark:bg-gray-900 dark:text-white dark:border-gray-600" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-200">Description</label>
                            <textarea v-model="form.description" rows="3" class="w-full border rounded-lg p-2 dark:bg-gray-900 dark:text-white dark:border-gray-600"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-500 hover:text-gray-700 dark:text-gray-400">Annuler</button>
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                                {{ isEditing ? 'Mettre à jour' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
