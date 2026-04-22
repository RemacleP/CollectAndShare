<script setup>
import { router, Link } from '@inertiajs/vue3';
import { Eye, Trash2 } from 'lucide-vue-next';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({ clubs: Object });

const restoreClub = (id) => {
    if (confirm("Restaurer ce club ?")) {
        router.post(route('admin.clubs.restore', id));
    }
};

const confirmForceDelete = (id) => {
    if (confirm("ATTENTION : Cette action supprimera DEFINITIVEMENT le club de la base de données. Continuer ?")) {
        router.delete(route('admin.clubs.force-delete', id));
    }
};
</script>
<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-zinc-200">
                    <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                        <h2 class="text-xl font-black uppercase tracking-widest text-zinc-800">Gestion des Clubs</h2>
                        <input v-model="search" type="text" placeholder="Rechercher un club..." class="rounded-xl border-zinc-200 text-sm" />
                    </div>

                    <table class="w-full text-left border-collapse">
                        <thead class="bg-zinc-50 text-[10px] uppercase font-black tracking-widest text-zinc-500">
                        <tr>
                            <th class="p-4">Nom</th>
                            <th class="p-4">Ville</th>
                            <th class="p-4">Membres</th>
                            <th class="p-4">Statut</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                        <tr v-for="club in clubs.data" :key="club.id" class="hover:bg-zinc-50 transition-colors">
                            <td class="p-4 font-bold text-zinc-900">{{ club.name }}</td>
                            <td class="p-4 text-sm text-zinc-600">{{ club.address?.city || 'N/A' }}</td>
                            <td class="p-4 text-sm">{{ club.users_count }}</td>
                            <td class="p-4">
                                <span v-if="club.deleted_at" class="px-2 py-1 bg-red-100 text-red-700 text-[9px] font-bold rounded-md uppercase">Archivé</span>
                                <span v-else class="px-2 py-1 bg-green-100 text-green-700 text-[9px] font-bold rounded-md uppercase">Actif</span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <button v-if="club.deleted_at" @click="restoreClub(club.id)" class="text-indigo-600 hover:underline font-bold text-xs">Restaurer</button>
                                <Link v-else :href="route('clubs.show', club.slug)" class="text-zinc-400 hover:text-indigo-600 transition-colors inline-block"><Eye class="w-4 h-4" /></Link>
                                <button @click="confirmForceDelete(club.id)" class="text-red-400 hover:text-red-600 transition-colors inline-block"><Trash2 class="w-4 h-4" /></button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

