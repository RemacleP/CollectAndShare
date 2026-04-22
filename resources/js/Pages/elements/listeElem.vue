<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from "vue";
import { Button } from "@/Components/ui/button";
import { Plus, Package, Tag } from 'lucide-vue-next';

// Interface
interface Element {
    id: number;
    label: string;
    description: string;
    year_production: number;
    price: number;
    quantity: number;
    slug: string;
    image: string | null;
    images?: { id: number; path: string }[];
}

const props = defineProps<{
    collect: { id: number; name: string; slug: string; };
    elements: Element[];
    isAdmin: boolean;
    userId: number | null;
    collectionOwnerUserId: number | null;
}>();

// --- Logique de Densité ---
const density = ref(localStorage.getItem('viewDensity') || 'normal');

const gridClasses = computed(() => {
    return {
        'compact': 'grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3',
        'normal': 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6',
        'large': 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8',
    }[density.value];
});

/**
 * LOGIQUE D'AUTORISATION :
 * Un utilisateur peut ajouter un objet s'il est Admin
 * OU s'il est le propriétaire de la collection.
 */
const canManageCollection = computed(() => {
    if (props.isAdmin) return true;
    return props.userId !== null && props.collectionOwnerUserId === props.userId;
});

const breadcrumbs = [
    { title: 'Collections', href: route('collections.listeCollec') },
    { title: props.collect.name, href: '#' },
];
</script>

<template>
    <Head :title="`Éléments - ${collect.name}`" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-indigo-600 font-bold uppercase text-[10px] tracking-widest">
                        <Package class="h-3 w-3" />
                        Collection
                    </div>
                    <h1 class="text-4xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                        {{ collect.name }}
                    </h1>
                </div>

                <div class="flex gap-3">
                    <Link
                        v-if="canManageCollection"
                        :href="route('elements.createElem', { collection: collect.slug })"
                    >
                        <Button class="rounded-2xl bg-indigo-600 px-6 py-6 font-bold text-white shadow-lg hover:bg-indigo-700 transition-all active:scale-95">
                            <Plus class="mr-2 h-5 w-5" /> Ajouter un objet
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid transition-all duration-500" :class="gridClasses">
                <Link
                    v-for="elem in elements"
                    :key="elem.id"
                    :href="route('elements.show', {
                        collection: collect.slug,
                        element: elem.slug
                    })"
                    class="group cursor-pointer overflow-hidden bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 transition-all hover:shadow-2xl hover:-translate-y-1"
                    :class="density === 'compact' ? 'rounded-2xl p-2' : 'rounded-[2.5rem] p-3 shadow-sm'"
                >
                    <div class="relative overflow-hidden" :class="density === 'compact' ? 'rounded-xl aspect-square' : 'rounded-[2rem] aspect-square'">

                        <img
                            v-if="elem.images && elem.images.length > 0"
                            :src="`/storage/${elem.images[0].path}`"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                        />
                        <img
                            v-else-if="elem.image"
                            :src="`/storage/${elem.image}`"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center bg-gray-50 dark:bg-zinc-800">
                            <Package class="h-10 w-10 text-gray-300" />
                        </div>

                        <div class="absolute bottom-3 right-3 bg-white/90 dark:bg-zinc-900/90 backdrop-blur px-3 py-1 rounded-full shadow-sm border border-white/20">
                            <span class="text-[10px] font-black text-indigo-600">{{ elem.price }} €</span>
                        </div>
                    </div>

                    <div :class="density === 'compact' ? 'p-1 mt-1' : 'p-4 mt-1'">
                        <h3 class="truncate font-black text-gray-900 dark:text-white" :class="density === 'compact' ? 'text-[10px]' : 'text-lg'">
                            {{ elem.label }}
                        </h3>
                        <div class="flex items-center justify-between mt-1">
                            <p v-if="density !== 'compact'" class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                Stock: {{ elem.quantity }}
                            </p>
                            <Tag v-if="density !== 'compact'" class="h-3 w-3 text-zinc-300" />
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="elements.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
                <div class="p-6 rounded-full bg-gray-50 dark:bg-zinc-900 mb-4 border border-gray-100 dark:border-zinc-800">
                    <Package class="h-12 w-12 text-zinc-200" />
                </div>
                <h3 class="text-xl font-bold text-zinc-400">Cette collection est vide</h3>
                <p class="text-sm text-zinc-300 max-w-xs mx-auto mt-2">
                    Il semble que vous n'ayez pas encore ajouté d'objets à cette collection.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajout d'une transition douce pour le changement de grille */
.grid {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
