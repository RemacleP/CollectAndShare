<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import {
    Search, Inbox, Send, Star,
    MoreVertical, Reply, Trash2, CheckCircle, XCircle
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import {route} from "ziggy-js";

const props = defineProps({
    // Ces données viennent maintenant de ton contrôleur Laravel
    messages: { type: Array, default: () => [] },
});

const selectedId = ref(null);
const searchQuery = ref('');

// Filtrage dynamique des messages selon la recherche
const filteredMessages = computed(() => {
    return props.messages.filter(msg =>
        msg.subject.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        msg.sender_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Récupération du message sélectionné
const currentConversation = computed(() =>
    props.messages.find(m => m.id === selectedId.value)
);

// Fonctions pour gérer les demandes d'inscription
const approveRequest = (id) => {
    if (confirm('Voulez-vous valider cette inscription ?')) {
        // La route est maintenant 'registrations.approve'
        router.post(route('requests.approve', id));
    }
};

const rejectRequest = (id) => {
    if (confirm('Voulez-vous refuser cette inscription ?')) {
        // La route est maintenant 'registrations.reject'
        router.post(route('requests.reject', id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-64px)] bg-white dark:bg-zinc-950 overflow-hidden">

            <aside class="w-64 border-r border-zinc-100 dark:border-zinc-800 hidden lg:flex flex-col p-4 gap-2">
                <button class="bg-indigo-600 text-white rounded-2xl py-3 px-4 font-bold text-sm mb-4 shadow-lg shadow-indigo-200 dark:shadow-none hover:bg-indigo-700 transition">
                    Nouveau message
                </button>

                <nav class="space-y-1">
                    <button class="w-full flex items-center gap-3 px-3 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-xl font-bold text-sm">
                        <Inbox class="w-4 h-4" /> Boîte de réception
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-xl font-medium text-sm transition">
                        <Send class="w-4 h-4" /> Envoyés
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-900 rounded-xl font-medium text-sm transition">
                        <Star class="w-4 h-4" /> Favoris
                    </button>
                </nav>
            </aside>

            <section class="w-full lg:w-96 border-r border-zinc-100 dark:border-zinc-800 flex flex-col bg-zinc-50/50 dark:bg-zinc-900/30">
                <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-950">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                        <input v-model="searchQuery" type="text" placeholder="Rechercher..." class="w-full pl-10 pr-4 py-2 bg-zinc-100 dark:bg-zinc-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div
                        v-for="msg in filteredMessages"
                        :key="msg.id"
                        @click="selectedId = msg.id"
                        :class="[
                            'p-4 border-b border-zinc-100 dark:border-zinc-800 cursor-pointer hover:bg-white dark:hover:bg-zinc-800 transition-colors relative',
                            selectedId === msg.id ? 'bg-white dark:bg-zinc-800 ring-1 ring-inset ring-indigo-500/20 shadow-sm' : ''
                        ]"
                    >
                        <div v-if="!msg.read_at" class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-indigo-600 rounded-full"></div>

                        <div class="flex justify-between items-start mb-1">
                            <span :class="['text-sm truncate mr-2', !msg.read_at ? 'font-black text-zinc-900 dark:text-white' : 'font-medium text-zinc-500']">
                                {{ msg.sender_name || 'Système' }}
                            </span>
                            <span class="text-[10px] text-zinc-400 font-medium shrink-0">{{ msg.created_at_human }}</span>
                        </div>
                        <h4 class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate mb-1">{{ msg.subject }}</h4>
                        <p class="text-xs text-zinc-500 line-clamp-2 leading-relaxed">{{ msg.body }}</p>
                    </div>

                    <div v-if="filteredMessages.length === 0" class="p-8 text-center text-zinc-400 text-xs italic">
                        Aucun message trouvé.
                    </div>
                </div>
            </section>

            <main class="flex-1 bg-white dark:bg-zinc-950 hidden md:flex flex-col relative">
                <template v-if="currentConversation">
                    <div class="h-16 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between px-6">
                        <div class="flex gap-4">
                            <button class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg text-zinc-500" title="Répondre"><Reply class="w-4 h-4" /></button>
                            <button class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg text-red-500" title="Supprimer"><Trash2 class="w-4 h-4" /></button>
                        </div>
                        <button class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg text-zinc-500"><MoreVertical class="w-4 h-4" /></button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8">
                        <div class="max-w-3xl">
                            <h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6 uppercase tracking-tight leading-tight">
                                {{ currentConversation.subject }}
                            </h2>

                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-black text-xs uppercase">
                                    {{ (currentConversation.sender_name || 'S')[0] }}
                                </div>
                                <div>
                                    <p class="font-bold text-zinc-900 dark:text-white">{{ currentConversation.sender_name || 'Système' }}</p>
                                    <p class="text-xs text-zinc-400">Reçu {{ currentConversation.created_at_human }}</p>
                                </div>
                            </div>

                            <div class="text-zinc-700 dark:text-zinc-300 leading-relaxed space-y-4 whitespace-pre-wrap border-l-2 border-zinc-100 dark:border-zinc-800 pl-6">
                                {{ currentConversation.body }}
                            </div>

                            <div v-if="currentConversation.reference_type?.includes('RegistrationRequest')"
                                 class="mt-12 p-6 bg-indigo-50 dark:bg-indigo-900/20 rounded-3xl border border-indigo-100 dark:border-indigo-800/50 shadow-sm">
                                <h4 class="text-xs font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-widest mb-2">Décision administrative</h4>
                                <p class="text-sm text-indigo-700/80 dark:text-indigo-400/80 mb-6 font-medium">
                                    Cette demande est en attente. Une fois validée, l'utilisateur recevra ses accès par email.
                                </p>

                                <div class="flex flex-wrap gap-3">
                                    <div v-if="$page.props.flash.error"
                                         class="mb-4 p-4 bg-red-500 text-white rounded-2xl shadow-lg font-bold text-sm animate-pulse">
                                        ⚠️ {{ $page.props.flash.error }}
                                    </div>

                                    <div v-if="$page.props.flash.success"
                                         class="mb-4 p-4 bg-emerald-500 text-white rounded-2xl shadow-lg font-bold text-sm">
                                        ✅ {{ $page.props.flash.success }}
                                    </div>
                                    <button
                                        @click="approveRequest(currentConversation.reference_id)"
                                        class="flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 dark:shadow-none"
                                    >
                                        <CheckCircle class="w-4 h-4" /> Approuver
                                    </button>

                                    <button
                                        @click="rejectRequest(currentConversation.reference_id)"
                                        class="flex items-center gap-2 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 px-6 py-3 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition"
                                    >
                                        <XCircle class="w-4 h-4" /> Refuser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div v-else class="flex-1 flex flex-col items-center justify-center text-zinc-300 dark:text-zinc-800">
                    <Inbox class="w-20 h-20 mb-4 opacity-20" />
                    <p class="font-black uppercase tracking-widest text-sm opacity-20">Sélectionnez un message</p>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
