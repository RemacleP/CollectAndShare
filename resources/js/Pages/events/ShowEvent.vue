<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {computed, onMounted, onUnmounted, ref} from 'vue';
import {
    Calendar, MapPin, Clock, Building2, User,
    ChevronLeft, Edit3, CheckCircle, AlertCircle, Euro,
    ExternalLink, Share2
} from 'lucide-vue-next';

// Props
const props = defineProps<{
    event: any;
    isRegistered: boolean;
}>();

const processing = ref(false);
const page = usePage();

const breadcrumbs = [
    { name: 'Événements', href: route('events.index') },
    { name: props.event.title, href: '#' },
];

const canManage = computed(() => {
    const user = page.props.auth.user as any;
    if (!user) return false;
    return user.is_admin || (user.club_ids && user.club_ids.includes(props.event.club_id));
});

// Formatters
const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });
};

const formatTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const formatPrice = (price: number | null) => {
    if (price === null || price === 0) return 'Gratuit';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
};

const joinEvent = () => {
    router.post(route('events.join', props.event.slug), {}, {
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
        preserveScroll: true,
    });
};

const leaveEvent = () => {
    if (!confirm('Voulez-vous vraiment vous désinscrire ?')) return;
    router.delete(route('events.leave', props.event.slug), {
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
        preserveScroll: true,
    });
};
const goBack = () => {
    // Si l'historique existe, on recule, sinon on redirige vers l'index par sécurité
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('events.index'));
    }
};
//Rafraichissement inscriptions
// 1. Créer une ref pour l'heure actuelle
const now = ref(new Date());
let timer: ReturnType<typeof setInterval>;

// 2. Mettre à jour l'heure chaque seconde
onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

// 3. Nettoyer le timer quand on quitte la page
onUnmounted(() => {
    clearInterval(timer);
});

// 4. Modifier la logique d'inscription pour utiliser 'now'
const isDeadlinePassed = computed(() => {
    if (!props.event.registration_deadline) return false;
    return now.value > new Date(props.event.registration_deadline);
});

