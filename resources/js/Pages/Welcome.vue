<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Package,
    Users,
    Trophy,
    Sparkles,
    ArrowRight,
    Plus,
    Clock
} from 'lucide-vue-next';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    latestCollections: Array,
    topClubs: Array,
    latestElems: Array,
    latestClub: Object,
    latestUser: Object,
    latestCollection: Object,
    latestElement: Object,
});

const slides = computed(() => [
    {
        condition: props.latestClub,
        title: 'Nouveau Club',
        name: props.latestClub?.name,
        text: 'Une nouvelle communauté vient de voir le jour. Rejoignez-les !',
        bg: 'from-indigo-600 to-purple-600',
        link: route('clubs.show', props.latestClub?.slug || ''),
        badge: 'Bienvenue'
    },
    {
        condition: props.latestUser,
        title: 'Nouveau Membre',
        name: `${props.latestUser?.firstname} ${props.latestUser?.lastname}`,
        text: 'Un nouveau passionné a rejoint l\'aventure Collect & Share.',
        bg: 'from-blue-600 to-cyan-600',
        link: route('profile.show', { id: props.latestUser?.id || 0 }),
        badge: 'Nouvelle Recrue'
    },
    {
        condition: props.latestElement,
        title: 'Dernière Trouvaille',
        name: props.latestElement?.label,
        text: `Découvrez cet objet récemment ajouté dans ${props.latestElement?.collection?.name}`,
        bg: 'from-emerald-600 to-teal-600',
        link: route('elements.show', {
            collection: props.latestElement?.collection?.slug || '',
            element: props.latestElement?.slug || ''
        }),
        badge: 'Objet Rare'
    }
].filter(s => s.condition));

const current = ref(0);
let interval;

onMounted(() => {
    if (slides.value.length > 1) {
        interval = setInterval(() => {
            current.value = (current.value + 1) % slides.value.length;
        }, 6000);
    }
});

onUnmounted(() => clearInterval(interval));
</script>

