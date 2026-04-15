<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    club: Object,
    can: Object // Reçoit { edit: boolean }
});

const goBack = () => {
    // Si l'historique existe, on recule, sinon on redirige vers l'index par sécurité
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('events.index'));
    }
};
// Formateurs pour les cartes d'événements
const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: 'numeric', month: 'short'
    });
};

const formatTime = (dateStr) => {
    return new Date(dateStr).toLocaleTimeString('fr-FR', {
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="club.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ club.name }}
                </h2>
                <button
                    @click="goBack"
                    class="group flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                >
                    <ChevronLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Retour
                </button>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">

                    <div class="relative h-48 sm:h-64 md:h-80 bg-indigo-900">
                        <img v-if="club.logo"
                             :src="club.logo"
                             class="w-full h-full object-cover opacity-60"
                             alt="Banner Background" />

                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        <div class="absolute bottom-0 left-0 p-4 sm:p-8 text-white w-full flex flex-row items-end gap-4 sm:gap-6">

                            <div v-if="club.logo" class="shrink-0 mb-1">
                                <div class="h-20 w-20 sm:h-28 sm:w-28 rounded-2xl border-4 border-white/20 shadow-2xl overflow-hidden bg-white/10 backdrop-blur-sm">
                                    <img :src="club.logo" class="w-full h-full object-contain p-1" :alt="club.name" />
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h1 class="text-2xl sm:text-4xl font-bold mb-2 leading-tight drop-shadow-md">
                                    {{ club.name }}
                                </h1>
                                <p v-if="club.address" class="flex items-center text-sm sm:text-base opacity-90 drop-shadow-md">
                                    <span class="mr-2">📍</span> {{ club.address.city }}, {{ club.address.country }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-4 sm:p-8">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white">
                                <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                    <span class="w-1 h-6 bg-indigo-600 rounded mr-3"></span>
                                    À propos du club
                                </h3>
                                <p class="text-gray-600 leading-relaxed whitespace-pre-wrap text-sm sm:text-base">
                                    {{ club.description || 'Aucune description disponible.' }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-2xl p-5 sm:p-6 border border-gray-100">
                                <h3 class="font-bold text-gray-900 border-b border-gray-200 pb-3 mb-4">Informations</h3>

                                <div class="space-y-4">
                                    <div v-if="club.email" class="break-all">
                                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Email</p>
                                        <a :href="`mailto:${club.email}`" class="text-indigo-600 hover:underline text-sm font-medium">{{ club.email }}</a>
                                    </div>

                                    <div v-if="club.address">
                                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Localisation</p>
                                        <p class="text-gray-700 text-sm leading-snug">
                                            {{ club.address.street }} {{ club.address.number }}<br>
                                            {{ club.address.postal_code }} {{ club.address.city }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Communauté</p>
                                        <p class="text-gray-900 font-semibold">{{ club.members_count }} membres inscrits</p>
                                    </div>
                                </div>

                                <div v-if="can.edit" class="mt-6 pt-6 border-t border-gray-200">
                                    <Link :href="route('clubs.edit', club.id)"
                                          class="flex justify-center items-center w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition shadow-sm font-bold text-sm">
                                        ⚙️ Modifier les réglages
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 p-4 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                                <Calendar class="h-5 w-5 text-indigo-600" />
                                Événements à venir
                            </h3>
                            <Link :href="route('events.index')" class="text-xs font-bold text-indigo-600 hover:underline">
                                Tout voir
                            </Link>
                        </div>

                        <div v-if="club.events?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Link
                                v-for="event in club.events"
                                :key="event.id"
                                :href="route('events.show', event.slug)"
                                class="group flex items-center p-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all"
                            >
                                <div class="flex flex-col items-center justify-center bg-indigo-50 text-indigo-700 rounded-xl px-3 py-2 mr-4 min-w-[60px]">
                                    <span class="text-[10px] font-black uppercase tracking-tighter">{{ formatDate(event.start_datetime).split(' ')[1] }}</span>
                                    <span class="text-xl font-black">{{ formatDate(event.start_datetime).split(' ')[0] }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ event.title }}</h4>
                                    <div class="flex items-center gap-3 mt-1 text-[11px] text-gray-500">
                                        <span class="flex items-center gap-1"><Clock class="h-3 w-3" /> {{ formatTime(event.start_datetime) }}</span>
                                        <span class="flex items-center gap-1"><MapPin class="h-3 w-3" /> {{ event.city }}</span>
                                    </div>
                                </div>
                                <ArrowRight class="h-4 w-4 text-gray-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" />
                            </Link>
                        </div>
                        <div v-else class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <p class="text-sm text-gray-500">Aucun événement prévu pour le moment.</p>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 bg-gray-50/50 p-4 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900">👥 Membres du club</h3>
                            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold">
                                {{ club.members_count }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="member in club.members" :key="member.id"
                                 class="flex items-center p-3 sm:p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:border-indigo-200 transition"
                                 :class="{'ring-1 ring-red-100 bg-red-50/10': member.is_super_admin}">

                                <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm sm:text-base mr-3 sm:mr-4 shrink-0 uppercase">
                                    {{ member.firstname?.[0] }}{{ member.lastname?.[0] }}
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ member.full_name }}</p>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">

        <span v-if="member.is_super_admin"
              class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-red-600 text-white shadow-sm">
            Admin Plateforme
        </span>

                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter"
                                              :class="member.club_role === 'Administrateur' || member.club_role === 'Responsable' || member.club_role === 'Président'
                      ? 'bg-amber-100 text-black-700 border border-amber-200'
                      : 'bg-blue-100 text-slate-600 border border-slate-200'">
            {{ member.club_role }}
        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
