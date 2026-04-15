<script setup>
import { ref, watch } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce'; // Optionnel : pour ne pas requêter à chaque lettre

const props = defineProps({
    clubs: Object,
    filters: Object
});

const search = ref(props.filters.search || '');

// On utilise debounce pour attendre 300ms après la fin de la frappe
watch(search, debounce((value) => {
    router.get(route('clubs.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));
</script>

<template>
    <Head title="Les Clubs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Liste des Clubs</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher un club..."
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-1/3"
                    />

                    <Link :href="route('clubs.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow transition">
                        + Créer un club
                    </Link>
                </div>

                <div v-if="clubs.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="club in clubs.data" :key="club.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 flex flex-col">
                        <img :src="club.image_url" class="h-48 w-full object-cover" :alt="club.name" />

                        <div class="p-6 flex-grow">
                            <h3 class="font-bold text-xl text-gray-900 mb-2">{{ club.name }}</h3>
                            <p class="text-gray-500 text-sm mb-4">
                                📍 {{ club.location?.city || 'Ville non précisée' }}
                            </p>
                            <p class="text-gray-600 line-clamp-2 mb-4">
                                {{ club.description || 'Pas de description.' }}
                            </p>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ club.members }} membre(s)
                            </span>
                            <Link :href="route('clubs.show', club.slug)" class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm">
                                Voir les détails →
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white rounded-lg shadow">
                    <p class="text-gray-500">Aucun club ne correspond à votre recherche.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
