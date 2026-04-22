<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronDown,
    Settings,
    LogOut,
    User as UserIcon,
    Users,
    Palette,
    ShoppingBag,
    Mail
} from 'lucide-vue-next';

defineProps({
    user: Object
});

const page = usePage();

// Calcul du total des notifications (Messages non lus + Demandes en attente pour les admins)
const totalNotifications = computed(() => {
    const unreadMessages = page.props.unreadMessagesCount || 0;
    const pendingRequests = page.props.pendingRequestsCount || 0;
    return unreadMessages + pendingRequests;
});
</script>

<template>
    <nav class="border-b border-gray-100 bg-white dark:bg-zinc-900 dark:border-zinc-800 sticky top-0 z-50 shadow-sm transition-colors duration-300">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">

                <div class="flex">
                    <div class="flex shrink-0 items-center">
                        <Link href="/" class="flex items-center gap-3 group">
                            <div class="h-10 w-10 bg-zinc-50 dark:bg-zinc-800 rounded-xl flex items-center justify-center p-1.5 border border-zinc-100 dark:border-zinc-700 group-hover:scale-105 transition-transform overflow-hidden">
                                <ApplicationLogo class="h-full w-auto" />
                            </div>

                            <div class="text-xl font-black tracking-tighter text-zinc-900 dark:text-white uppercase">
                                Collect<span class="text-indigo-600">&</span>Share
                            </div>
                        </Link>
                    </div>

                    <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-center">
                        <NavLink :href="route('home')" :active="route().current('home')">Accueil</NavLink>

                        <template v-if="user">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Tableau de bord</NavLink>
                        </template>

                        <NavLink :href="route('clubs.index')" :active="route().current('clubs.*')">Clubs</NavLink>
                        <NavLink :href="route('collections.listeCollec')" :active="route().current('collections.*')">Collections</NavLink>
                        <NavLink :href="route('events.index')" :active="route().current('events.*')">Événements</NavLink>
                    </div>
                </div>

                <div class="hidden sm:ms-6 sm:flex sm:items-center gap-1">

                    <Link
                        v-if="user"
                        :href="route('messages.index')"
                        class="relative p-2.5 text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800"
                        title="Messages et Demandes"
                    >
                        <Mail class="w-5 h-5" />
                        <span
                            v-if="totalNotifications > 0"
                            class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-indigo-600 text-[9px] font-bold text-white ring-2 ring-white dark:ring-zinc-900 shadow-sm"
                            :class="{ 'bg-red-500 animate-pulse': page.props.pendingRequestsCount > 0 }"
                        >
                            {{ totalNotifications }}
                        </span>
                    </Link>

                    <Link
                        :href="route('cart.index')"
                        class="relative p-2.5 text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800"
                    >
                        <ShoppingBag class="w-5 h-5" />
                        <span
                            v-if="page.props.cartCount > 0"
                            class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-indigo-600 text-[9px] font-bold text-white ring-2 ring-white dark:ring-zinc-900 shadow-sm"
                        >
                            {{ page.props.cartCount }}
                        </span>
                    </Link>

                    <div v-if="user" class="relative ms-2">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button class="flex items-center gap-2 p-1 px-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-transparent hover:border-zinc-200 dark:hover:border-zinc-700 text-sm font-bold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-750 transition-all group">
                                    <div class="h-7 w-7 rounded-full bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-[10px] font-black text-white shadow-sm uppercase group-hover:scale-105 transition-transform">
                                        {{ user.firstname[0] }}{{ user.lastname[0] }}
                                    </div>
                                    <span class="max-w-[120px] truncate">{{ user.username }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-2 text-[10px] text-zinc-400 dark:text-zinc-500 uppercase font-black tracking-widest">
                                    Mon Compte
                                </div>

                                <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                    <UserIcon class="w-4 h-4 opacity-70" /> Paramètres Profil
                                </DropdownLink>
                                <DropdownLink :href="route('settings.ui.index')" class="flex items-center gap-2">
                                    <Palette class="w-4 h-4 opacity-70" /> Apparence & UI
                                </DropdownLink>
                                <template v-if="user.is_admin">
                                    <div class="border-t border-zinc-100 dark:border-zinc-800 my-1"></div>
                                    <div class="px-4 py-2 text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-black tracking-widest">
                                        Administration
                                    </div>

                                    <DropdownLink :href="route('admin.users.index')" class="flex items-center gap-2">
                                        <Users class="w-4 h-4 opacity-70" /> Gestion Utilisateurs
                                    </DropdownLink>



                                    <DropdownLink :href="route('admin.settings.index')" class="flex items-center gap-2 font-bold text-zinc-700 dark:text-zinc-200">
                                        <Settings class="w-4 h-4 opacity-70" /> Config. Plateforme
                                    </DropdownLink>
                                </template>

                                <div class="border-t border-zinc-100 dark:border-zinc-800 my-1"></div>

                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 dark:text-red-400 flex items-center gap-2 w-full text-left">
                                    <LogOut class="w-4 h-4" /> Déconnexion
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <div v-else class="flex items-center gap-3 ms-4">
                        <Link :href="route('login')" class="text-sm font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-white transition">
                            Connexion
                        </Link>

                        <Link
                            :href="route('register')"
                            class="inline-flex items-center px-5 py-2.5 bg-zinc-900 dark:bg-indigo-600 border border-transparent rounded-xl font-black text-[10px] uppercase tracking-[0.2em] text-white hover:bg-indigo-600 dark:hover:bg-indigo-500 transition shadow-lg active:scale-95"
                        >
                            S'inscrire
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
