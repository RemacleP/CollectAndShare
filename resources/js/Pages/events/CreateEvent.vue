<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Calendar, MapPin, Euro, Info,
    ArrowLeft, Save, Clock, ChevronRight
} from 'lucide-vue-next';
import PictureUploader from '@/Components/upload/PictureUploader.vue';

interface Club {
    id: number;
    name: string;
}

const props = defineProps<{
    clubs: Club[];
}>();

const breadcrumbs = [
    { name: 'Événements', href: route('events.index') },
    { name: 'Créer', href: '#' },
];

const form = useForm({
    title: '',
    description: '',
    start_datetime: '',
    end_datetime: '',
    location_name: '',
    address: '',
    city: '',
    country: 'Belgique', // Valeur par défaut possible
    price: null as number | null,
    status: 'validated',
    registration_required: false,
    registration_deadline: '',
    image: null as File | null,
    club_id: '' as string | number,
});

function submit() {
    form.post(route('events.store'), {
        forceFormData: true,
        onSuccess: () => {
            // La redirection est gérée par le contrôleur
        },
    });
}

function handleFile(file: File | null) {
    form.image = file;
}
</script>

<template>
    <Head title="Créer un événement" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Nouvel événement
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Organisez et publiez une nouvelle activité pour la communauté.
                    </p>
                </div>
                <Link
                    :href="route('events.index')"
                    class="flex items-center gap-2 text-sm font-medium text-gray-600 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Retour à la liste
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    <div class="space-y-6 lg:col-span-2">
                        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-6 flex items-center gap-2 border-b border-gray-100 pb-4 dark:border-gray-700">
                                <Info class="h-5 w-5 text-indigo-500" />
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informations générales</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Titre de l'événement</label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white"
                                        placeholder="Ex: Tournoi de Printemps 2024"
                                        required
                                    />
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Organisateur</label>
                                    <select
                                        v-model="form.club_id"
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white"
                                        required
                                    >
                                        <option value="" disabled>Choisir un club...</option>
                                        <option v-for="club in props.clubs" :key="club.id" :value="club.id">
                                            {{ club.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <textarea
                                        v-model="form.description"
                                        rows="5"
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white"
                                        placeholder="Décrivez le programme, les prérequis..."
                                        required
                                    ></textarea>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-6 flex items-center gap-2 border-b border-gray-100 pb-4 dark:border-gray-700">
                                <MapPin class="h-5 w-5 text-indigo-500" />
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Localisation</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du lieu</label>
                                    <input v-model="form.location_name" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" placeholder="Ex: Stade Communal" required />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                                    <input v-model="form.address" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Ville</label>
                                    <input v-model="form.city" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pays</label>
                                    <input v-model="form.country" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required />
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="space-y-6">
                        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Illustration</h2>
                            <PictureUploader @file-selected="handleFile" />
                        </section>

                        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-4 flex items-center gap-2">
                                <Clock class="h-4 w-4 text-indigo-500" />
                                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Dates & Heures</h2>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-500">Début</label>
                                    <input v-model="form.start_datetime" type="datetime-local" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-500">Fin</label>
                                    <input v-model="form.end_datetime" type="datetime-local" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required />
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-4 flex items-center gap-2">
                                <Euro class="h-4 w-4 text-indigo-500" />
                                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tarification</h2>
                            </div>

                            <div class="mb-6 flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-gray-700/30">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Inscription requise</span>
                                <button
                                    type="button"
                                    @click="form.registration_required = !form.registration_required"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                    :class="form.registration_required ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600'"
                                >
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="form.registration_required ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>

                            <div v-if="form.registration_required" class="mb-4 animate-in fade-in slide-in-from-top-2">
                                <label class="mb-1 block text-xs font-medium text-gray-500">Date limite</label>
                                <input v-model="form.registration_deadline" type="datetime-local" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Prix par personne (€)</label>
                                <input v-model="form.price" type="number" step="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" placeholder="0.00 (Laissez vide si gratuit)" />
                            </div>
                        </section>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-4 text-base font-bold text-white shadow-lg transition-all hover:bg-indigo-700 hover:shadow-indigo-500/25 focus:ring-4 focus:ring-indigo-500/50 disabled:opacity-50"
                        >
                            <Save v-if="!form.processing" class="h-5 w-5" />
                            <span v-if="form.processing">Enregistrement...</span>
                            <span v-else>Publier l'événement</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
