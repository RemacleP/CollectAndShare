<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'; // Ajout de usePage pour la sécurité
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted } from 'vue';
import PictureUploader from '@/components/upload/PictureUploader.vue';
import { Button } from "@/components/ui/button";

interface Category {
    id: number;
    name: string;
}

interface ClubUserRole {
    id: number;
    club_id: number;
    user_id: number;
    user?: { id: number; username: string };
}

const propsData = defineProps<{
    clubs: Array<{ id: number; name: string }>;
    club_users: ClubUserRole[];
    categories: Category[];
    userClub: { id: number; name: string } | null;
    userId: number;
    isUser: boolean;
    isClubManager: boolean;
}>();

const form = useForm({
    name: '',
    description: '',
    // On lie la collection au club actuel
    club_id: propsData.userClub?.id ?? null,
    // C'est cet ID (pivot) qui est crucial pour le lien Membre-Club
    club_user_id: null as number | null,
    image: null as File | null,
    categories: [] as number[],
});

const image = ref<File | null>(null);
function handleFile(file: File) {
    image.value = file;
}

// Trouver la ligne pivot de l'utilisateur connecté pour ce club précis
const currentUserRole = computed(() => {
    return propsData.club_users.find(
        cu => cu.user_id === propsData.userId && cu.club_id === propsData.userClub?.id
    ) ?? null;
});

// Initialisation automatique au montage du composant
onMounted(() => {
    if (currentUserRole.value && !propsData.isClubManager) {
        form.club_user_id = currentUserRole.value.id;
    }
});

function submit() {
    if (image.value) {
        form.image = image.value;
    }

    // @ts-ignore
    form.post(route('collections.storeCollec'), {
        forceFormData: true,
        onSuccess: () => {
            // Optionnel : redirection ou message
        }
    });
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
                    <div class="flex justify-center p-4 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-2xl">
                        <PictureUploader @file-selected="handleFile" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-black uppercase tracking-wider text-gray-400">Nom</label>
                        <input v-model="form.name" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900" required />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-black uppercase tracking-wider text-gray-400">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900" required></textarea>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black uppercase tracking-wider text-gray-400">Club de rattachement</label>
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                            {{ userClub?.name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-bold text-indigo-900 dark:text-indigo-200">{{ userClub?.name }}</p>
                            <p class="text-xs text-indigo-600">La collection sera liée à ce club</p>
                        </div>
                    </div>
                </div>

                <div v-if="isClubManager" class="space-y-2">
                    <label class="text-sm font-black uppercase tracking-wider text-gray-400">Propriétaire de la collection</label>
                    <select v-model="form.club_user_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900" required>
                        <option :value="null" disabled>Sélectionner le membre responsable...</option>
                        <option v-for="cu in club_users" :key="cu.id" :value="cu.id">
                            {{ cu.user?.username }}
                        </option>
                    </select>
                </div>
                <div v-else class="space-y-2">
                    <label class="text-sm font-black uppercase tracking-wider text-gray-400">Propriétaire</label>
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border text-gray-500 text-sm">
                        En tant que membre, vous serez le propriétaire de cette collection.
                    </div>
                    <input type="hidden" v-model="form.club_user_id" />
                </div>

                <div class="space-y-3">
                    <label class="text-sm font-black uppercase tracking-wider text-gray-400">Catégories de la collection</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                        <div v-for="category in categories" :key="category.id" class="flex items-center space-x-2">
                            <input type="checkbox" :id="`cat-${category.id}`" :value="category.id" v-model="form.categories" class="rounded border-gray-300 text-indigo-600" />
                            <label :for="`cat-${category.id}`" class="text-sm font-medium dark:text-gray-300 cursor-pointer">{{ category.name }}</label>
                        </div>
                    </div>
                </div>

                <Button type="submit" :disabled="form.processing" class="w-full py-6 rounded-xl bg-indigo-600 text-white font-black">
                    {{ form.processing ? 'Chargement...' : 'Créer la collection' }}
                </Button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