//compte à rebour
const timeLeft = computed(() => {
    if (!props.event.registration_deadline) return null;

    const diff = new Date(props.event.registration_deadline).getTime() - now.value.getTime();

    if (diff <= 0) return null;

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    // Retourne le format 00:00:00
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

</script>

<template>
    <Head :title="props.event.title" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ props.event.title }}
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
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <div class="mb-8 flex items-center justify-between">


                <div class="flex items-center gap-3">
                    <Link v-if="canManage" :href="route('events.edit', props.event.slug)" class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700">
                        <Edit3 class="h-4 w-4 text-indigo-500" />
                        Modifier
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">

                <div class="lg:col-span-2 space-y-10">

                    <div class="relative aspect-[16/9] overflow-hidden rounded-[2.5rem] border border-gray-200 bg-gray-100 shadow-2xl dark:border-gray-700 dark:bg-gray-800">
                        <img v-if="props.event.image" :src="`/storage/${props.event.image}`" class="h-full w-full object-cover transition-transform duration-700 hover:scale-105" />
                        <div v-else class="flex h-full flex-col items-center justify-center text-gray-400">
                            <Calendar class="h-20 w-20 opacity-10" />
                        </div>

                        <div class="absolute left-6 top-6">
                            <span class="inline-flex items-center gap-2 rounded-2xl bg-white/95 px-4 py-2 text-xs font-black uppercase tracking-widest text-gray-900 shadow-xl backdrop-blur dark:bg-gray-900/90 dark:text-white">
                                <span class="flex h-2 w-2">
                                    <span class="absolute inline-flex h-2 w-2 animate-ping rounded-full opacity-75" :class="props.event.status === 'validated' ? 'bg-green-400' : 'bg-yellow-400'"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full" :class="props.event.status === 'validated' ? 'bg-green-500' : 'bg-yellow-500'"></span>
                                </span>
                                {{ props.event.status === 'validated' ? 'Confirmé' : 'En attente' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-gray-900 dark:text-white">
                            {{ props.event.title }}
                        </h1>
                        <div class="mt-6 flex flex-wrap items-center gap-6 text-sm">
                            <Link :href="route('clubs.show', props.event.club?.slug)" class="flex items-center gap-2 font-bold text-indigo-600 hover:underline dark:text-indigo-400">
                                <Building2 class="h-5 w-5" />
                                {{ props.event.club?.name }}
                            </Link>
                            <span class="flex items-center gap-2 font-medium text-gray-500 dark:text-gray-400">
                                <MapPin class="h-5 w-5 text-red-500" />
                                {{ props.event.city }}, {{ props.event.country }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-gray-100 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-800/40">
                        <h2 class="mb-6 text-2xl font-black text-gray-900 dark:text-white">Description</h2>
                        <div class="prose prose-indigo max-w-none dark:prose-invert whitespace-pre-line text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ props.event.description || 'Aucune description disponible pour le moment.' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-6">

                    <div class="sticky top-8 rounded-[2.5rem] border border-gray-200 bg-white p-8 shadow-2xl dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-8 flex items-end justify-between border-b border-gray-100 pb-6 dark:border-gray-700">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-400">Participation</p>
                                <p class="text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ formatPrice(props.event.price) }}</p>
                            </div>
                            <Euro class="h-12 w-12 text-gray-100 dark:text-gray-700" />
                        </div>

                        <div class="space-y-6">
                            <div class="group flex gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition-colors group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-900/30 dark:text-indigo-400">
                                    <Calendar class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">{{ formatDate(props.event.start_datetime) }}</p>
                                    <p class="text-xs font-bold text-gray-500">{{ formatTime(props.event.start_datetime) }} — {{ formatTime(props.event.end_datetime) }}</p>
                                </div>
                            </div>

                            <div class="group flex gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition-colors group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-900/30 dark:text-indigo-400">
                                    <MapPin class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">{{ props.event.location_name }}</p>
                                    <p class="text-xs font-medium text-gray-500 leading-tight">{{ props.event.address }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10">
                            <div v-if="props.isRegistered" class="space-y-4">
                                <div class="flex items-center gap-3 rounded-2xl bg-green-50 p-4 text-sm font-bold text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-900/50">
                                    <CheckCircle class="h-6 w-6" />
                                    Vous êtes inscrit !
                                </div>
                                <button @click="leaveEvent" :disabled="processing" class="w-full rounded-2xl border-2 border-red-100 py-4 text-sm font-black text-red-500 transition-all hover:bg-red-50 hover:text-red-600 dark:border-red-900/30 dark:hover:bg-red-900/20">
                                    Annuler mon inscription
                                </button>
                            </div>

                            <div v-else-if="props.event.registration_required">
                                <div v-if="isDeadlinePassed" class="flex items-center justify-center gap-2 rounded-2xl bg-gray-100 py-4 text-sm font-bold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                    <AlertCircle class="h-5 w-5" />
                                    Inscriptions closes
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-if="props.event.registration_deadline" class="flex items-center justify-center gap-2 text-xs font-bold text-gray-400">
                                        <Clock class="h-3 w-3" />
                                        Limite : {{ new Date(props.event.registration_deadline).toLocaleDateString() }}
                                        <div v-if="timeLeft"
                                             class="flex items-center gap-2 rounded-full px-4 py-1 text-[11px] font-black uppercase tracking-wider shadow-sm border"
                                             :class="timeLeft.startsWith('00:') ? 'bg-red-50 text-red-600 border-red-100 animate-pulse' : 'bg-indigo-50 text-indigo-600 border-indigo-100'"
                                        >
                                            <Clock class="h-3 w-3" />
                                            Ferme dans : {{ timeLeft }}
                                        </div>
                                    </div>
                                    <button @click="joinEvent" :disabled="processing" class="w-full rounded-2xl bg-indigo-600 py-5 text-lg font-black text-white shadow-xl shadow-indigo-100 transition-all hover:bg-indigo-700 hover:shadow-none active:scale-[0.98] dark:shadow-none">
                                        {{ processing ? 'Action en cours...' : "Réserver ma place" }}
                                    </button>
                                </div>
                            </div>

                            <div v-else class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-emerald-50 py-6 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30">
                                <CheckCircle class="h-8 w-8 text-emerald-500" />
                                <span class="font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest text-xs">Entrée libre</span>
                            </div>
                        </div>
                    </div>

                    <Link :href="route('clubs.show', props.event.club?.slug)" class="group flex items-center gap-4 rounded-[2rem] border border-gray-100 bg-white p-6 transition-all hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gray-100 overflow-hidden transition-colors group-hover:bg-indigo-600 dark:bg-gray-700">
                            <img
                                v-if="props.event.club?.logo"
                                :src="`/storage/${props.event.club.logo}`"
                                class="h-full w-full object-contain p-1"
                                :alt="props.event.club.name"
                            />
                            <span
                                v-else
                                class="text-2xl font-black text-gray-400 group-hover:text-white"
                            >
            {{ props.event.club?.name?.charAt(0) }}
        </span>
                        </div>

                        <div class="flex-1 overflow-hidden">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Organisé par</p>
                            <p class="truncate font-black text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400 transition-colors">
                                {{ props.event.club?.name }}
                            </p>
                            <span class="text-xs font-bold text-indigo-500 flex items-center gap-1">
            Voir le profil <ExternalLink class="h-3 w-3" />
        </span>
                        </div>
                    </Link>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.prose {
    line-height: 1.8;
}
</style>
