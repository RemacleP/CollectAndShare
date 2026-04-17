<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ user: Object });
const showingNavigationDropdown = ref(false);
</script>

<template>
    <nav class="border-b border-gray-100 bg-white dark:bg-zinc-900 dark:border-zinc-800 sticky top-0 z-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex">
                    <div class="flex shrink-0 items-center">
                        <Link href="/">
                            <div class="text-xl font-black tracking-tighter text-indigo-600 dark:text-indigo-400">
                                COLLECT<span class="text-zinc-400">&</span>SHARE
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

                <div class="hidden sm:ms-6 sm:flex sm:items-center gap-4">
                    <div v-if="user" class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 p-1 px-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 text-sm font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 transition">
                                    <div class="h-6 w-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] text-white">
                                        {{ user.firstname[0] }}{{ user.lastname[0] }}
                                    </div>
                                    {{ user.username }}
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-2 text-xs text-zinc-400 uppercase font-black">Mon Compte</div>
                                <DropdownLink :href="route('profile.edit')">Paramètres Profil</DropdownLink>
                                <DropdownLink :href="route('categories.index')">Mes Catégories</DropdownLink>
                                <div class="border-t border-zinc-100 dark:border-zinc-700 my-1"></div>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600">
                                    Déconnexion
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <div v-else class="flex items-center gap-4">
                        <Link :href="route('login')" class="text-sm font-bold text-zinc-500 hover:text-indigo-600 transition">
                            Connexion
                        </Link>

                        <Link
                            :href="route('register')"
                            class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm shadow-indigo-200 dark:shadow-none"
                        >
                            S'inscrire
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
