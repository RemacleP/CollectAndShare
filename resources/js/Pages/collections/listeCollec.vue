<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { onMounted, ref, computed } from 'vue';
import { Button } from "@/components/ui/button";

interface Category {
    id: number;
    name: string;
}

interface Collection {
    id: number;
    name: string;
    description: string;
    club?: { id: number; name: string; address: string };
    // Mise à jour pour correspondre à ta structure pivot club_user_role
    club_user_role?: {
        id: number;
        user?: { id: number; username: string; email: string };
    };
    slug: string;
    image: string;
    categories?: Category[];
    can_edit: boolean;
}

const props = defineProps<{
    collects: Collection[];
    categories: Category[];
    filters?: { category?: string };
    open?: string | number;
    isAdmin: boolean;
    userId: number;
}>();

// --- Logique du Modal ---
const showModal = ref(false);
const selectedCollect = ref<Collection | null>(null);

function openModal(collect: Collection) {
    selectedCollect.value = collect;
    showModal.value = true;
}

function deleteCollect(slug: string) {
    if (confirm('Voulez-vous vraiment supprimer cette collection ?')) {
        // @ts-ignore (route est global via Ziggy)
        router.delete(route('collections.deleteCollec', slug), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

onMounted(() => {
    if (props.open) {
        const openId = Number(props.open);
        const col = props.collects.find((c) => c.id === openId);
        if (col) openModal(col);
    }
});

// --- Séparation des collections ---
const myCollections = computed(() => {
    return props.collects.filter(c => c.club_user_role?.user?.id === props.userId);
});

const otherCollections = computed(() => {
    return props.collects.filter(c => c.club_user_role?.user?.id !== props.userId);
});

// Fil d'Ariane : on utilise route() directement
const breadcrumbs = [
    // @ts-ignore
    { title: 'Accueil', href: route('home') },
    // @ts-ignore
    { title: 'Collections', href: route('collections.listeCollec') },
];
</script>

<template>
    <Head title="Liste des collections" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-black text-gray-900 dark:text-white">Exploration</h1>

                <Link v-if="$page.props.auth.user" :href="route('collections.createCollec')">
                    <Button class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-none active:scale-95 dark:shadow-none">
                        Créer une collection
                    </Button>
                </Link>
            </div>

            <div v-if="myCollections.length" class="mb-12">
                <h2 class="mb-6 flex items-center gap-2 text-lg font-black uppercase tracking-widest text-indigo-600">
                    <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                    Mes Collections
                </h2>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    <div
                        v-for="collect in myCollections"
                        :key="collect.id"
                        @click="openModal(collect)"
                        class="group cursor-pointer overflow-hidden rounded-[2rem] border-2 border-indigo-100 bg-white p-2 shadow-sm transition-all hover:border-indigo-500 hover:shadow-xl dark:border-gray-800 dark:bg-gray-800"
                    >
                        <div class="relative aspect-square overflow-hidden rounded-[1.5rem]">
                            <img v-if="collect.image" :src="`/storage/${collect.image}`" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            <div v-else class="flex h-full w-full items-center justify-center bg-indigo-50 text-indigo-200">
                                <span class="text-4xl font-black uppercase opacity-20">N/A</span>
                            </div>
                            <div class="absolute bottom-3 left-3">
                                <span class="rounded-full bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-indigo-600 backdrop-blur shadow-sm">Propriétaire</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="truncate font-bold text-gray-900 dark:text-white">{{ collect.name }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-6 flex items-center gap-2 text-lg font-black uppercase tracking-widest text-gray-400">
                    <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                    Toutes les collections
                </h2>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    <div
                        v-for="collect in otherCollections"
                        :key="collect.id"
                        @click="openModal(collect)"
                        class="group cursor-pointer overflow-hidden rounded-[2rem] border border-gray-100 bg-white p-2 shadow-sm transition-all hover:shadow-xl dark:border-gray-800 dark:bg-gray-800"
                    >
                        <div class="aspect-square overflow-hidden rounded-[1.5rem] bg-gray-100">
                            <img v-if="collect.image" :src="`/storage/${collect.image}`" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                <span class="text-4xl font-black opacity-10">Empty</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="truncate font-bold text-gray-900 dark:text-white">{{ collect.name }}</h3>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="h-5 w-5 rounded-full bg-gray-200 text-[10px] flex items-center justify-center font-bold text-gray-500 uppercase">
                                    {{ collect.club_user_role?.user?.username?.charAt(0) }}
                                </div>
                                <span class="text-xs font-medium text-gray-500">{{ collect.club_user_role?.user?.username }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="showModal && selectedCollect"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md bg-black/40"
                @click.self="showModal = false"
            >
                <div class="relative w-full max-w-lg overflow-hidden rounded-[2.5rem] bg-white shadow-2xl dark:bg-gray-900 border dark:border-gray-800">
                    <button @click="showModal = false" class="absolute right-6 top-6 z-10 text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <span class="text-2xl">&times;</span>
                    </button>

                    <div class="p-8">
                        <div class="mb-6 flex gap-6">
                            <div class="h-32 w-32 shrink-0 overflow-hidden rounded-2xl bg-gray-100 shadow-inner">
                                <img v-if="selectedCollect.image" :src="`/storage/${selectedCollect.image}`" class="h-full w-full object-cover" />
                            </div>
                            <div class="flex flex-col justify-center">
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ selectedCollect.name }}</h3>
                                <p class="text-sm font-bold text-indigo-600">{{ selectedCollect.club?.name || 'Indépendant' }}</p>
                            </div>
                        </div>

                        <div class="space-y-4 rounded-2xl bg-gray-50 p-6 dark:bg-gray-800/50 text-sm">
                            <p class="text-gray-600 dark:text-gray-300">
                                <span class="font-bold text-gray-400 uppercase text-[10px] block mb-1">Description</span>
                                {{ selectedCollect.description || 'Aucune description.' }}
                            </p>

                            <div v-if="selectedCollect.categories?.length">
                                <span class="font-bold text-gray-400 uppercase text-[10px] block mb-2">Catégories</span>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="cat in selectedCollect.categories" :key="cat.id" class="rounded-lg bg-white px-2 py-1 text-[10px] font-bold shadow-sm dark:bg-gray-700">
                                        {{ cat.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link :href="route('elements.listeElem', selectedCollect.slug)" class="flex-1">
                                <Button class="w-full rounded-xl bg-gray-900 py-6 font-black text-white hover:bg-gray-800 dark:bg-indigo-600 dark:hover:bg-indigo-700">
                                    Voir les éléments
                                </Button>
                            </Link>

                            <div v-if="selectedCollect.can_edit" class="flex w-full gap-2">
                                <Link :href="route('collections.editCollec', selectedCollect.slug)" class="flex-1">
                                    <Button class="w-full rounded-xl border-2 border-gray-100 py-6 font-bold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                                        Modifier
                                    </Button>
                                </Link>
                                <Button @click="deleteCollect(selectedCollect.slug)" class="rounded-xl bg-red-50 px-6 py-6 font-bold text-red-600 hover:bg-red-100 dark:bg-red-900/20">
                                    Supprimer
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
