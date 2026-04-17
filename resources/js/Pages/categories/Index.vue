<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, router, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";

interface Category {
    id: number;
    name: string;
    description: string;
}

interface PaginatedCategories {
    data: Category[];
    current_page: number;
    last_page: number;
    links: any[];
}

const props = defineProps<{
    categories: PaginatedCategories;
    perPage: number;
    sort: string;
    direction: 'asc' | 'desc';
    search: string;
}>();

const isEditing = ref(false);
const showModal = ref(false);
const searchQuery = ref(props.search);

const form = useForm({
    id: null as number | null,
    name: '',
    description: '',
    is_active: true,
});

// Navigation & Filtres
function updateFilters() {
    router.get(route('categories.index'), {
        search: searchQuery.value,
        perPage: props.perPage,
        sort: props.sort,
        direction: props.direction,
    }, { preserveState: true, preserveScroll: true });
}

function changeSort(field: string) {
    const newDirection = props.sort === field && props.direction === 'asc' ? 'desc' : 'asc';
    router.get(route('categories.index'), {
        ...route().params,
        sort: field,
        direction: newDirection,
    });
}

// Actions
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
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteCategory = (id: number) => {
    if (confirm('Supprimer cette catégorie ? Cela n\'affectera pas les collections existantes mais elle ne sera plus sélectionnable.')) {
        router.delete(route('categories.destroy', id));
    }
};

const breadcrumbs = [
    { title: 'Accueil', href: route('home') },
    { title: 'Catégories', href: route('categories.index') },
];
</script>

<template>
    <Head title="Gestion des Catégories" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-6 space-y-8">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white">Mes Catégories</h1>
                    <p class="text-gray-500 text-sm">Organisez vos collections par thématiques.</p>
                </div>

                <Button @click="openModal()" class="rounded-xl bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-none py-6 px-8">
                    <span class="text-lg font-bold">+</span> Nouvelle Catégorie
                </Button>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-[1.5rem] shadow-sm border border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[250px]">
                    <Input
                        v-model="searchQuery"
                        @keyup.enter="updateFilters"
                        placeholder="Rechercher une catégorie..."
                        class="rounded-xl border-gray-200"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <select
                        :value="perPage"
                        @change="(e) => router.get(route('categories.index', { ...route().params, perPage: (e.target as HTMLSelectElement).value }))"
                        class="rounded-xl border-gray-200 text-sm dark:bg-gray-900"
                    >
                        <option :value="10">10 par page</option>
                        <option :value="25">25 par page</option>
                        <option :value="50">50 par page</option>
                    </select>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <th @click="changeSort('name')" class="p-5 font-black uppercase text-xs tracking-widest text-gray-400 cursor-pointer hover:text-indigo-600 transition">
                            Nom <span v-if="sort === 'name'">{{ direction === 'asc' ? '↑' : '↓' }}</span>
                        </th>
                        <th class="p-5 font-black uppercase text-xs tracking-widest text-gray-400">Description</th>
                        <th class="p-5 font-black uppercase text-xs tracking-widest text-gray-400 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    <tr v-for="cat in categories.data" :key="cat.id" class="group hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                        <td class="p-5">
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ cat.name }}</span>
                        </td>
                        <td class="p-5">
                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ cat.description || '—' }}</p>
                        </td>
                        <td class="p-5 text-right">
                            <div class="flex justify-end gap-2">
                                <Button variant="ghost" size="sm" @click="openModal(cat)" class="rounded-lg text-indigo-600 hover:bg-indigo-100">
                                    Modifier
                                </Button>
                                <Button variant="ghost" size="sm" @click="deleteCategory(cat.id)" class="rounded-lg text-red-500 hover:bg-red-50">
                                    Supprimer
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="categories.data.length === 0">
                        <td colspan="3" class="p-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <span class="text-4xl">📂</span>
                                <p class="text-gray-400 font-medium">Aucune catégorie ne correspond à votre recherche.</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="categories.last_page > 1" class="flex justify-center items-center gap-2">
                <Link
                    v-for="link in categories.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-4 py-2 rounded-xl text-sm font-bold transition"
                    :class="[
                        link.active ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 hover:bg-gray-100',
                        !link.url ? 'opacity-30 cursor-not-allowed' : ''
                    ]"
                />
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-8">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6">
                        {{ isEditing ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                    </h2>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-400">Nom de la catégorie</label>
                            <Input v-model="form.name" placeholder="Ex: Numismatique" class="rounded-xl py-6" required />
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-400">Description (optionnel)</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-xl border-gray-200 dark:bg-gray-900 dark:border-gray-700 focus:ring-indigo-500"
                                placeholder="À quoi sert cette catégorie ?"
                            ></textarea>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <Button type="button" variant="outline" @click="showModal = false" class="flex-1 rounded-xl py-6 border-gray-200">
                                Annuler
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="flex-1 rounded-xl py-6 bg-indigo-600 hover:bg-indigo-700">
                                {{ isEditing ? 'Mettre à jour' : 'Créer' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
