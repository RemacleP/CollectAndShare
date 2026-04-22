<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, watch, nextTick, onUnmounted, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Send, Hash, Lock, MessageSquare, Plus, X, ChevronLeft } from 'lucide-vue-next';

const props = defineProps({
    club: Object,
    conversations: Array,
    activeConversation: Object,
    messages: Array,
    userRole: String,
    isClubMember: Boolean,
    conversationMembers: Array
});

const form = useForm({ content: '' });
const messagesContainer = ref(null);
const showNewConvModal = ref(false);
const usersOnline = ref([]);

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// --- LOGIQUE DE PRÉSENCE ---
const listenForPresence = () => {
    if (typeof window !== 'undefined' && window.Echo && props.activeConversation) {
        window.Echo.leave(`chat.${props.activeConversation.id}`);

        window.Echo.join(`chat.${props.activeConversation.id}`)
            .here((users) => {
                usersOnline.value = users;
            })
            .joining((user) => {
                usersOnline.value.push(user);
            })
            .leaving((user) => {
                usersOnline.value = usersOnline.value.filter(u => u.id !== user.id);
            })
            .listen('.MessageSent', (e) => {
                router.reload({
                    only: ['messages'],
                    preserveScroll: true,
                    onSuccess: () => scrollToBottom()
                });
            })
            .error((error) => {
                console.error("Erreur Echo :", error);
            });
    }
};

const isOnline = (userId) => {
    return usersOnline.value.some(u => u.id == userId);
};

// --- ENVOI DE MESSAGE ---
const submit = () => {
    if (!form.content.trim()) return;

    // Correction ici : Nom de route complet et passage du club + conversation
    form.post(route('clubs.chat.messages.store', {
        club: props.club.slug,
        conversation: props.activeConversation.slug
    }), {
        onSuccess: () => form.reset(),
        preserveScroll: true,
    });
};

onMounted(() => {
    scrollToBottom();
    listenForPresence();
});

onUnmounted(() => {
    if (window.Echo && props.activeConversation) {
        window.Echo.leave(`chat.${props.activeConversation.id}`);
    }
});

watch(() => props.activeConversation?.id, (newId, oldId) => {
    if (window.Echo && oldId) window.Echo.leave(`chat.${oldId}`);
    if (newId) {
        listenForPresence();
        scrollToBottom();
    }
});

watch(() => props.messages, scrollToBottom, { deep: true });

// --- MODAL SALON ---
const newConvForm = useForm({ title: '', is_private: false });

const createConversation = () => {
    // Correction ici : Nom de route complet 'clubs.chat.conversations.store'
    newConvForm.post(route('clubs.chat.conversations.store', { club: props.club.slug }), {
        onSuccess: () => {
            showNewConvModal.value = false;
            newConvForm.reset();
        },
    });
};
// Fonction pour retourner à la page du club
const goBack = () => {
    router.visit(route('clubs.show', props.club.slug));
};
</script>

