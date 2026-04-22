<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/Components/ui/button";
import {
    Trash2,
    Plus,
    Minus,
    ShoppingBag,
    ArrowRight,
    Store,
    CreditCard
} from 'lucide-vue-next';
import axios from "axios";

const props = defineProps<{
    cart: any; // On part sur la structure groupée par vendeur
}>();

const handlePayment = async () => {
    try {
        // 1. On appelle notre serveur
        const response = await axios.post(route('checkout'));

        // 2. On récupère l'URL de la session Stripe
        const checkoutUrl = response.data.url;

        if (checkoutUrl) {
            // 3. Redirection directe vers Stripe
            window.location.href = checkoutUrl;
        } else {
            console.error("L'URL de paiement n'a pas été générée.");
        }

    } catch (err) {
        console.error("Erreur:", err.response?.data?.error || err.message);
        alert("Erreur lors de l'initialisation du paiement.");
    }
};

// --- Fonctions ---
function updateQuantity(itemId, qty) {
    router.patch(route('cart.update', itemId), { quantity: qty }, { preserveScroll: true });
}

function removeItem(itemId) {
    if(confirm('Retirer cet objet du panier ?')) {
        router.delete(route('cart.remove', itemId), { preserveScroll: true });
    }
}

function checkoutSeller(sellerId) {
    // Paiement spécifique à un vendeur
    router.get(route('checkout.seller', sellerId));
}

function checkoutAll() {
    // Paiement global
    router.get(route('checkout.all'));
}
</script>

<template>
    <Head title="Mon Panier" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto p-6">
            <div class="flex items-center gap-4 mb-10">
                <div class="p-3 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none">
                    <ShoppingBag class="w-8 h-8 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Mon Panier</h1>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">Vous avez {{ cart.total_items }} objets en attente</p>
                </div>
            </div>

            <div v-if="cart.groups.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                <div class="lg:col-span-2 space-y-10">
                    <div v-for="group in cart.groups" :key="group.seller.id"
                         class="bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-gray-100 dark:border-zinc-800 overflow-hidden shadow-sm">

                        <div class="p-6 border-b border-gray-50 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Store class="w-5 h-5 text-indigo-500" />
                                <span class="font-black text-gray-900 dark:text-white uppercase tracking-wider text-sm">
                                    Collection de {{ group.seller.username }}
                                </span>
                            </div>
                            <Button @click="checkoutSeller(group.seller.id)" variant="ghost" class="text-indigo-600 dark:text-indigo-400 font-bold hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                Payer ce vendeur uniquement <ArrowRight class="ml-2 w-4 h-4" />
                            </Button>
                        </div>

                        <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                            <div v-for="item in group.items" :key="item.id" class="p-6 flex items-center gap-6">
                                <img :src="`/storage/${item.element.image}`" class="w-20 h-20 rounded-2xl object-cover" />

                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white uppercase">{{ item.element.label }}</h4>
                                    <p class="text-sm text-indigo-600 dark:text-indigo-400 font-black">{{ item.element.price }}€</p>
                                </div>

                                <div class="flex items-center bg-zinc-100 dark:bg-zinc-800 rounded-xl p-1">
                                    <button @click="updateQuantity(item.id, item.quantity - 1)" class="w-8 h-8 flex items-center justify-center dark:text-white">-</button>
                                    <span class="w-8 text-center font-bold dark:text-white">{{ item.quantity }}</span>
                                    <button @click="updateQuantity(item.id, item.quantity + 1)" class="w-8 h-8 flex items-center justify-center dark:text-white">+</button>
                                </div>

                                <button @click="removeItem(item.id)" class="text-gray-300 hover:text-red-500 transition-colors p-2">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-200 dark:shadow-none sticky top-24">
                        <h3 class="text-xl font-black uppercase tracking-widest mb-6">Résumé Global</h3>

                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between opacity-80 font-medium">
                                <span>Sous-total</span>
                                <span>{{ cart.total_price }}€</span>
                            </div>
                            <div class="flex justify-between opacity-80 font-medium">
                                <span>Frais de port (est.)</span>
                                <span>Gratuit</span>
                            </div>
                            <div class="pt-4 border-t border-white/20 flex justify-between text-2xl font-black">
                                <span>Total</span>
                                <span>{{ cart.total_price }}€</span>
                            </div>
                        </div>


                        <Button @click="handlePayment" class="w-full bg-green-600 hover:bg-green-700">
                            Payer avec Stripe
                        </Button>

                        <p class="mt-4 text-[10px] text-center opacity-60 font-bold uppercase tracking-widest">
                            Paiement sécurisé via Collect&Share
                        </p>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 bg-white dark:bg-zinc-900 rounded-[3rem] border border-dashed border-zinc-200 dark:border-zinc-800">
                <ShoppingBag class="w-16 h-16 mx-auto text-zinc-300 mb-4" />
                <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase">Votre panier est vide</h2>
                <p class="text-zinc-500 mt-2 mb-8">Il est temps de dénicher de nouvelles perles rares !</p>
                <Link :href="route('collections.listeCollec')">
                    <Button class="rounded-xl bg-indigo-600 text-white font-bold px-8">Explorer les collections</Button>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
