<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import * as LucideIcons from 'lucide-vue-next';
import {
    ChevronLeft,
    UserPlus,
    Mail,
    MapPin,
    Package,
    FileText,
    Settings,
    X,
    MessageSquare,
    Send,
    Globe,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    myConversations: Array,
    socials: Array,
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);
const isOwnProfile = computed(() => authUser.value?.id === props.user.id);

const getSocialIcon = (iconName) => {
    return LucideIcons[iconName] || Globe;
};

const goBack = () => {
    window.history.length > 1 ? window.history.back() : router.visit(route('dashboard'));
};

// Gestion de l'invitation
const showInviteModal = ref(false);
const inviteForm = useForm({ user_id: props.user.id });

const submitInvitation = (convSlug) => {
    inviteForm.post(route('chat.users.store', convSlug), {
        onSuccess: () => showInviteModal.value = false,
        preserveScroll: true,
    });
};

const formatAddress = (addr) => {
    if (!addr) return null;
    return `${addr.street} ${addr.number}${addr.box ? '/' + addr.box : ''}, ${addr.postal_code} ${addr.city}`;
};
</script>

<template>
    <Head :title="`${user.firstname} ${user.lastname}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profil Utilisateur</h2>
                <button @click="goBack" class="group flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-600 transition-colors">
                    <ChevronLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Retour
                </button>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-gray-100">

                    <div class="relative h-48 sm:h-64 bg-indigo-900">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-700 opacity-90"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

                        <div class="absolute bottom-0 left-0 p-6 sm:p-10 text-white w-full flex items-end gap-6">
                            <div class="shrink-0">
                                <div class="h-24 w-24 sm:h-32 sm:w-32 rounded-[2rem] border-4 border-white/20 shadow-2xl overflow-hidden bg-white flex items-center justify-center text-3xl font-black text-indigo-600 uppercase">
                                    {{ user.firstname[0] }}{{ user.lastname[0] }}
                                </div>
                            </div>

                            <div class="min-w-0 pb-2">
                                <h1 class="text-2xl sm:text-4xl font-bold mb-1 leading-tight drop-shadow-md">
                                    {{ user.firstname }} {{ user.lastname }}
                                </h1>
                                <p v-if="user.address" class="flex items-center text-sm opacity-90">
                                    <MapPin class="w-4 h-4 mr-2" /> {{ user.address.city }}, {{ user.address.country }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-6 sm:p-10">

                        <div class="lg:col-span-2 space-y-8">

                            <div class="flex flex-wrap gap-4">
                                <Link v-if="isOwnProfile" :href="route('profile.edit')"
                                      class="flex items-center gap-3 px-6 py-3 bg-zinc-900 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-zinc-800 transition shadow-lg active:scale-95">
                                    <Settings class="w-4 h-4" />
                                    Paramètres du profil
                                </Link>

                                <button v-else-if="authUser" @click="showInviteModal = true"
                                        class="flex items-center gap-3 px-6 py-3 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-95">
                                    <UserPlus class="w-4 h-4" />
                                    Inviter dans un salon
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-6 bg-zinc-50 rounded-3xl border border-zinc-100">
                                    <div class="flex items-center gap-3 mb-4 text-emerald-600">
                                        <Package class="w-5 h-5" />
                                        <h3 class="font-black uppercase text-[10px] tracking-widest">Adresse de Livraison</h3>
                                    </div>
                                    <p class="text-sm text-zinc-600 font-medium">
                                        {{ formatAddress(user.shipping_address) || formatAddress(user.address) || 'Non renseigné' }}
                                    </p>
                                </div>

                                <div class="p-6 bg-zinc-50 rounded-3xl border border-zinc-100">
                                    <div class="flex items-center gap-3 mb-4 text-amber-600">
                                        <FileText class="w-5 h-5" />
                                        <h3 class="font-black uppercase text-[10px] tracking-widest">Adresse de Facturation</h3>
                                    </div>
                                    <p class="text-sm text-zinc-600 font-medium">
                                        {{ formatAddress(user.billing_address) || formatAddress(user.address) || 'Non renseigné' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-[2rem] p-6 border border-gray-100">
                                <h3 class="font-bold text-gray-900 border-b border-gray-200 pb-3 mb-4 font-black uppercase text-[10px] tracking-widest">Informations de contact</h3>

                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Email professionnel</p>
                                        <a :href="`mailto:${user.email}`" class="text-indigo-600 hover:underline text-sm font-bold">{{ user.email }}</a>
                                    </div>

                                    <div v-if="socials && socials.length > 0" class="pt-4 border-t border-gray-200">
                                        <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-3">Réseaux Sociaux</p>
                                        <div class="flex flex-col gap-3">
                                            <a v-for="link in socials" :key="link.id" :href="link.full_url" target="_blank"
                                               class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-zinc-200 hover:border-indigo-600 transition-all group">
                                                <component :is="getSocialIcon(link.platform.icon)" class="w-4 h-4 text-zinc-400 group-hover:text-indigo-600" />
                                                <div class="flex flex-col">
                                                    <span class="text-[8px] font-black uppercase text-zinc-400 leading-none">{{ link.platform.name }}</span>
                                                    <span class="text-xs font-bold text-zinc-700 truncate">{{ link.identifier }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showInviteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-zinc-950/40 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl animate-in fade-in zoom-in duration-200">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-xl font-black text-zinc-900 uppercase tracking-tight">Inviter {{ user.firstname }}</h3>
                    <button @click="showInviteModal = false" class="text-zinc-400 hover:text-zinc-600"><X /></button>
                </div>

                <div class="space-y-3 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                    <button v-for="conv in myConversations" :key="conv.id" @click="submitInvitation(conv.slug)"
                            class="w-full flex items-center justify-between p-4 rounded-2xl bg-zinc-50 hover:bg-indigo-50 group transition-all border border-transparent hover:border-indigo-100">
                        <div class="flex items-center gap-3">
                            <MessageSquare class="w-5 h-5 text-indigo-600" />
                            <span class="font-bold text-zinc-700 text-sm">{{ conv.title }}</span>
                        </div>
                        <ArrowRight class="w-4 h-4 text-zinc-300 group-hover:text-indigo-600" />
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
