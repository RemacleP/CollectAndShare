<script setup>
import { ref, watch } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';
import { Search, MapPin, Users, ArrowRight, Plus, Info, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {route} from "ziggy-js";

const props = defineProps({
    clubs: Object, // Cet objet contient maintenant meta et links de la pagination
    filters: Object
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('clubs.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('clubs.index'));
    }
};
</script>

<template>
    <Head title="Les Clubs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Exploration des Clubs
                </h2>
                <button
                    @click="goBack"
                    class="group flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors"
                >
                    <ChevronLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Retour
                </button>
            </div>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex flex-col md:flex-row gap-4 justify-between items-center mb-8 md:mb-12">
                    <div class="relative w-full md:w-1/3 group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400 group-focus-within:text-indigo-500 transition-colors" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher un club..."
                            class="w-full pl-12 pr-4 py-3 md:py-4 bg-white dark:bg-zinc-900 border-zinc-100 dark:border-zinc-800 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl shadow-sm dark:text-white transition-all text-sm md:text-base font-medium"
                        />
                    </div>

                    <Link
                        v-if="$page.props.auth.user?.is_admin"
                        :href="route('clubs.create')"
                        class="w-full md:w-auto flex items-center justify-center gap-2 bg-zinc-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-500 text-white px-6 py-3 md:py-4 rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-[0.2em] transition-all active:scale-95 shadow-lg shrink-0"
                    >
                        <Plus class="w-4 h-4" />
                        Créer un club
                    </Link>
                </div>

                <div v-if="clubs.data.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 mb-12">
                        <div
                            v-for="club in clubs.data"
                            :key="club.id"
                            class="group bg-white dark:bg-zinc-900 rounded-[2rem] md:rounded-[2.5rem] overflow-hidden border border-zinc-100 dark:border-zinc-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col"
                        >
                            <div class="relative aspect-video w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0">
                                <img :src="club.image_url" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" :alt="club.name" />
                            </div>

                            <div class="p-6 md:p-8 flex-grow min-w-0">
                                <h3 class="font-black text-xl md:text-2xl text-zinc-900 dark:text-white uppercase tracking-tighter leading-tight mb-2 break-words italic">
                                    {{ club.name }}
                                </h3>
                                <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400 text-xs md:text-sm font-bold mb-4 italic">
                                    <MapPin class="w-3.5 h-3.5 text-indigo-500 shrink-0" />
                                    <span class="truncate">{{ club.location?.city || 'Ville non précisée' }}</span>
                                </div>
                                <p class="text-zinc-600 dark:text-zinc-300 line-clamp-2 text-xs md:text-sm leading-relaxed break-words">
                                    {{ club.description || 'Pas de description disponible.' }}
                                </p>
                            </div>

                            <div class="px-6 py-4 md:px-8 md:py-6 bg-zinc-50/50 dark:bg-zinc-800/30 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-[9px] md:text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-widest truncate italic">
                                        {{ club.members }} Membres
                                    </span>
                                </div>
                                <Link :href="route('clubs.show', club.slug)" class="shrink-0 inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-black text-[10px] md:text-xs uppercase tracking-widest group/btn italic">
                                    Voir <ArrowRight class="w-3 h-3 md:w-4 md:h-4 transition-transform group-hover/btn:translate-x-1" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-if="clubs.links && clubs.links.length > 3" class="flex flex-wrap justify-center items-center gap-2 pb-10">
                        <template v-for="(link, k) in clubs.links" :key="k">
                            <div v-if="link.url === null"
                                 class="px-4 py-3 text-zinc-400 dark:text-zinc-600 text-xs font-black uppercase tracking-widest border border-transparent"
                                 v-html="link.label" />

                            <Link v-else
                                  :href="link.url"
                                  class="px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all"
                                  :class="{
                                      'bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none': link.active,
                                      'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-100 dark:border-zinc-800 hover:border-indigo-500 hover:text-indigo-600': !link.active
                                  }"
                                  v-html="link.label" />
                        </template>
                    </div>
                </div>

                <div v-else class="text-center py-16 md:py-24 bg-white dark:bg-zinc-900 rounded-[2rem] md:rounded-[3rem] border border-zinc-100 dark:border-zinc-800 mx-auto max-w-lg transition-colors">
                    <Info class="w-12 h-12 text-zinc-200 dark:text-zinc-700 mx-auto mb-4" />
                    <h3 class="text-lg md:text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tighter px-4">Aucun résultat trouvé</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-2 px-6">Modifiez votre recherche pour trouver un club.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
