<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/Components/ui/button";
import {
    ShoppingCart,
    ArrowLeftRight,
    Pencil,
    Trash2,
    ChevronLeft,
    ChevronRight,
    History,
    X
} from 'lucide-vue-next';

const props = defineProps<{
    collect: any;
    element: any;
    can_edit: boolean;
}>();

// --- État ---
const isZoomOpen = ref(false);
const activeImage = ref(props.element.images?.[0]?.path || props.element.image);
const requestedQuantity = ref(1);

// --- Calculs ---
const breadcrumbs = computed(() => [
    { title: 'Accueil', href: route('home') },
    { title: 'Collections', href: route('collections.listeCollec') },
    { title: props.collect.name, href: route('elements.listeElem', props.collect.slug) },
    { title: props.element.label }
]);

const currentIndex = computed(() => {
    return props.element.images.findIndex(img => img.path === activeImage.value);
});

// --- Actions ---
function addToCart() {
    router.post(route('cart.add', props.element.id), {
        quantity: requestedQuantity.value
    }, { preserveScroll: true });
}

function proposeExchange() {
    router.get(route('exchanges.create', { element_id: props.element.id }));
}

function deleteElem() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet objet définitivement ?')) {
        router.delete(route('elements.deleteElem', {
            collection: props.collect.slug,
            element: props.element.slug
        }));
    }
}

// --- Navigation Galerie ---
function nextImage() {
    if (!props.element.images?.length) return;
    const nextIdx = (currentIndex.value + 1) % props.element.images.length;
    activeImage.value = props.element.images[nextIdx].path;
}

function prevImage() {
    if (!props.element.images?.length) return;
    const prevIdx = (currentIndex.value - 1 + props.element.images.length) % props.element.images.length;
    activeImage.value = props.element.images[prevIdx].path;
}

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') isZoomOpen.value = false;
    if (isZoomOpen.value || e.target === document.body) {
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
    }
};

onMounted(() => window.addEventListener('keydown', handleKeyDown));
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));
</script>