<template>
    <Head :title="`Chat - ${club.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div class="flex items-center gap-3">
                    <MessageSquare class="w-6 h-6 text-indigo-600" />
                    <h2 class="font-black text-xl text-zinc-800 dark:text-white uppercase tracking-tight">
                        Espace Discussion : {{ club.name }}
                    </h2>
                </div>
                <button
                    @click="goBack"
                    class="group flex items-center gap-2 text-sm font-black uppercase tracking-widest text-zinc-500 hover:text-indigo-600 transition-all"
                >
                    <ChevronLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Retour au club
                </button>
            </div>
        </template>
        <div class="py-6 h-[calc(100vh-100px)]">
            <div class="max-w-[1600px] mx-auto px-4 h-full">
                <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] shadow-sm border border-zinc-200 dark:border-zinc-800 h-full flex overflow-hidden">

                    <div class="w-64 bg-zinc-50 dark:bg-zinc-950/50 border-r border-zinc-100 dark:border-zinc-800 flex flex-col shrink-0">
                        <div class="p-6 border-b flex items-center justify-between">
                            <div class="min-w-0">
                                <h2 class="font-black text-xs uppercase text-indigo-600 truncate">{{ club.name }}</h2>
                                <p class="text-[9px] font-black text-zinc-400 uppercase tracking-widest">Salons</p>
                            </div>
                            <button v-if="isClubMember" @click="showNewConvModal = true"
                                    class="p-2 hover:bg-indigo-50 text-indigo-600 rounded-xl transition-all active:scale-90">
                                <Plus class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-1">
                            <Link v-for="conv in conversations" :key="conv.id"
                                  :href="route('clubs.chat.show', { club: club.slug, conversation: conv.slug })"
                                  class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-sm transition-all"
                                  :class="activeConversation?.id === conv.id ? 'bg-indigo-600 text-white shadow-lg' : 'text-zinc-500 hover:bg-white dark:hover:bg-zinc-800'">
                                <Hash v-if="!conv.is_private" class="w-4 h-4 opacity-40" />
                                <Lock v-else class="w-4 h-4 opacity-40" />
                                <span class="truncate">{{ conv.title }}</span>
                            </Link>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col bg-white dark:bg-zinc-900 min-w-0">
                        <template v-if="activeConversation">
                            <div class="px-8 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <Hash class="w-5 h-5 text-indigo-600" />
                                    <span class="font-black text-zinc-900 dark:text-white uppercase tracking-tight">{{ activeConversation.title }}</span>
                                </div>
                            </div>

                            <div ref="messagesContainer" class="flex-1 overflow-y-auto p-8 space-y-6">
                                <div v-for="msg in messages" :key="msg.id" class="flex gap-4" :class="msg.user_id === $page.props.auth.user.id ? 'flex-row-reverse' : 'flex-row'">
                                    <div class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-[10px] font-black uppercase text-zinc-500 shrink-0 border border-zinc-200">
                                        {{ msg.user.firstname[0] }}{{ msg.user.lastname[0] }}
                                    </div>
                                    <div class="flex flex-col max-w-[70%]" :class="msg.user_id === $page.props.auth.user.id ? 'items-end' : 'items-start'">
                                        <span class="text-[9px] font-black uppercase text-zinc-400 mb-1 px-1">{{ msg.user.firstname }}</span>
                                        <div class="p-4 text-sm font-medium shadow-sm border"
                                             :class="msg.user_id === $page.props.auth.user.id ? 'bg-indigo-600 border-indigo-500 text-white rounded-2xl rounded-tr-none' : 'bg-white dark:bg-zinc-800 border-zinc-100 text-zinc-800 dark:text-zinc-200 rounded-2xl rounded-tl-none'">
                                            {{ msg.content }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 border-t border-zinc-100 dark:border-zinc-800">
                                <form @submit.prevent="submit" class="relative flex items-center gap-2">
                                    <input v-model="form.content" type="text" placeholder="Écrire un message..."
                                           class="w-full bg-zinc-100 dark:bg-zinc-800 border-none rounded-2xl px-6 py-4 focus:ring-2 focus:ring-indigo-600 text-sm" />
                                    <button type="submit" class="p-4 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all">
                                        <Send class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </template>
                        <div v-else class="flex-1 flex flex-col items-center justify-center text-zinc-300">
                            <MessageSquare class="w-12 h-12 mb-4 opacity-10" />
                            <p class="font-black uppercase tracking-[0.2em] text-[10px]">Sélectionnez un salon</p>
                        </div>
                    </div>

                    <div v-if="activeConversation" class="w-72 bg-zinc-50 dark:bg-zinc-950/50 border-l border-zinc-100 dark:border-zinc-800 hidden xl:flex flex-col">
                        <div class="p-6 border-b border-zinc-100 dark:border-zinc-800">
                            <p class="text-[10px] font-black uppercase text-zinc-400 tracking-widest">Membres — {{ usersOnline.length }}</p>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-4">
                            <div v-for="member in conversationMembers" :key="member.id"
                                 class="flex items-center gap-3 px-2 transition-all"
                                 :class="isOnline(member.id) ? 'opacity-100' : 'opacity-40 grayscale'">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-[10px] font-black uppercase">
                                        {{ member.firstname[0] }}{{ member.lastname[0] }}
                                    </div>
                                    <div v-if="isOnline(member.id)" class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-4 border-zinc-50 dark:border-zinc-950 rounded-full animate-pulse"></div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-black text-zinc-800 dark:text-zinc-200 truncate uppercase">{{ member.firstname }}</p>
                                    <p class="text-[9px] font-bold" :class="isOnline(member.id) ? 'text-green-600' : 'text-zinc-400'">
                                        {{ isOnline(member.id) ? 'En ligne' : 'Hors-ligne' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="showNewConvModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-zinc-950/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-md rounded-[2.5rem] p-8 border border-zinc-200 dark:border-zinc-800 shadow-2xl">
                <div class="flex justify-between items-start mb-6 font-black uppercase text-zinc-900 dark:text-white">
                    <h3>Nouveau salon</h3>
                    <button @click="showNewConvModal = false"><X class="w-6 h-6 text-zinc-400" /></button>
                </div>
                <form @submit.prevent="createConversation" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-zinc-400 mb-2 ml-1">Nom du salon</label>
                        <input v-model="newConvForm.title" type="text" placeholder="ex: Discussion Générale"
                               class="w-full bg-zinc-100 dark:bg-zinc-800 border-none rounded-2xl p-4 font-medium" />
                    </div>
                    <div class="flex items-center gap-3 px-1">
                        <input type="checkbox" v-model="newConvForm.is_private" id="priv" class="rounded text-indigo-600" />
                        <label for="priv" class="text-sm font-bold text-zinc-600 dark:text-zinc-400">Salon privé (invitations)</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showNewConvModal = false" class="flex-1 p-4 font-bold text-zinc-500 hover:bg-zinc-100 rounded-2xl transition-all">Annuler</button>
                        <button type="submit" :disabled="newConvForm.processing || !newConvForm.title"
                                class="flex-1 bg-indigo-600 text-white p-4 rounded-2xl font-black uppercase text-xs tracking-widest disabled:opacity-50">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
