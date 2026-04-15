<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Plus, Calendar, MapPin, Clock,
    Pencil, Trash2, Image as ImageIcon, ChevronRight
} from 'lucide-vue-next';

const props = defineProps<{ events: any }>();
const page = usePage();

// --- Logique d'autorisation ---
const isAdmin = computed(() => !!page.props.auth.user?.is_admin);
const canCreate = isAdmin;

const canUpdateEvent = (ev: any) => {
    if (isAdmin.value) return true;
    return page.props.auth.user?.club_ids?.includes(ev.club_id);
};

// --- Fonctions de formatage (Correction de l'erreur) ---
const formatDateShort = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('fr-FR', { month: 'short' }).replace('.', '');
};

const formatDay = (dateStr: string) => {
    return new Date(dateStr).getDate();
};

const formatTime = (dateStr: string | null) => {
    if (!dateStr) return '--:--';
    return new Date(dateStr).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// --- Actions ---
const deleteEvent = (slug: string) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
        router.delete(route('events.destroy', slug), {
            preserveScroll: true,
        });
    }
};

const breadcrumbs = [
    { name: 'Accueil', href: route('dashboard') },
    { name: 'Événements', href: '#' },
];
</script>

<template>
    <Head title="Événements" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">

            <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                        Nos Événements
                    </h1>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">
                        Découvrez et gérez les activités de la communauté.
                    </p>
                </div>
                <Link
                    v-if="canCreate"
                    :href="route('events.create')"
                    class="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-none dark:shadow-none"
                >
                    <Plus class="h-5 w-5" />
                    Créer un événement
                </Link>
            </div>

            <div v-if="events.data.length > 0" class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="ev in events.data"
                    :key="ev.id"
                    class="group flex flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="relative h-52 w-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="ev.image"
                            :src="`/storage/${ev.image}`"
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                            :alt="ev.title"
                        />
                        <div v-else class="flex h-full w-full flex-col items-center justify-center text-gray-400">
                            <ImageIcon class="h-12 w-12 opacity-20" />
                        </div>

                        <div class="absolute right-4 top-4">
                            <span class="rounded-lg bg-white/90 px-3 py-1 text-sm font-bold text-gray-900 shadow-sm backdrop-blur dark:bg-gray-900/90 dark:text-white">
                                {{ ev.price ? `${ev.price}€` : 'Gratuit' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-6">
                        <div class="mb-4 flex items-start justify-between">
                            <div class="flex flex-col items-center justify-center rounded-2xl border border-indigo-50 bg-indigo-50 px-3 py-2 text-indigo-700 dark:border-indigo-900/50 dark:bg-indigo-900/30 dark:text-indigo-300">
                                <span class="text-xs font-black uppercase tracking-tighter">
                                    {{ formatDateShort(ev.start_datetime) }}
                                </span>
                                <span class="text-2xl font-black">
                                    {{ formatDay(ev.start_datetime) }}
                                </span>
                            </div>

                            <div v-if="canUpdateEvent(ev)" class="flex space-x-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <Link
                                    :href="route('events.edit', ev.slug)"
                                    class="rounded-xl bg-gray-100 p-2 text-gray-600 transition hover:bg-indigo-600 hover:text-white dark:bg-gray-700 dark:text-gray-300"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button
                                    @click="deleteEvent(ev.slug)"
                                    class="rounded-xl bg-gray-100 p-2 text-gray-600 transition hover:bg-red-600 hover:text-white dark:bg-gray-700 dark:text-gray-300"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <h3 class="mb-2 line-clamp-1 text-xl font-bold text-gray-900 dark:text-white" :title="ev.title">
                            {{ ev.title }}
                        </h3>

                        <div class="mb-6 space-y-2">
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <Clock class="mr-2 h-4 w-4 text-indigo-500" />
                                {{ formatTime(ev.start_datetime) }} <span v-if="ev.end_datetime" class="mx-1">-</span> {{ formatTime(ev.end_datetime) }}
                            </div>
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <MapPin class="mr-2 h-4 w-4 text-indigo-500" />
                                <span class="truncate">{{ ev.location_name }} ({{ ev.city }})</span>
                            </div>
                        </div>

                        <Link
                            :href="route('events.show', ev.slug)"
                            class="mt-auto flex items-center justify-center gap-2 rounded-xl bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-600 transition-all hover:bg-indigo-600 hover:text-white dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-600 dark:hover:text-white"
                        >
                            Voir les détails
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 py-20 dark:border-gray-700 dark:bg-gray-800/50">
                <Calendar class="h-16 w-16 text-gray-300" />
                <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Aucun événement</h3>
                <p class="mt-1 text-gray-500 dark:text-gray-400">La liste est vide pour le moment.</p>
                <Link
                    v-if="canCreate"
                    :href="route('events.create')"
                    class="mt-6 rounded-xl bg-indigo-600 px-6 py-2 text-sm font-bold text-white shadow-md hover:bg-indigo-700"
                >
                    Créer le premier événement
                </Link>
            </div>

            <div v-if="events.links && events.data.length > 0" class="mt-12 flex justify-center">
                <nav class="flex items-center gap-1">
                    <component
                        :is="link.url ? Link : 'span'"
                        v-for="link in events.links"
                        :key="link.label"
                        :href="link.url"
                        v-html="link.label"
                        class="flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm font-bold transition-all"
                        :class="[
                            link.active
                                ? 'bg-indigo-600 text-white shadow-md'
                                : 'bg-white text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-gray-800 dark:text-gray-400',
                            !link.url ? 'cursor-not-allowed opacity-30' : 'cursor-pointer',
                        ]"
                    />
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
