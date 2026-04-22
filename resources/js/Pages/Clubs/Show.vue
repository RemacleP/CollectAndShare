<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import * as LucideIcons from 'lucide-vue-next';
import {
    ChevronLeft,
    MessageSquare,
    Calendar,
    Clock,
    MapPin,
    ArrowRight,
    Globe
} from 'lucide-vue-next';

const props = defineProps({
    club: Object,
    can: Object // { edit: boolean }
});

const page = usePage();

// Sécurisation : authUser peut être null si le visiteur n'est pas connecté
const authUser = computed(() => page.props.auth?.user || null);

/**
 * RÉCUPÉRATION DYNAMIQUE DES ICÔNES
 */
const getSocialIcon = (iconName) => {
    return LucideIcons[iconName] || Globe;
};

/**
 * Sécurisation de la vérification de membre
 * On vérifie d'abord si authUser existe avant d'accéder à son ID
 */
const isClubMember = computed(() => {
    if (!authUser.value) return false;
    return props.club.members?.some(member => member.id === authUser.value.id);
});

const defaultChatSlug = computed(() => {
    return props.club.conversations?.[0]?.slug || null;
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('clubs.index'));
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: 'numeric', month: 'short'
    });
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
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
                    class="group flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors"
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

                            <div v-if="authUser && (isClubMember || can.edit)" class="pt-4">
                                <Link
                                    v-if="defaultChatSlug"
                                    :href="route('clubs.chat.show', { club: club.slug, conversation: defaultChatSlug })"
                                    class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 active:scale-95"
                                >
                                    <MessageSquare class="w-5 h-5" />
                                    Ouvrir le Chat du Club
                                </Link>

                                <p v-else class="text-xs text-red-500 font-bold italic">
                                    Aucun salon de discussion disponible.
                                </p>

                                <p class="text-[10px] text-zinc-400 font-bold uppercase mt-3 ml-1 tracking-wider">
                                    Espace réservé aux membres
                                </p>
                            </div>

                            <div v-else-if="!authUser" class="pt-4 p-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                <p class="text-sm text-gray-500 italic">
                                    Connectez-vous pour rejoindre ce club et accéder au chat privé.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-2xl p-5 sm:p-6 border border-gray-100">
                                <h3 class="font-bold text-gray-900 border-b border-gray-200 pb-3 mb-4 font-black uppercase text-xs tracking-widest">Informations</h3>

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

                                    <div v-if="club.socials && club.socials.length > 0" class="pt-4 border-t border-gray-200">
                                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-3">Suivez-nous</p>
                                        <div class="flex flex-wrap gap-2">
                                            <a
                                                v-for="social in club.socials"
                                                :key="social.name"
                                                :href="social.url"
                                                target="_blank"
                                                class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-zinc-200 text-zinc-500 hover:border-indigo-600 hover:text-indigo-600 hover:shadow-sm transition-all active:scale-90"
                                                :title="social.name"
                                            >
                                                <component
                                                    :is="getSocialIcon(social.icon)"
                                                    class="w-4 h-4 transition-transform group-hover:scale-110"
                                                />
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="can.edit" class="mt-6 pt-6 border-t border-gray-200">
                                    <Link :href="route('clubs.edit', club.slug)"
                                          class="flex justify-center items-center w-full bg-white border-2 border-indigo-600 text-indigo-600 px-4 py-2.5 rounded-xl hover:bg-indigo-50 transition font-black text-xs uppercase tracking-widest">
                                        ⚙️ Modifier le club
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
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-3">
                                👥 Membres du club
                                <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-black">
                                    {{ club.members_count }}
                                </span>
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Link
                                v-for="member in club.members"
                                :key="member.id"
                                :href="route('profile.show', { id: member.id })"
                                class="group flex items-center p-4 bg-white rounded-2xl border border-zinc-100 shadow-sm transition-all hover:shadow-md hover:border-indigo-300"
                            >
                                <div class="h-12 w-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 font-black text-base mr-4 shrink-0 uppercase transition-transform group-hover:scale-105">
                                    {{ member.firstname?.[0] }}{{ member.lastname?.[0] }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-black text-zinc-900 truncate group-hover:text-indigo-600 transition-colors">
                                        {{ member.firstname }} {{ member.lastname }}
                                    </h4>

                                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                        <span v-if="member.is_super_admin"
                                              class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest bg-red-600 text-white shadow-sm">
                                            Staff
                                        </span>

                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-tighter border border-zinc-200 bg-zinc-100 text-zinc-600">
                                            {{ member.club_role || 'Membre' }}
                                        </span>
                                    </div>
                                </div>
                                <ArrowRight class="h-4 w-4 text-zinc-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all ml-2" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
