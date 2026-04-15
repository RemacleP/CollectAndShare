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
    <nav class="border-b border-gray-100 bg-white dark:bg-zinc-900 dark:border-zinc-800 sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex">
                    <div class="flex shrink-0 items-center">
                        <Link href="/">
                            <div class="text-xl font-bold text-indigo-600 dark:text-indigo-400">Collect & Share</div>
                        </Link>
                    </div>

                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <NavLink href="/" :active="route().current('welcome')">
                            Accueil
                        </NavLink>

                        <template v-if="user">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </NavLink>
                        </template>
                            <NavLink :href="route('clubs.index')" :active="route().current('clubs.*')">
                                Les Clubs
                            </NavLink>
                        <NavLink :href="route('collections.listeCollec')" :active="route().current('collections.*')">
                            Les Collections
                        </NavLink>
                            <NavLink :href="route('events.index')" :active="route().current('events.*')">
                                Les Evenements
                            </NavLink>

                    </div>
                </div>

                <div class="hidden sm:ms-6 sm:flex sm:items-center">
                    <div v-if="user" class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none transition">
                                    {{ user.firstname }} {{ user.lastname }}
                                    <svg class="ms-2 -me-0.5 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profil</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Déconnexion
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <div v-else class="space-x-4">
                        <Link :href="route('login')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                            Connexion
                        </Link>
                        <Link :href="route('register')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition">
                            S'inscrire
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