<template>
    <Head title="Bienvenue" />

    <AuthenticatedLayout>
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <section v-if="slides.length > 0"
                     class="relative h-[400px] md:h-[480px] rounded-[3rem] overflow-hidden flex items-center shadow-2xl transition-all duration-1000 mb-16"
                     :class="`bg-gradient-to-br ${slides[current].bg}`"
            >
                <div class="absolute inset-0 bg-black/20 backdrop-blur-[1px]"></div>
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>

                <div class="relative z-10 px-8 md:px-20 w-full md:w-3/4 text-white">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-[0.2em] mb-4">
                        {{ slides[current].badge }}
                    </span>
                    <h1 class="text-xs md:text-sm font-black text-white/70 uppercase tracking-widest mb-2">
                        {{ slides[current].title }}
                    </h1>
                    <h2 class="text-4xl md:text-7xl font-black mb-6 leading-tight drop-shadow-lg">
                        {{ slides[current].name }}
                    </h2>
                    <p class="text-lg md:text-xl text-white/90 mb-10 max-w-xl leading-relaxed font-medium">
                        {{ slides[current].text }}
                    </p>
                    <Link :href="slides[current].link"
                          class="inline-block bg-white text-zinc-900 px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:scale-105 active:scale-95 transition duration-300"
                    >
                        Découvrir
                    </Link>
                </div>

                <div class="absolute bottom-10 right-10 flex gap-3">
                    <button v-for="(_, i) in slides" :key="i" @click="current = i"
                            class="h-2 transition-all duration-500 rounded-full"
                            :class="current === i ? 'w-12 bg-white' : 'w-3 bg-white/40 hover:bg-white/60'"
                    ></button>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="bg-gradient-to-br from-white to-indigo-50/40 dark:from-zinc-900 dark:to-zinc-900/50 p-6 sm:p-8 rounded-[2.5rem] shadow-sm border border-indigo-100/50 dark:border-zinc-800 flex flex-col">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl text-indigo-600">
                                <Sparkles class="w-5 h-5" />
                            </div>
                            <h2 class="font-black uppercase tracking-widest text-xs text-zinc-500 dark:text-zinc-400">Collections</h2>
                        </div>
                        <Link :href="route('collections.listeCollec')" class="text-[10px] font-black uppercase text-indigo-500 hover:underline">Tout voir</Link>
                    </div>

                    <div class="space-y-4 flex-1">
                        <Link v-for="collection in latestCollections" :key="collection.id"
                              :href="route('elements.listeElem', collection.slug)"
                              class="group flex items-center gap-4 p-3 bg-white/60 dark:bg-zinc-800/40 hover:bg-white dark:hover:bg-zinc-800 rounded-2xl transition-all border border-transparent hover:border-indigo-100 dark:hover:border-zinc-700"
                        >
                            <div class="h-14 w-14 rounded-2xl bg-zinc-200 dark:bg-zinc-700 overflow-hidden shrink-0 shadow-inner">
                                <img v-if="collection.image" :src="`/storage/${collection.image}`" class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div v-else class="h-full w-full flex items-center justify-center text-zinc-400 dark:text-zinc-500"><Package class="w-6 h-6" /></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-black text-zinc-800 dark:text-zinc-100 truncate group-hover:text-indigo-600 transition-colors">{{ collection.name }}</h3>
                                <p class="text-[10px] text-zinc-400 font-bold uppercase mt-0.5 truncate">{{ collection.club?.name || 'Collection Privée' }}</p>
                            </div>
                            <ArrowRight class="w-4 h-4 text-zinc-300 group-hover:text-indigo-600 transition-all group-hover:translate-x-1" />
                        </Link>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 dark:from-zinc-900 dark:to-zinc-900/50 p-6 sm:p-8 rounded-[2.5rem] shadow-sm border border-amber-100 dark:border-zinc-800 flex flex-col relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-200/20 rounded-full blur-2xl transition-all"></div>

                    <div class="relative z-10 flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-amber-100 dark:bg-amber-900/30 rounded-xl text-amber-600">
                                <Trophy class="w-5 h-5" />
                            </div>
                            <h2 class="font-black uppercase tracking-widest text-xs text-amber-700 dark:text-amber-500/80">Top Clubs</h2>
                        </div>
                    </div>

                    <div class="relative z-10 space-y-4 flex-1">
                        <Link v-for="(club, index) in topClubs" :key="club.id"
                              :href="route('clubs.show', club.slug)"
                              class="group/item flex items-center justify-between p-4 bg-white/80 dark:bg-zinc-800/40 hover:bg-white dark:hover:bg-zinc-800 rounded-[1.8rem] transition-all border border-amber-50 dark:border-transparent hover:border-amber-200 dark:hover:border-zinc-700 shadow-sm"
                        >
                            <div class="flex items-center gap-4 min-w-0">
                                <span class="text-lg font-black text-amber-200 dark:text-zinc-700">{{ index + 1 }}</span>
                                <div class="min-w-0 text-zinc-800 dark:text-zinc-100">
                                    <h4 class="text-sm font-bold truncate group-hover/item:text-amber-600 transition-colors">{{ club.name }}</h4>
                                    <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-tighter">{{ club.address?.city || 'Belgique' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/20 px-3 py-1 rounded-full border border-amber-100 dark:border-amber-900/30 shrink-0">
                                <Users class="w-3 h-3 text-amber-600" />
                                <span class="text-[10px] font-black text-amber-700 dark:text-amber-500">{{ club.users_count }}</span>
                            </div>
                        </Link>
                    </div>

                    <Link :href="route('clubs.index')"
                          class="relative z-10 mt-8 flex items-center justify-center gap-2 w-full py-4 bg-amber-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-amber-700 hover:scale-[1.02] active:scale-95 transition shadow-lg shadow-amber-900/10">
                        Trouver un club <Plus class="w-3 h-3" />
                    </Link>
                </div>

                <div class="bg-gradient-to-br from-white to-emerald-50/40 dark:from-zinc-900 dark:to-zinc-900/50 p-6 sm:p-8 rounded-[2.5rem] shadow-sm border border-emerald-100/50 dark:border-zinc-800 flex flex-col">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl text-emerald-600">
                                <Package class="w-5 h-5" />
                            </div>
                            <h2 class="font-black uppercase tracking-widest text-xs text-zinc-500 dark:text-zinc-400">Derniers ajouts</h2>
                        </div>
                    </div>

                    <div class="space-y-4 flex-1">
                        <Link v-for="elem in latestElems" :key="elem.id"
                              :href="route('elements.show', { collection: elem.collection.slug, element: elem.slug })"
                              class="group flex items-center gap-4 p-3 bg-white/60 dark:bg-zinc-800/40 hover:bg-white dark:hover:bg-zinc-800 rounded-2xl transition-all border border-transparent hover:border-emerald-100 dark:hover:border-zinc-700"
                        >
                            <div class="h-14 w-14 rounded-2xl bg-zinc-200 dark:bg-zinc-700 overflow-hidden shrink-0 shadow-inner">
                                <img v-if="elem.images?.length" :src="`/storage/${elem.images[0].path}`" class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div v-else class="h-full w-full flex items-center justify-center text-zinc-400 dark:text-zinc-500"><Package class="w-6 h-6" /></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-black text-zinc-800 dark:text-zinc-100 truncate group-hover:text-emerald-600 transition-colors">{{ elem.label }}</h3>
                                <div class="flex items-center gap-1 text-[9px] text-zinc-400 font-bold uppercase mt-1">
                                    <Clock class="w-3 h-3" /> {{ new Date(elem.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                            <div v-if="elem.price" class="text-xs font-black text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-lg shrink-0">
                                {{ elem.price }}€
                            </div>
                        </Link>
                    </div>
                </div>

            </div>
        </main>

        <footer class="mt-20 py-12 bg-white dark:bg-zinc-950 border-t border-zinc-100 dark:border-zinc-900">
            <div class="max-w-7xl mx-auto px-6 text-center text-zinc-400">
                <div class="flex justify-center gap-8 mb-6">
                    <Link href="#" class="text-xs font-bold hover:text-indigo-600 transition uppercase tracking-widest">CGU</Link>
                    <Link href="#" class="text-xs font-bold hover:text-indigo-600 transition uppercase tracking-widest">Confidentialité</Link>
                    <Link href="#" class="text-xs font-bold hover:text-indigo-600 transition uppercase tracking-widest">Contact</Link>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em]">Collect & Share &copy; 2026 — L'art de collectionner</p>
            </div>
        </footer>
    </AuthenticatedLayout>
</template>

<style scoped>
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>