<template>
    <Head :title="element.label" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-6 transition-colors duration-300">

            <div class="mb-8 flex items-center justify-between">
                <Link :href="route('elements.listeElem', collect.slug)" class="group flex items-center text-sm font-bold text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-full border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm group-hover:border-indigo-100 dark:group-hover:border-indigo-900 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/30">
                        <ChevronLeft class="h-4 w-4" />
                    </div>
                    Retour à la collection
                </Link>

                <div v-if="can_edit" class="flex gap-2">
                    <Link :href="route('elements.editElem', { collection: collect.slug, element: element.slug })">
                        <Button variant="outline" class="rounded-xl border-gray-200 dark:border-gray-800 font-bold hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:text-gray-200">
                            <Pencil class="mr-2 h-4 w-4" /> Modifier
                        </Button>
                    </Link>
                    <Button @click="deleteElem" variant="outline" class="rounded-xl border-gray-200 dark:border-gray-800 font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 hover:border-red-100">
                        <Trash2 class="mr-2 h-4 w-4" /> Supprimer
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">

                <div class="space-y-6">
                    <div class="group relative aspect-square overflow-hidden rounded-[2.5rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-2xl shadow-indigo-100/50 dark:shadow-none">
                        <button v-if="element.images?.length > 1" @click.stop="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-xl rounded-full opacity-0 group-hover:opacity-100 transition-all hover:bg-white dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400">
                            <ChevronLeft class="w-6 h-6" />
                        </button>

                        <img
                            :src="`/storage/${activeImage}`"
                            @click="isZoomOpen = true"
                            class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105 cursor-zoom-in"
                            alt="Agrandir l'image"
                        />

                        <button v-if="element.images?.length > 1" @click.stop="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-xl rounded-full opacity-0 group-hover:opacity-100 transition-all hover:bg-white dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400">
                            <ChevronRight class="w-6 h-6" />
                        </button>
                    </div>

                    <div v-if="element.images?.length > 1" class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
                        <button
                            v-for="img in element.images"
                            :key="img.id"
                            @click="activeImage = img.path"
                            class="w-24 h-24 rounded-2xl overflow-hidden border-2 transition-all flex-shrink-0 shadow-sm"
                            :class="activeImage === img.path ? 'border-indigo-500 scale-105 shadow-indigo-200 dark:shadow-indigo-900' : 'border-white dark:border-gray-800 opacity-60 hover:opacity-100'"
                        >
                            <img :src="`/storage/${img.path}`" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <div class="flex flex-col justify-center">
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-800 italic">
                                    {{ element.condition }}
                                </span>
                                <span class="px-3 py-1 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-gray-100 dark:border-gray-700">
                                    {{ element.year_production }}
                                </span>
                            </div>
                            <h1 class="text-5xl font-black text-gray-900 dark:text-white tracking-tight leading-tight uppercase">
                                {{ element.label }}
                            </h1>
                        </div>

                        <div class="text-xl text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                            {{ element.description }}
                        </div>

                        <div v-if="element.history" class="p-8 bg-indigo-50/30 dark:bg-indigo-950/20 rounded-[2rem] border border-indigo-100/50 dark:border-indigo-900/30 relative overflow-hidden group">
                            <History class="absolute -right-4 -top-4 w-24 h-24 text-indigo-100/40 dark:text-indigo-900/20 transition-transform group-hover:scale-110" />
                            <h3 class="font-black text-indigo-400 dark:text-indigo-500 text-[10px] uppercase tracking-[0.2em] mb-3 relative z-10">L'histoire de cet objet</h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed relative z-10 italic">"{{ element.history }}"</p>
                        </div>

                        <div class="grid grid-cols-2 gap-8 py-8 border-y border-gray-50 dark:border-gray-800">
                            <div class="space-y-1">
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-widest">Valeur estimée</p>
                                <p class="text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ element.price }}<span class="text-xl ml-1">€</span></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-widest">Disponibilité</p>
                                <p class="text-4xl font-black text-gray-900 dark:text-white">x{{ element.quantity }}</p>
                            </div>
                        </div>

                        <div v-if="!can_edit" class="pt-4 space-y-4">
                            <div class="flex gap-4">
                                <div class="flex items-center bg-gray-50 dark:bg-gray-900 border-2 border-gray-100 dark:border-gray-800 rounded-2xl p-1">
                                    <button @click="requestedQuantity > 1 ? requestedQuantity-- : null" class="w-10 h-10 flex items-center justify-center font-bold text-gray-400 hover:text-indigo-600 transition-colors">-</button>
                                    <span class="w-10 text-center font-black text-lg dark:text-white">{{ requestedQuantity }}</span>
                                    <button @click="requestedQuantity < element.quantity ? requestedQuantity++ : null" class="w-10 h-10 flex items-center justify-center font-bold text-gray-400 hover:text-indigo-600 transition-colors">+</button>
                                </div>

                                <Button
                                    @click="addToCart"
                                    :disabled="element.quantity === 0"
                                    class="flex-1 h-16 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-lg shadow-xl shadow-indigo-100 dark:shadow-none transition-all active:scale-95 disabled:bg-gray-200 dark:disabled:bg-gray-800"
                                >
                                    <ShoppingCart class="w-5 h-5 mr-2" />
                                    {{ element.quantity > 0 ? 'Ajouter au panier' : 'Rupture de stock' }}
                                </Button>
                            </div>

                            <Button
                                @click="proposeExchange"
                                variant="outline"
                                class="w-full h-16 rounded-2xl border-2 border-indigo-100 dark:border-indigo-900 bg-indigo-50/30 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 font-black text-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-all active:scale-95"
                            >
                                <ArrowLeftRight class="w-5 h-5 mr-2" /> Proposer un échange
                            </Button>
                        </div>

                        <div v-else class="pt-4 p-6 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl text-center">
                            <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">C'est votre objet</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Gérez votre pièce via les options en haut de page.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="fade">
                <div v-if="isZoomOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-md">
                    <button @click="isZoomOpen = false" class="absolute top-6 right-6 z-[110] text-white/50 hover:text-white transition-all">
                        <X class="w-12 h-12" />
                    </button>

                    <button v-if="element.images?.length > 1" @click="prevImage" class="absolute left-4 md:left-10 z-[110] p-6 text-white/30 hover:text-white hover:bg-white/10 rounded-full transition-all">
                        <ChevronLeft class="w-16 h-16" />
                    </button>

                    <div class="relative w-full h-full flex items-center justify-center p-4 md:p-20" @click="isZoomOpen = false">
                        <Transition mode="out-in" name="scale-fade">
                            <img :key="activeImage" :src="`/storage/${activeImage}`" class="max-w-full max-h-full object-contain rounded-sm shadow-2xl" @click.stop />
                        </Transition>
                    </div>

                    <button v-if="element.images?.length > 1" @click="nextImage" class="absolute right-4 md:right-10 z-[110] p-6 text-white/30 hover:text-white hover:bg-white/10 rounded-full transition-all">
                        <ChevronRight class="w-16 h-16" />
                    </button>

                    <div v-if="element.images?.length > 1" class="absolute bottom-10 text-white/50 font-mono text-sm tracking-widest">
                        {{ currentIndex + 1 }} / {{ element.images.length }}
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.scale-fade-enter-active, .scale-fade-leave-active { transition: all 0.3s ease; }
.scale-fade-enter-from { opacity: 0; transform: scale(0.95); }
.scale-fade-leave-to { opacity: 0; transform: scale(1.05); }
</style>
