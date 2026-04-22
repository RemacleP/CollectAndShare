<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { onMounted, ref, computed } from 'vue';
import { Button } from "@/components/ui/button";
import { Info, Plus, Trash2, Pencil, LayoutGrid } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
}

interface Collection {
    id: number;
    name: string;
    description: string;
    club?: { id: number; name: string; address: string };
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

const density = ref(localStorage.getItem('viewDensity') || 'normal');

// Calcul des classes de la grille en fonction de la densité
const gridClasses = computed(() => {
    return {
        'compact': 'grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-3',
        'normal': 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6',
        'large': 'grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-8',
    }[density.value] || 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6';
});

// --- Logique du Modal ---
const showModal = ref(false);
const selectedCollect = ref<Collection | null>(null);

function openModal(collect: Collection) {
    selectedCollect.value = collect;
    showModal.value = true;
}

function deleteCollect(slug: string) {
    if (confirm('Voulez-vous vraiment supprimer cette collection ?')) {
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

const breadcrumbs = [
    { title: 'Accueil', href: route('home') },
    { title: 'Collections', href: route('collections.listeCollec') },
];
</script>

<template>
    <Head title="Exploration des collections" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">Exploration</h1>
                    <p class="text-sm text-gray-500 font-medium">Découvrez les trésors de la communauté</p>
                </div>

                <Link v-if="$page.props.auth.user" :href="route('collections.createCollec')">
                    <Button class="rounded-2xl bg-indigo-600 px-6 py-6 font-bold text-white shadow-xl shadow-indigo-100 transition-all hover:bg-indigo-700 hover:shadow-none active:scale-95 dark:shadow-none">
                        <Plus class="mr-2 h-5 w-5" /> Créer une collection
                    </Button>
                </Link>
            </div>

            <div v-if="myCollections.length" class="mb-12">
                <h2 class="mb-6 flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-indigo-600">
                    <span class="h-1.5 w-6 rounded-full bg-indigo-600"></span>
                    Mes Collections
                </h2>

                <div class="grid transition-all duration-500" :class="gridClasses">
                    <div
                        v-for="collect in myCollections"
                        :key="collect.id"
                        class="group relative"
                    >
                        <Link
                            :href="route('elements.listeElem', collect.slug)"
                            class="block overflow-hidden rounded-[2.5rem] border-2 border-indigo-50 bg-white p-2 shadow-sm transition-all hover:border-indigo-500 hover:shadow-2xl dark:border-zinc-800 dark:bg-zinc-900"
                        >
                            <div class="relative aspect-square overflow-hidden rounded-[2rem]">
                                <img v-if="collect.image" :src="`/storage/${collect.image}`" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                <div v-else class="flex h-full w-full items-center justify-center bg-indigo-50 text-indigo-200 dark:bg-zinc-800">
                                    <LayoutGrid class="h-12 w-12 opacity-20" />
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="truncate font-black text-gray-900 dark:text-white text-lg">{{ collect.name }}</h3>
                            </div>
                        </Link>

                        <button
                            @click.stop.prevent="openModal(collect)"
                            class="absolute top-5 right-5 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur transition-all hover:scale-110 hover:bg-white text-indigo-600 opacity-0 group-hover:opacity-100"
                        >
                            <Info class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="mb-6 flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-gray-400">
                    <span class="h-1.5 w-6 rounded-full bg-gray-300"></span>
                    Communauté
                </h2>

                <div class="grid transition-all duration-500" :class="gridClasses">
                    <div
                        v-for="collect in otherCollections"
                        :key="collect.id"
                        class="group relative"
                    >
                        <Link
                            :href="route('elements.listeElem', collect.slug)"
                            class="block overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white p-2 shadow-sm transition-all hover:shadow-2xl dark:border-zinc-800 dark:bg-zinc-900"
                        >
                            <div class="aspect-square overflow-hidden rounded-[2rem] bg-gray-50 dark:bg-zinc-800">
                                <img v-if="collect.image" :src="`/storage/${collect.image}`" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                <div v-else class="flex h-full w-full items-center justify-center text-gray-200">
                                    <LayoutGrid class="h-12 w-12 opacity-10" />
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="truncate font-bold text-gray-900 dark:text-white">{{ collect.name }}</h3>
                                <div class="mt-2 flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-black text-indigo-600 uppercase border border-indigo-200">
                                        {{ collect.club_user_role?.user?.username?.charAt(0) }}
                                    </div>
                                    <span class="text-xs font-bold text-gray-400">{{ collect.club_user_role?.user?.username }}</span>
                                </div>
                            </div>
                        </Link>

                        <button
                            @click.stop.prevent="openModal(collect)"
                            class="absolute top-5 right-5 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur transition-all hover:scale-110 hover:bg-white text-gray-600 opacity-0 group-hover:opacity-100"
                        >
                            <Info class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="showModal && selectedCollect"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-md bg-black/40 transition-all"
                @click.self="showModal = false"
            >
                <div class="relative w-full max-w-lg overflow-hidden rounded-[3rem] bg-white shadow-2xl dark:bg-zinc-950 border dark:border-zinc-800 animate-in zoom-in duration-200">
                    <button @click="showModal = false" class="absolute right-8 top-8 z-10 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        <span class="text-3xl">&times;</span>
                    </button>

                    <div class="p-10">
                        <div class="mb-8 flex gap-6 items-center">
                            <div class="h-28 w-28 shrink-0 overflow-hidden rounded-[1.5rem] shadow-2xl border-4 border-white dark:border-zinc-800">
                                <img v-if="selectedCollect.image" :src="`/storage/${selectedCollect.image}`" class="h-full w-full object-cover" />
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-gray-900 dark:text-white leading-tight">{{ selectedCollect.name }}</h3>
                                <p class="text-sm font-black text-indigo-600 uppercase tracking-widest mt-1">{{ selectedCollect.club?.name || 'Indépendant' }}</p>
                            </div>
                        </div>

                        <div class="space-y-6 rounded-[2rem] bg-gray-50 p-8 dark:bg-zinc-900/50">
                            <div>
                                <span class="font-black text-gray-400 uppercase text-[10px] block mb-2 tracking-widest">Description</span>
                                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                                    {{ selectedCollect.description || 'Aucune description fournie.' }}
                                </p>
                            </div>

                            <div v-if="selectedCollect.categories?.length">
                                <span class="font-black text-gray-400 uppercase text-[10px] block mb-3 tracking-widest">Tags</span>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="cat in selectedCollect.categories" :key="cat.id" class="rounded-xl bg-white px-3 py-1.5 text-[10px] font-black shadow-sm dark:bg-zinc-800 dark:text-gray-300 border dark:border-zinc-700">
                                        #{{ cat.name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 space-y-3">
                            <Link :href="route('elements.listeElem', selectedCollect.slug)">
                                <Button class="w-full rounded-2xl bg-zinc-900 py-7 font-black text-white hover:bg-black dark:bg-white dark:text-zinc-950 transition-all">
                                    Explorer les objets
                                </Button>
                            </Link>

                            <div v-if="selectedCollect.can_edit" class="flex gap-3">
                                <Link :href="route('collections.editCollec', selectedCollect.slug)" class="flex-1">
                                    <Button class="w-full rounded-2xl border-2 border-gray-100 py-7 font-bold text-gray-600 hover:bg-gray-50 dark:border-zinc-800 dark:text-gray-400 dark:hover:bg-zinc-900 transition-all">
                                        <Pencil class="mr-2 h-4 w-4" /> Modifier
                                    </Button>
                                </Link>
                                <Button @click="deleteCollect(selectedCollect.slug)" class="rounded-2xl bg-red-50 px-6 py-7 font-bold text-red-600 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400 transition-all">
                                    <Trash2 class="h-5 w-5" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
