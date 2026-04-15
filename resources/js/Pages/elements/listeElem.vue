<script setup lang="ts">
import { route } from 'ziggy-js';
import {Head, Link, router, useForm} from '@inertiajs/vue3'; // Ajout de useForm
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import {computed, onMounted, ref} from "vue";
import type {BreadcrumbItem} from "@/types";
import {listeCollec} from "@/routes/collections";
import {Button} from "@/components/ui/button";

// Interfaces
interface Element {
    id: number;
    label: string;
    description: string;
    year_production: number;
    history: string;
    condition: string;
    price: number;
    quantity: number;
    collection_id: number;
    slug: string;
    image: string;
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
    };
    elements: Element[];
    myElements: Element[]; // <--- NOUVEAU : Les éléments de l'utilisateur connecté
    open: {
        type: [String, Number],
        default: null
    },
    isAdmin: boolean;
    isVisitor: boolean;
    userId: number | null;
    collectionOwnerUserId: number | null;
}>();

const page = usePage();
const currentUser = page.props.auth.user;

// --- Gestion du Panier ---
function addToCart(elementId: number) {
    if (!currentUser) {
        alert("Vous devez être connecté pour ajouter au panier.");
        return;
    }
    router.post(route('cart.add', elementId), {}, { preserveScroll: true });
}

// --- Gestion des Échanges ---
const showExchangeModal = ref(false); // État pour la modale d'échange
const targetElementForExchange = ref<Element | null>(null);

// Form exchange
const exchangeForm = useForm({
    offered_element_id: null as number | null, // null = aucun objet proposé
    message: ''
});

function openExchangeModal(elem: Element) {
    if (!currentUser) {
        alert("Vous devez être connecté pour demander un échange.");
        return;
    }

    // Check if the element belongs to the user
    const isMine = props.myElements.some(e => e.id === elem.id);
    if (isMine) {
        alert("Vous possédez déjà cet objet.");
        return;
    }

    targetElementForExchange.value = elem;
    showExchangeModal.value = true;
    // Close the details modal if open
    showModal.value = false;
    exchangeForm.reset();
}

function submitExchange() {
    if (!targetElementForExchange.value) return;

    exchangeForm.post(route('exchanges.store', targetElementForExchange.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showExchangeModal.value = false;
        },
        onError: () => {
            alert("Une erreur est survenue lors de la demande.");
        }
    });
}

// --- Gestion Modale Détails ---
const showModal = ref(false);
const selectedElement = ref<Element | null>(null);

function openModal(elem: Element) {
    selectedElement.value = elem;
    showModal.value = true;
}

function deleteElem(slug: string) {
    if (confirm('Voulez-vous vraiment supprimer cet élément ?')) {
        router.delete(route('elements.deleteElem',{
            currentCollect: props.collect.slug,
            currentElem: slug
        }));
    }
    showModal.value = false;
}
onMounted(() => {
    console.log("props.open =", props.open);

    const openId = Number(props.open);
    const elem = props.elements.find(e => e.id === openId);

    console.log("Element trouvé :", elem);

    if (elem) openModal(elem);
});

