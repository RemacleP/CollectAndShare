<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    latestCollections: Array,
    topClubs: Array,
    latestElems: Array,
});

const slides = [
    {
        title: 'La plateforme des collectionneurs',
        text: 'Découvrez, partagez et échangez des collections uniques.',
        bg: 'from-indigo-600 to-purple-600',
    },
    {
        title: 'Sécurité e-ID intégrée',
        text: 'Identité vérifiée pour des échanges en toute confiance.',
        bg: 'from-blue-600 to-cyan-600',
    }
];

const current = ref(0);
let interval;

onMounted(() => {
    interval = setInterval(() => {
        current.value = (current.value + 1) % slides.length;
    }, 5000);
});

onUnmounted(() => clearInterval(interval));
</script>

<template>
    <Head title="Bienvenue" />

    <AuthenticatedLayout>
        <main class="max-w-7xl mx-auto px-6 py-8">
            <section
                class="relative h-[450px] rounded-3xl overflow-hidden flex items-center justify-center text-white transition-colors duration-700 shadow-2xl"
                :class="`bg-gradient-to-r ${slides[current].bg}`"
            >
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative z-10 text-center px-6 max-w-3xl">
                    <h1 class="text-4xl md:text-6xl font-extrabold mb-6 drop-shadow-md">
                        {{ slides[current].title }}
                    </h1>
                    <p class="text-xl opacity-90 mb-10 leading-relaxed">
                        {{ slides[current].text }}
                    </p>

                    <div class="flex justify-center gap-4">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('register')"
                            class="bg-white text-indigo-600 px-8 py-4 rounded-full font-bold shadow-lg hover:bg-gray-100 transition duration-300 ease-in-out"
                        >
                            Créer un compte
                        </Link>
                        <Link
                            :href="route('dashboard')"
                            class="bg-indigo-900/30 backdrop-blur-md border border-white/30 text-white px-8 py-4 rounded-full font-bold hover:bg-white/20 transition duration-300 ease-in-out"
                        >
                            Parcourir
                        </Link>
                    </div>
                </div>
            </section>

            <div class="grid gap-8 md:grid-cols-3 mt-16 pb-16">
                <div class="bg-white dark:bg-zinc-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h2 class="font-bold uppercase tracking-wider text-sm text-gray-500">Dernières Collections</h2>
                    </div>
                    <div v-if="latestCollections?.length" class="space-y-4"></div>
                    <p v-else class="text-gray-400 italic text-sm">Aucune collection récente.</p>
                </div>

                <div class="bg-white dark:bg-zinc-900 p-8 rounded-3xl shadow-md border-t-4 border-t-indigo-600 dark:border-x-zinc-800 dark:border-b-zinc-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h2 class="font-bold uppercase tracking-wider text-sm text-gray-500">Clubs Populaires</h2>
                    </div>
                    <div class="space-y-3">
                        <Link v-for="club in topClubs" :key="club.id"
                              :href="route('clubs.show', club.slug)"
                              class="group flex justify-between items-center p-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 rounded-xl transition duration-200 ease-in-out cursor-pointer">
                            <span class="font-semibold text-gray-700 dark:text-gray-200 group-hover:text-indigo-600">{{ club.name }}</span>
                            <span class="text-xs font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 px-3 py-1 rounded-full">
                                {{ club.users_count }} membres
                            </span>
                        </Link>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <h2 class="font-bold uppercase tracking-wider text-sm text-gray-500">Nouveaux Objets</h2>
                    </div>
                    <p class="text-gray-400 italic text-sm text-center py-4 border-2 border-dashed border-gray-100 dark:border-zinc-800 rounded-xl">
                        En attente d'ajouts.
                    </p>
                </div>
            </div>
        </main>

        <footer class="py-12 border-t border-gray-100 dark:border-zinc-900 text-center">
            <p class="text-xs text-gray-400">Collect & Share &copy; 2026</p>
            <p class="text-[10px] text-gray-300 mt-2 tracking-widest uppercase">Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>
        </footer>
    </AuthenticatedLayout>
</template>
