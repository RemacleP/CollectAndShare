<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    orders: Array
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Mon Historique" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes Commandes</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="orders.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    Vous n'avez pas encore passé de commande.
                </div>

                <div v-else v-for="order in orders" :key="order.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <div class="flex justify-between items-center border-b pb-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Commande n°{{ order.id }}</p>
                            <p class="font-bold text-lg text-indigo-600">{{ formatDate(order.created_at) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total payé</p>
                            <p class="font-bold text-xl">{{ order.total_amount }}€</p>
                        </div>
                    </div>

                    <table class="w-full text-left">
                        <thead>
                        <tr class="text-gray-500 text-sm uppercase">
                            <th class="pb-2">Article</th>
                            <th class="pb-2 text-center">Quantité</th>
                            <th class="pb-2 text-right">Prix Unitaire</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in order.items" :key="item.id" class="border-t">
                            <td class="py-3 font-medium">{{ item.label }}</td>
                            <td class="py-3 text-center">{{ item.quantity }}</td>
                            <td class="py-3 text-right">{{ item.price }}€</td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 flex justify-end">
                         <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 uppercase">
                            {{ order.status }}
                         </span>
                    </div>
                    <div class="mt-4 flex justify-end items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 uppercase">
                            {{ order.status }}
                        </span>

                        <a :href="route('order.invoice', order.id)"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Facture PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
