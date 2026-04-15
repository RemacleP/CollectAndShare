<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import {
    LayoutDashboard, ShieldCheck, FileText, ExternalLink,
    Users, Settings, ChevronDown, Plus, List, Circle, ArrowRight,
    Calendar
} from 'lucide-vue-next';

const page = usePage();
const isAdmin = computed(() => !!page.props.auth.user?.is_admin);

// Gestion de l'ouverture des menus (Dropdowns)
// S'ouvre automatiquement si on est sur une route parente
const openMenus = ref({
    clubs: route().current('clubs.*'),
    liens: route().current('liensUtiles.*'),
    legals: route().current('legals.*'),
    collections: route().current('collections.*')
});

const toggleMenu = (menu) => {
    openMenus.value[menu] = !openMenus.value[menu];
};

// Configuration des menus CRUD
const adminNavigation = [
    {
        name: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutDashboard,
        active: 'dashboard'
    },

    {
        name: 'Collections',
        icon: List,//Ou Library ou Layers
        active: 'collections.*',
        dropdown: 'collections',
        children: [
            { name: 'Liste des collections', href: route('collections.listeCollec'), icon: List, active: 'collections.listeCollec' },
            { name: 'Nouvelle collection', href: route('collections.createCollec'), icon: Plus, active: 'collections.createCollec' },
        ]
    },

    {
        name: 'Gestion Clubs',
        icon: ShieldCheck,
        active: 'clubs.*',
        dropdown: 'clubs',
        children: [
            { name: 'Liste des clubs', href: route('clubs.index'), icon: List, active: 'clubs.index' },
            { name: 'Ajouter un club', href: route('clubs.create'), icon: Plus, active: 'clubs.create' },
        ]
    },
    {
        name: 'Événements',
        icon: Calendar,
        active: 'events.*',
        dropdown: 'events',
        children: [
            { name: 'Liste des événements', href: route('events.index'), icon: List },
            { name: 'Créer un événement', href: route('events.create'), icon: Plus },
        ]
    },
    {
        name: 'Liens Utiles',
        icon: ExternalLink,
        active: 'liensUtiles.*',
        dropdown: 'liens',
        children: [
            { name: 'Gérer les liens', href: route('liensUtiles.index'), icon: List, active: 'liensUtiles.index' },
            { name: 'Nouveau lien', href: route('liensUtiles.create'), icon: Plus, active: 'liensUtiles.create' },
        ]
    },
    {
        name: 'Légal',
        icon: FileText,
        active: 'legals.*',
        dropdown: 'legals',
        children: [
            {
                name: 'Voir Mentions',
                href: route('legals.mentionsLegales'),
                icon: List,
                active: 'legals.mentionsLegales'
            },
            {
                name: 'Modifier',
                // On pointe vers la même page, mais on pourrait ajouter un paramètre
                // ou simplement laisser l'utilisateur cliquer sur "Modifier" dans la page
                href: route('legals.mentionsLegales', { edit: 'true' }),
                icon: Plus,
                active: null
            }
        ]
    }
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 flex flex-col font-sans">
        <Navbar :user="$page.props.auth.user" />

        <div class="flex flex-1">
            <aside v-if="isAdmin" class="w-72 bg-white dark:bg-zinc-900 border-r border-gray-200 dark:border-zinc-800 hidden md:flex flex-col sticky top-0 h-[calc(100vh-64px)] overflow-y-auto shadow-sm">

                <div class="p-6">
                    <div class="flex items-center gap-2 px-3 py-2 bg-zinc-100 dark:bg-zinc-800 rounded-xl">
                        <Settings class="h-4 w-4 text-zinc-500" />
                        <span class="text-[10px] font-black text-zinc-500 uppercase tracking-widest">Administration</span>
                    </div>
                </div>

                <nav class="flex-1 px-4 space-y-2 pb-10">
                    <div v-for="item in adminNavigation" :key="item.name">

                        <Link v-if="!item.children" :href="item.href"
                              :class="[
                                route().current(item.active)
                                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-lg'
                                : 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800',
                                'flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200'
                            ]"
                        >
                            <component :is="item.icon" class="mr-3 h-5 w-5 shrink-0" />
                            {{ item.name }}
                        </Link>

                        <div v-else class="space-y-1">
                            <button @click="toggleMenu(item.dropdown)"
                                    :class="[
                                    route().current(item.active)
                                    ? 'text-indigo-600 bg-indigo-50/50 dark:bg-indigo-500/10'
                                    : 'text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800',
                                    'w-full flex items-center justify-between px-4 py-3 text-sm font-bold rounded-2xl transition-all'
                                ]"
                            >
                                <div class="flex items-center">
                                    <component :is="item.icon" class="mr-3 h-5 w-5 shrink-0" />
                                    {{ item.name }}
                                </div>
                                <ChevronDown :class="['h-4 w-4 transition-transform duration-300', openMenus[item.dropdown] ? 'rotate-180' : '']" />
                            </button>

                            <div v-show="openMenus[item.dropdown]" class="mt-1 ml-4 pl-4 border-l-2 border-zinc-100 dark:border-zinc-800 space-y-1">
                                <Link v-for="child in item.children" :key="child.name" :href="child.href"
                                      :class="[
                                          route().current(child.active)
                                          ? 'text-indigo-600 dark:text-indigo-400 font-bold'
                                          : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200',
                                          'flex items-center px-4 py-2 text-xs transition-colors group'
                                      ]"
                                >
                                    <component :is="child.icon" class="mr-2 h-3.5 w-3.5 opacity-70 group-hover:scale-110 transition-transform" />
                                    {{ child.name }}
                                    <ArrowRight v-if="route().current(child.active)" class="ml-auto h-3 w-3" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </nav>
            </aside>

            <div class="flex-1 min-w-0 flex flex-col bg-gray-50 dark:bg-zinc-950">
                <header v-if="$slots.header" class="bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 shadow-sm">
                    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main class="p-8 flex-1 overflow-y-auto">
                    <div class="max-w-7xl mx-auto">
                        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-500 text-white rounded-2xl shadow-lg font-bold text-sm animate-bounce-short">
                            ✅ {{ $page.props.flash.success }}
                        </div>

                        <slot />
                    </div>
                </main>
            </div>
        </div>

        <Footer />
    </div>
</template>

<style scoped>
.animate-bounce-short {
    animation: bounce 0.5s ease-in-out 1;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
</style>
