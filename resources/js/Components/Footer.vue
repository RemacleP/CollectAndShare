<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const currentYear = new Date().getFullYear();

// Intialize form
const form = useForm({
    email: '',
});

const subscribe = () => {
    form.post(route('newsletter.subscribe'), {
        preserveScroll: true, // Stop the page from jumping to the top
        onSuccess: () => {
            form.reset(); // Vide le champ uniquement si ça a marché
        },
    });
};
</script>

<template>
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">

                <!-- Name -->
                <div class="col-span-1">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Collect & share</h2>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Collectionnez et échangez vous des objets uniques pour votre collection personnelle.
                    </p>
                </div>

                <!-- Navigation -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Navigation</h3>
                    <ul class="space-y-3">
                        <li><Link :href="route('events.index')" class="text-gray-500 hover:text-indigo-600 transition-colors text-sm">Événements</Link></li>
                        <li><Link :href="route('home')" class="text-gray-500 hover:text-indigo-600 transition-colors text-sm">Accueil</Link></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Informations</h3>
                    <ul class="space-y-3">
                        <li><Link :href="route('legals.mentionsLegales')" class="text-gray-500 hover:text-indigo-600 transition-colors text-sm">Mentions légales</Link></li>
                        <li><Link :href="route('legals.contacts')" class="text-gray-500 hover:text-indigo-600 transition-colors text-sm">Contact</Link></li>
                        <li><Link :href="route('liensUtiles.index')" class="text-gray-500 hover:text-indigo-600 transition-colors text-sm">Liens utiles</Link></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Newsletter</h3>
                    <p class="text-gray-500 text-sm mb-4">Restez informé de nos dernières actualités.</p>

                    <form @submit.prevent="subscribe" class="flex flex-col space-y-2">
                        <div class="relative">
                            <label for="newsletter-email" class="sr-only">Email</label>
                            <input
                                id="newsletter-email"
                                v-model="form.email"
                                type="email"
                                placeholder="votre@email.com"
                                class="dark:text-gray-900 appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors"
                                :class="{ 'border-red-300 text-red-900 placeholder-red-300': form.errors.email, 'border-gray-300': !form.errors.email }"
                                :disabled="form.processing"
                            >
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600" id="email-error">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-wait transition-all"
                        >
                            <span v-if="form.processing">Inscription...</span>
                            <span v-else>S'inscrire</span>
                        </button>
                    </form>

                    <!-- Success message -->
                    <transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <div v-if="form.recentlySuccessful" class="mt-3 p-2 bg-green-50 rounded-md flex items-center">
                            <svg class="h-4 w-4 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <p class="text-sm font-medium text-green-800">
                                Inscription réussie !
                            </p>
                        </div>
                    </transition>
                </div>
            </div>

        </div>
    </footer>
</template>