const isOwnerOfCollection = computed(() => {
    return (
        props.userId !== null &&
        props.collectionOwnerUserId === props.userId
    );
});


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Accueil', href: route('home') },
    {
        title: props.collect.name
            ? `Collection  : ${props.collect.name}`
            : 'Collection :',
        href: listeCollec().url
    },
    { title: 'Elements', href: route ('elements.listeElem',props.collect.slug)},
];
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <h1 class="mb-4 text-2xl font-bold">LISTE DES ELEMENTS</h1>
        <h2 class="mb-4 text-2xl font-bold">Collection : {{props.collect.name}}</h2>

        <div class="flex gap-4 mb-6">
            <div v-if="!isAdmin && isVisitor">
                <div v-if="isOwnerOfCollection">
                    <Link :href="route('elements.createElem', props.collect.slug)">
                        <Button class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                            Ajouter des éléments
                        </Button>
                    </Link>
                </div>
            </div>
            <Link :href="route('collections.listeCollec')">
                <Button class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Retour
                </Button>
            </Link>
        </div>

        <!-- Grille des éléments -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6">
            <div
                v-for="elem in elements"
                :key="elem.id"
                class="border rounded-lg shadow-lg border border-gray-300 overflow-hidden cursor-pointer transition-all duration-300 ease-in-out transform hover:scale-105 "
            >
                <div @click="openModal(elem)" class="p-4 ">
                    <img
                        v-if="elem.image"
                        :src="`/storage/${elem.image}`"
                        class="w-full h-48 object-cover rounded-t-lg"
                        :alt="elem.label"
                    />
                    <div v-else class="w-full h-48 bg-gray-200 flex items-center justify-center rounded-t-lg">
                        <span class="text-gray-500">Pas d'image</span>
                    </div>

                    <div class="mt-2 text-center">
                        <p class="font-bold text-lg truncate ">{{ elem.label }}</p>
                        <p class="text-sm text-indigo-600">Prix: {{ elem.price }} €</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale 1 Element details -->
        <div
            v-if="showModal && selectedElement"
            class="fixed inset-0  bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-40"
            @click.self="showModal = false"
        >
            <div class="bg-blue-950 text-white rounded-lg shadow-2xl p-6 max-w-lg w-full relative border border-blue-800">
                <button
                    @click="showModal = false"
                    class="absolute top-2 right-2 text-gray-400 hover:text-white text-2xl"
                >
                    &times;
                </button>

                <h3 class="text-2xl font-bold mb-4 border-b border-blue-800 pb-2">{{ selectedElement.label }}</h3>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <img
                            v-if="selectedElement.image"
                            :src="`/storage/${selectedElement.image}`"
                            class="w-40 h-40 object-cover rounded border border-blue-800"
                            :alt="selectedElement.label"
                        />
                        <div v-else class="w-40 h-40 bg-gray-700 flex items-center justify-center rounded">
                            <span class="text-gray-400 text-sm">Pas d'image</span>
                        </div>
                    </div>

                    <div class="space-y-1 text-sm flex-1">
                        <p><strong class="text-blue-300">PRIX :</strong> {{ selectedElement.price }} €</p>
                        <p><strong class="text-blue-300">QUANTITE :</strong> {{ selectedElement.quantity }}</p>
                        <p><strong class="text-blue-300">CONDITION :</strong> {{ selectedElement.condition }}</p>
                        <p><strong class="text-blue-300">ANNÉE :</strong> {{ selectedElement.year_production }}</p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <div v-if="!isAdmin && isVisitor && !isOwnerOfCollection" class="flex gap-2">
                                <Button @click="addToCart(selectedElement.id)" class="rounded bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 flex items-center gap-2 text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                    Panier
                                </Button>

                                <Button
                                    @click="openExchangeModal(selectedElement)"
                                    class="rounded bg-amber-500 px-3 py-2 text-white hover:bg-amber-600 flex items-center gap-2 text-xs transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-left"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                                    Échanger
                                </Button>
                            </div>

                                <div v-if="isOwnerOfCollection" class="flex gap-2">
                                    <Button
                                        class="rounded bg-red-600 px-3 py-2 text-white hover:bg-red-700 text-xs"
                                        @click="deleteElem(selectedElement.slug)">
                                        Supprimer
                                    </Button>

                                    <Link :href="route('elements.editElem', [props.collect.slug, selectedElement.slug])">
                                        <Button class="rounded bg-orange-600 px-3 py-2 text-white hover:bg-orange-700 text-xs">
                                            Modifier
                                        </Button>
                                    </Link>
                                </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-sm bg-blue-900/50 p-3 rounded">
                    <p><strong class="text-blue-300">DESCRIPTION :</strong> {{ selectedElement.description }}</p>
                    <p><strong class="text-blue-300">HISTORIQUE :</strong> {{ selectedElement.history }}</p>
                </div>
            </div>
        </div>

        <!-- Modal 2 Formular to exchange -->
        <div
            v-if="showExchangeModal && targetElementForExchange"
            class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="showExchangeModal = false"
        >
            <div class="bg-white rounded-lg shadow-2xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold mb-4 text-gray-800">Proposer un échange</h3>

                <p class="mb-4 text-sm text-gray-600">
                    Vous souhaitez obtenir : <span class="font-bold text-indigo-600">{{ targetElementForExchange.label }}</span>
                </p>

                <form @submit.prevent="submitExchange">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Que proposez-vous en échange ?
                        </label>

                        <div v-if="props.myElements && props.myElements.length > 0">
                            <select
                                v-model="exchangeForm.offered_element_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                            >
                                <option :value="null">-- Rien (Demander un don / Achat) --</option>
                                <option
                                    v-for="myElem in props.myElements"
                                    :key="myElem.id"
                                    :value="myElem.id"
                                >
                                    {{ myElem.label }} (Valeur: {{ myElem.price }}€)
                                </option>
                            </select>
                        </div>
                        <div v-else class="text-sm text-amber-600 bg-amber-50 p-2 rounded mb-2">
                            Vous n'avez aucun élément dans vos collections à échanger.
                            Vous pouvez envoyer une demande sans contrepartie.
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message (Optionnel)</label>
                        <textarea
                            v-model="exchangeForm.message"
                            rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                            placeholder="Bonjour, je suis très intéressé par cet objet..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button
                            type="button"
                            @click="showExchangeModal = false"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition"
                        >
                            Annuler
                        </Button>
                        <Button
                            type="submit"
                            class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700 transition flex items-center"
                            :disabled="exchangeForm.processing"
                        >
                            <span v-if="exchangeForm.processing" class="mr-2">Envoi...</span>
                            Envoyer la proposition
                        </Button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
