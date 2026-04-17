<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';
import PictureUploader from '@/Components/upload/PictureUploader.vue';
import { Button } from "@/Components/ui/button";
import axios from "axios";

interface Category {
    id: number;
    name: string;
}

interface ClubUserRole {
    id: number;
    club_id: number;
    user_id: number;
    role_id: number; // 1: Admin, 2: Responsable, 3: Membre
    user?: { id: number; username: string };
}

const propsData = defineProps<{
    clubs: Array<{ id: number; name: string }>;
    club_users: ClubUserRole[];
    categories: Category[];
    userId: number;
    isAdmin: boolean; // Super Admin Global
}>();

const form = useForm({
    name: '',
    description: '',
    club_id: null as number | null,
    club_user_id: null as number | null,
    image: null as File | null,
    categories: [] as number[],
});

// --- Logique de filtrage ---

// 1. Liste des clubs accessibles
const availableClubs = computed(() => {
    if (propsData.isAdmin) return propsData.clubs;
    // Pour un membre, on ne montre que les clubs où il possède une ligne dans club_users
    const userClubIds = propsData.club_users
        .filter(cu => cu.user_id === propsData.userId)
        .map(cu => cu.club_id);
    return propsData.clubs.filter(c => userClubIds.includes(c.id));
});

// 2. Vérifier si l'utilisateur est responsable/admin du club sélectionné
const canSelectMember = computed(() => {
    if (propsData.isAdmin) return true;
    if (!form.club_id) return false;

    // On cherche si l'utilisateur connecté est "Responsable" (id:2) dans ce club précis
    return propsData.club_users.some(cu =>
        cu.club_id === form.club_id &&
        cu.user_id === propsData.userId &&
        cu.role_id <= 2 // Admin (1) ou Responsable (2)
    );
});

// 3. Liste des membres du club sélectionné
const filteredMembers = computed(() => {
    if (!form.club_id) return [];
    return propsData.club_users.filter(cu => cu.club_id === form.club_id);
});

// 4. Watcher : Si le club change, on réinitialise ou on auto-assigne le membre
watch(() => form.club_id, (newClubId) => {
    form.club_user_id = null;

    if (newClubId && !canSelectMember.value) {
        // Auto-assignation pour un simple membre
        const myRoleInClub = propsData.club_users.find(
            cu => cu.club_id === newClubId && cu.user_id === propsData.userId
        );
        if (myRoleInClub) form.club_user_id = myRoleInClub.id;
    }
});

// --- Actions ---
const image = ref<File | null>(null);
function handleFile(file: File) { image.value = file; }

function submit() {
    if (image.value) form.image = image.value;
    // @ts-ignore
    form.post(route('collections.storeCollec'), { forceFormData: true });
}

const newCategoryName = ref('');
const isCreatingCategory = ref(false);

async function addCategory() {
    if (!newCategoryName.value.trim()) return;

    isCreatingCategory.value = true;
    try {
        // On envoie la nouvelle catégorie au backend
        const response = await axios.post(route('categories.storeQuick'), {
            name: newCategoryName.value
        });

        // On ajoute la nouvelle catégorie à la liste locale pour qu'elle s'affiche direct
        propsData.categories.push(response.data.category);

        // On la coche automatiquement dans le formulaire
        form.categories.push(response.data.category.id);

        // Reset du champ
        newCategoryName.value = '';
    } catch (error) {
        console.error("Erreur lors de la création de la catégorie", error);
        alert("Impossible de créer la catégorie. Elle existe peut-être déjà ?");
    } finally {
        isCreatingCategory.value = false;
    }
}

const breadcrumbs = [
    // @ts-ignore
    { title: 'Accueil', href: route('home') },
    // @ts-ignore
    { title: 'Collections', href: route('collections.listeCollec') },
    { title: 'Créer', href: '#' },
];
</script>

<template>
    <Head title="Créer une collection" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-6">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-black text-gray-900 dark:text-white">Nouvelle Collection</h1>
                <Link :href="route('collections.listeCollec')">
                    <Button variant="outline" class="rounded-xl">Retour</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700">

                <div class="space-y-2">
                    <label class="text-sm font-black uppercase tracking-wider text-gray-400">Image de couverture</label>
                    <PictureUploader @file-selected="handleFile" />
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-black uppercase tracking-wider text-gray-400">Nom</label>
                        <input v-model="form.name" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500" required />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-black uppercase tracking-wider text-gray-400">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900" required></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-black uppercase tracking-wider text-gray-400">Club</label>
                        <select v-model="form.club_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900" required>
                            <option :value="null" disabled>Choisir un club...</option>
                            <option v-for="club in availableClubs" :key="club.id" :value="club.id">
                                {{ club.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black uppercase tracking-wider text-gray-400">Propriétaire</label>

                        <div v-if="canSelectMember">
                            <select v-model="form.club_user_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900" :disabled="!form.club_id" required>
                                <option :value="null" disabled>Attribuer à...</option>
                                <option v-for="cu in filteredMembers" :key="cu.id" :value="cu.id">
                                    {{ cu.user?.username }}
                                </option>
                            </select>
                        </div>
                        <div v-else class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border text-sm text-gray-500">
                            {{ form.club_id ? 'Vous serez le propriétaire.' : 'Sélectionnez un club d\'abord.' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <label class="text-sm font-black uppercase tracking-wider text-gray-400">Catégories</label>

                        <div class="flex items-center gap-2">
                            <input
                                v-model="newCategoryName"
                                type="text"
                                placeholder="Nouvelle catégorie..."
                                class="text-xs rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 py-1.5"
                                @keyup.enter="addCategory"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="rounded-lg h-8 text-[10px] font-bold uppercase"
                                @click="addCategory"
                                :disabled="isCreatingCategory || !newCategoryName"
                            >
                                {{ isCreatingCategory ? '...' : 'Ajouter' }}
                            </Button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto shadow-inner">
                        <div v-for="category in categories" :key="category.id" class="flex items-center space-x-2 p-1">
                            <input
                                type="checkbox"
                                :id="`cat-${category.id}`"
                                :value="category.id"
                                v-model="form.categories"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label :for="`cat-${category.id}`" class="text-xs font-medium dark:text-gray-300 cursor-pointer select-none">
                                {{ category.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <Button type="submit" :disabled="form.processing || !form.club_user_id" class="w-full py-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-lg transition-all shadow-lg shadow-indigo-100 dark:shadow-none">
                    {{ form.processing ? 'Création en cours...' : 'Créer la collection' }}
                </Button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
