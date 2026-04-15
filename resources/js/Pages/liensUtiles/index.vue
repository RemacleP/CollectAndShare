<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { route } from "ziggy-js";

const props = defineProps<{
    liensUtiles: Array<any>,
    isAdmin: boolean
}>();


function deleteLien(id: number) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce lien utile ?")) {
        router.delete(route("liensUtiles.delete", id));
    }
}

// Petite fonction pour générer une couleur de fond basée sur le nom
const getBgColor = (name: string) => {
    const colors = ['bg-blue-100 text-blue-600', 'bg-emerald-100 text-emerald-600', 'bg-purple-100 text-purple-600', 'bg-amber-100 text-amber-600'];
    const index = name.length % colors.length;
    return colors[index];
};
</script>

<template>
    <Head title="Liens Utiles">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto p-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                <div>
                    <h1 class="text-4xl font-black text-gray tracking-tight uppercase">
                        Liens Utiles
                    </h1>
                    <div class="h-1.5 w-20 bg-indigo-600 mt-2 rounded-full"></div>
                </div>

                <div v-if="isAdmin">
                    <Link :href="route('liensUtiles.create')">
                        <Button class="bg-indigo-600 hover:bg-indigo-800 text-white px-6 py-6 rounded-xl shadow-lg transition-transform hover:scale-105">
                            + Ajouter un lien
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="liensUtiles.length > 0" class="grid grid-cols-1 gap-4">
                <div
                    v-for="lien in liensUtiles"
                    :key="lien.id"
                    class="group flex flex-col sm:flex-row sm:items-center justify-between bg-white border border-gray-400 p-5 rounded-2xl shadow-lg hover:shadow-md transition-all duration-300"
                >
                    <div class="flex items-center gap-5">
                        <div :class="['w-12 h-12 flex items-center justify-center rounded-xl font-bold text-lg shadow-sm', getBgColor(lien.nom)]">
                            {{ lien.nom.charAt(0).toUpperCase() }}
                        </div>

                        <div>
                            <a
                                :href="lien.url"
                                target="_blank"
                                rel="noopener"
                                class="text-xl font-semibold text-gray-800 hover:text-indigo-600 block transition-colors"
                            >
                                {{ lien.nom }}
                            </a>
                            <span class="text-sm text-gray-400 font-mono">{{ lien.url.replace('https://', '').replace('www.', '') }}</span>
                        </div>
                    </div>

                    <div v-if="isAdmin" class="flex items-center gap-3 mt-4 sm:mt-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                        <Link :href="route('liensUtiles.edit', lien.id)">
                            <Button class="bg-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white border-none transition-colors">
                                Éditer
                            </Button>
                        </Link>
                        <Button
                            @click="deleteLien(lien.id)"
                            class="bg-red-200 text-red-600 hover:bg-red-600 hover:text-white border-none transition-colors"
                        >
                            Supprimer
                        </Button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                <p class="text-gray-400 font-medium italic">Aucune ressource répertoriée pour le moment.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
div {
    font-family: 'Inter', sans-serif;
}
</style>
