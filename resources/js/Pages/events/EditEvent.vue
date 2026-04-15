<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Calendar, MapPin, Euro, Info,
    ArrowLeft, Save, Clock, AlertTriangle
} from 'lucide-vue-next';
import PictureUploader from '@/Components/upload/PictureUploader.vue';

interface Club {
    id: number;
    name: string;
}

interface Event {
    id: number;
    slug: string;
    title: string;
    description: string;
    start_datetime: string;
    end_datetime: string | null;
    location_name: string | null;
    address: string | null;
    city: string | null;
    country: string | null;
    price: number | null;
    status: 'pending' | 'validated' | 'cancelled';
    registration_required: boolean | number;
    registration_deadline: string | null;
    image: string | null;
    club_id: number;
}

const props = defineProps<{
    event: Event;
    clubs: Club[];
}>();

const breadcrumbs = [
    { name: 'Événements', href: route('events.index') },
    { name: props.event.title, href: route('events.show', props.event.slug) },
    { name: 'Modifier', href: '#' },
];

// Helper pour formater les dates reçues du serveur vers l'input datetime-local
const formatDateTimeForInput = (dateStr: string | null) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const tzOffset = date.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(date.getTime() - tzOffset)).toISOString().slice(0, 16);
    return localISOTime;
};

const form = useForm({
    _method: 'PUT', // Important pour Laravel avec les FormData (images)
    title: props.event.title ?? '',
    description: props.event.description ?? '',
    start_datetime: formatDateTimeForInput(props.event.start_datetime),
    end_datetime: formatDateTimeForInput(props.event.end_datetime),
    location_name: props.event.location_name ?? '',
    address: props.event.address ?? '',
    city: props.event.city ?? '',
    country: props.event.country ?? '',
    price: props.event.price,
    status: props.event.status ?? 'pending',
    registration_required: Boolean(props.event.registration_required),
    registration_deadline: formatDateTimeForInput(props.event.registration_deadline),
    image: null as File | null,
    club_id: props.event.club_id ?? '',
});

function handleFile(file: File | null) {
    form.image = file;
}

function submit() {
    // Utilisation de POST avec _method: PUT pour supporter l'upload d'image en update
    form.post(route('events.update', props.event.slug), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Modifier - ${props.event.title}`" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Modifier l'événement
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Édition de <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ props.event.title }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('events.show', props.event.slug)"
                        class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700"
                    >
                        Voir l'événement
                    </Link>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                <div class="space-y-6 lg:col-span-2">

                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-6 flex items-center gap-2 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <Info class="h-5 w-5 text-indigo-500" />
                            <h2 class="text-lg font-semibold dark:text-white">Contenu de l'événement</h2>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium dark:text-gray-300">Titre</label>
                                <input v-model="form.title" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required />
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <label class="mb-2 block text-sm font-medium dark:text-gray-300">Organisateur</label>
                                    <select v-model="form.club_id" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required>
                                        <option v-for="club in props.clubs" :key="club.id" :value="club.id">{{ club.name }}</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="mb-2 block text-sm font-medium dark:text-gray-300">Statut de publication</label>
                                    <select v-model="form.status" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required>
                                        <option value="pending">En attente</option>
                                        <option value="validated">Validé / Publié</option>
                                        <option value="cancelled">Annulé</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium dark:text-gray-300">Description</label>
                                <textarea v-model="form.description" rows="6" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-6 flex items-center gap-2 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <MapPin class="h-5 w-5 text-indigo-500" />
                            <h2 class="text-lg font-semibold dark:text-white">Lieu</h2>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-medium dark:text-gray-300">Nom du lieu</label>
                                <input v-model="form.location_name" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-medium dark:text-gray-300">Adresse</label>
                                <input v-model="form.address" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                            </div>
                            <input v-model="form.city" type="text" placeholder="Ville" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                            <input v-model="form.country" type="text" placeholder="Pays" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-400">Image de couverture</h2>
                        <PictureUploader
                            @file-selected="handleFile"
                            :existing-image="props.event.image ? `/storage/${props.event.image}` : null"
                        />
                    </section>

                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2">
                            <Clock class="h-4 w-4 text-indigo-500" />
                            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400">Planification</h2>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs text-gray-500">Début</label>
                                <input v-model="form.start_datetime" type="datetime-local" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" required />
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Fin</label>
                                <input v-model="form.end_datetime" type="datetime-local" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2">
                            <Euro class="h-4 w-4 text-indigo-500" />
                            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400">Tarif & Inscriptions</h2>
                        </div>

                        <div class="mb-6 flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-gray-700/30">
                            <span class="text-sm dark:text-gray-300">Inscription requise</span>
                            <button
                                type="button"
                                @click="form.registration_required = !form.registration_required"
                                class="relative inline-flex h-6 w-11 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                :class="form.registration_required ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600'"
                            >
                                <span class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-200" :class="form.registration_required ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>

                        <div v-if="form.registration_required" class="mb-4">
                            <label class="mb-1 block text-xs text-gray-500">Date limite d'inscription</label>
                            <input v-model="form.registration_deadline" type="datetime-local" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Prix (€)</label>
                            <input v-model.number="form.price" type="number" step="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm dark:border-gray-600 dark:bg-gray-700/50 dark:text-white" />
                        </div>
                    </section>

                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-4 text-base font-bold text-white shadow-lg transition-all hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <Save class="h-5 w-5" />
                            {{ form.processing ? 'Enregistrement...' : 'Enregistrer les modifications' }}
                        </button>

                        <Link :href="route('events.index')" class="mt-4 block text-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            Annuler les changements
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
