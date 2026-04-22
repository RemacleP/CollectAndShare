<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    UserPlus, Mail, MapPin, Package, FileText,
    Settings, X, MessageSquare, Send, CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    myConversations: Array, // Liste des salons passée par le contrôleur
});

const page = usePage();
const isOwnProfile = page.props.auth.user.id === props.user.id;

// --- GESTION DE L'INVITATION ---
const showInviteModal = ref(false);
const inviteForm = useForm({
    user_id: props.user.id,
});

const submitInvitation = (convSlug) => {
    // On envoie vers la route que nous avons définie précédemment
    inviteForm.post(route('chat.users.store', convSlug), {
        onSuccess: () => {
            showInviteModal.value = false;
            // On pourrait ajouter un toast de notification ici
        },
        preserveScroll: true,
    });
};

// Helper pour formater l'adresse proprement
const formatAddress = (addr) => {
    if (!addr) return null;
    return `${addr.street} ${addr.number}${addr.box ? '/' + addr.box : ''}, ${addr.postal_code} ${addr.city}, ${addr.country}`;
};
</script>

<template>
    <Head :title="user.firstname + ' ' + user.lastname" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white dark:bg-zinc-900 shadow-xl rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                    <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                    <div class="px-8 pb-8">
                        <div class="relative flex justify-between items-end -mt-12">
                            <div class="w-24 h-24 rounded-[2rem] bg-white dark:bg-zinc-800 border-4 border-white dark:border-zinc-900 flex items-center justify-center text-3xl font-black text-indigo-600 shadow-lg">
                                {{ user.firstname[0] }}{{ user.lastname[0] }}
                            </div>

                            <div class="flex gap-3">
                                <Link v-if="isOwnProfile" :href="route('profile.edit')"
                                      class="flex items-center gap-2 px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-sm font-bold rounded-2xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all">
                                    <Settings class="w-4 h-4" />
                                    Modifier
                                </Link>

                                <button v-else @click="showInviteModal = true"
                                        class="flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white text-sm font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-all active:scale-95">
                                    <UserPlus class="w-4 h-4" />
                                    Inviter dans un salon
                                </button>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h2 class="text-2xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">
                                {{ user.firstname }} {{ user.lastname }}
                            </h2>
                            <div class="flex items-center gap-2 text-zinc-500 mt-1">
                                <Mail class="w-4 h-4" />
                                <span class="font-medium">{{ user.email }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-8 bg-white dark:bg-zinc-900 rounded-[2rem] border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <div class="flex items-center gap-3 mb-4 text-indigo-600">
                            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                                <MapPin class="w-5 h-5" />
                            </div>
                            <h3 class="font-black uppercase text-xs tracking-widest">Résidence</h3>
                        </div>
                        <p v-if="user.address" class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">
                            {{ formatAddress(user.address) }}
                        </p>
                        <p v-else class="text-xs italic text-zinc-400">Aucune adresse enregistrée</p>
                    </div>

                    <div class="p-8 bg-white dark:bg-zinc-900 rounded-[2rem] border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <div class="flex items-center gap-3 mb-4 text-emerald-600">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                                <Package class="w-5 h-5" />
                            </div>
                            <h3 class="font-black uppercase text-xs tracking-widest">Livraison</h3>
                        </div>
                        <p v-if="user.shipping_address" class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">
                            {{ formatAddress(user.shipping_address) }}
                        </p>
                        <p v-else class="text-xs italic text-zinc-400">Identique à la résidence</p>
                    </div>

                    <div class="p-8 bg-white dark:bg-zinc-900 rounded-[2rem] border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <div class="flex items-center gap-3 mb-4 text-amber-600">
                            <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                                <FileText class="w-5 h-5" />
                            </div>
                            <h3 class="font-black uppercase text-xs tracking-widest">Facturation</h3>
                        </div>
                        <p v-if="user.billing_address" class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">
                            {{ formatAddress(user.billing_address) }}
                        </p>
                        <p v-else class="text-xs italic text-zinc-400">Identique à la résidence</p>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="showInviteModal"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-zinc-950/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl border border-zinc-200 dark:border-zinc-800 animate-in fade-in zoom-in duration-200">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-xl font-black text-zinc-900 dark:text-white uppercase tracking-tight">Inviter {{ user.firstname }}</h3>
                        <p class="text-xs font-bold text-zinc-400 mt-1 uppercase tracking-wider">Sélectionnez un salon de discussion</p>
                    </div>
                    <button @click="showInviteModal = false" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <div class="space-y-3 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                    <template v-if="myConversations && myConversations.length > 0">
                        <button v-for="conv in myConversations" :key="conv.id"
                                @click="submitInvitation(conv.slug)"
                                :disabled="inviteForm.processing"
                                class="w-full flex items-center justify-between p-4 rounded-[1.5rem] bg-zinc-50 dark:bg-zinc-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-left transition-all group border border-transparent hover:border-indigo-100 dark:hover:border-indigo-900/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-zinc-800 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                    <MessageSquare class="w-5 h-5 text-indigo-600" />
                                </div>
                                <span class="font-bold text-zinc-700 dark:text-zinc-200">{{ conv.title }}</span>
                            </div>
                            <Send class="w-4 h-4 text-zinc-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" />
                        </button>
                    </template>
                    <div v-else class="py-8 text-center">
                        <p class="text-sm text-zinc-500 font-medium">Vous n'avez aucun salon actif pour le moment.</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <button @click="showInviteModal = false" class="text-[10px] font-black uppercase tracking-widest text-zinc-400 hover:text-zinc-600 transition-colors">
                        Fermer la fenêtre
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e4e4e7; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; }
</style>
