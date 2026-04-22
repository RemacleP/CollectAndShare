<script setup>
import { ref, watch } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';
import {
    UserCheck,
    Shield,
    Ban,
    Mail,
    ExternalLink,
    Search,
    ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
    users: Object,
    filters: Object
});

const search = ref(props.filters.search || '');

// Recherche avec debounce pour préserver les performances serveur
watch(search, debounce((value) => {
    router.get(route('admin.users.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

const verifyUser = (userId) => {
    if (confirm('Confirmer la vérification de cet utilisateur ?')) {
        router.patch(route('admin.users.verify', userId));
    }
};

const toggleBan = (user) => {
    const action = user.is_banned ? 'débannir' : 'bannir';
    if (confirm(`Voulez-vous vraiment ${action} ${user.firstname} ?`)) {
        router.patch(route('admin.users.ban', user.id));
    }
};
</script>

<template>
    <Head title="Gestion Utilisateurs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-zinc-200 leading-tight">
                Administration des Membres
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div class="relative w-full md:w-1/3">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher un nom ou email..."
                            class="w-full border-gray-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm pl-10 h-12"
                        />
                        <Search class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" />
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-[10px] font-black uppercase text-zinc-400 tracking-widest">Total Membres</p>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white leading-none">{{ users.total }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="users.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="user in users.data" :key="user.id"
                         class="group relative bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-zinc-800 flex flex-col transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                         :class="{'ring-2 ring-red-500 ring-offset-2 dark:ring-offset-zinc-950': user.is_banned}">

                        <div v-if="user.is_banned" class="absolute top-0 right-0 z-20">
                            <div class="bg-red-600 text-white text-[10px] font-black px-4 py-1.5 rounded-bl-2xl shadow-lg flex items-center gap-1 uppercase tracking-tighter">
                                <ShieldAlert class="w-3 h-3" /> Banni
                            </div>
                        </div>

                        <div class="p-6 flex-grow" :class="{'opacity-50 grayscale': user.is_banned}">
                            <div class="flex items-start justify-between mb-4">
                                <Link :href="route('profile.show', user.username)" class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center text-lg font-black text-indigo-600 dark:text-indigo-400 uppercase transition-transform active:scale-95">
                                    {{ user.firstname[0] }}{{ user.lastname[0] }}
                                </Link>

                                <div class="flex flex-col items-end gap-2">
                                    <span :class="user.is_admin ? 'bg-zinc-900 text-white dark:bg-white dark:text-black' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'"
                                          class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm">
                                        {{ user.is_admin ? 'Admin' : 'Membre' }}
                                    </span>
                                    <div v-if="user.id_verified_at" class="flex items-center gap-1 text-green-600 bg-green-50 dark:bg-green-500/10 px-2 py-1 rounded-md">
                                        <UserCheck class="w-3 h-3" />
                                        <span class="text-[8px] font-black uppercase">Vérifié</span>
                                    </div>
                                </div>
                            </div>

                            <Link :href="route('profile.show', user.id)" class="block">
                                <h3 class="font-black text-lg text-zinc-900 dark:text-white uppercase leading-tight group-hover:text-indigo-600 transition-colors">
                                    {{ user.firstname }} {{ user.lastname }}
                                </h3>
                                <p class="text-xs font-bold text-zinc-400 lowercase mb-4">@{{ user.username }}</p>
                            </Link>

                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400 text-xs">
                                    <Mail class="w-3.5 h-3.5" />
                                    <span class="truncate">{{ user.email }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 mt-auto">
                            <div class="flex justify-between items-center" v-if="user.id !== 1">
                                <div class="flex gap-2">
                                    <button v-if="!user.id_verified_at"
                                            @click="verifyUser(user.id)"
                                            class="p-2.5 bg-white dark:bg-zinc-900 text-green-600 hover:bg-green-600 hover:text-white rounded-xl shadow-sm transition-all border border-zinc-200 dark:border-zinc-700"
                                            title="Valider l'identité">
                                        <Shield class="w-4 h-4" />
                                    </button>

                                    <button @click="toggleBan(user)"
                                            class="p-2.5 bg-white dark:bg-zinc-900 shadow-sm transition-all border border-zinc-200 dark:border-zinc-700 rounded-xl"
                                            :class="user.is_banned ? 'text-blue-600 hover:bg-blue-600 hover:text-white' : 'text-red-600 hover:bg-red-600 hover:text-white'"
                                            :title="user.is_banned ? 'Débannir' : 'Bannir'">
                                        <Ban class="w-4 h-4" />
                                    </button>
                                </div>

                                <Link :href="route('profile.show', user.id)" class="inline-flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 font-black text-[10px] uppercase tracking-widest hover:gap-3 transition-all">
                                    Gérer <ExternalLink class="w-3 h-3" />
                                </Link>
                            </div>
                            <div v-else class="text-center">
                                <span class="text-[9px] font-black text-zinc-400 dark:text-zinc-600 uppercase tracking-[0.3em]">Profil Racine Protégé</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-24 bg-white dark:bg-zinc-900 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                    <p class="text-zinc-500 font-bold uppercase tracking-widest text-sm">Aucun membre trouvé pour cette recherche</p>
                </div>

                <div class="mt-12 flex justify-center gap-2">
                    <Link v-for="link in users.links" :key="link.label"
                          :href="link.url || '#'"
                          v-html="link.label"
                          class="px-5 py-2.5 rounded-xl text-[11px] font-black uppercase transition-all"
                          :class="link.active
                            ? 'bg-zinc-900 text-white dark:bg-white dark:text-black shadow-xl scale-110'
                            : 'bg-white dark:bg-zinc-900 text-zinc-400 dark:text-zinc-500 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50'"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
