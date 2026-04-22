<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';
import { Button } from "@/components/ui/button";
import PictureUploader from '@/components/upload/PictureUploader.vue';
import { ArrowLeft, Save, LayoutGrid, Info, Tag, Users } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    collect: {
        id: number;
        name: string;
        description: string;
        club_id: number | null;
        club_user_id: number | null;
        slug: string;
        image: string | null;
        categories: Category[];
    };
    clubs: Array<{ id: number; name: string }>;
    club_users: Array<{ id: number; user?: {
        username: string; id: number;
        } }>;
    categories: Category[];
    isUser: boolean;
    isClubManager: boolean;
}>();

const form = useForm({
    // On ajoute explicitement _method pour le spoofing Laravel
    _method: 'patch',
    name: props.collect.name,
    description: props.collect.description,
    club_id: props.collect.club_id,
    club_user_id: props.collect.club_user_id,
    image: null as File | null,
    delete_image: false,
    categories: props.collect.categories?.map((c) => c.id) || [],
});

const handleFile = (file: File) => {
    form.image = file;
    form.delete_image = false;
};

const submit = () => {
    // Utilise simplement props.collect.slug directement
    form.post(route('collections.updateCollec', props.collect.slug), {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => console.log("Envoi en cours..."),
        onFinish: () => console.log("Terminé."),
        onError: (e) => console.error("Erreurs :", e)
    });
};

const breadcrumbs = computed(() => [
    { title: 'Accueil', href: route('home') },
    { title: 'Collections', href: route('collections.listeCollec') },
    { title: `Modifier : ${props.collect.name}` },
]);
</script>

<template>
    <Head :title="`Modifier - ${form.name}`" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto p-6">

            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Configuration</h1>
                    <p class="text-sm text-gray-500 font-medium">Mise à jour de votre collection</p>
                </div>
                <Link :href="route('collections.listeCollec')">
                    <Button variant="outline" class="rounded-xl hover:bg-gray-50 transition-colors">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Retour
                    </Button>
                </Link>
            </div>

            <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-sm font-bold">
                Certains champs comportent des erreurs. Veuillez vérifier le formulaire.
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-zinc-900 p-6 rounded-[2.5rem] border border-gray-100 dark:border-zinc-800 shadow-sm">
                        <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-6">
                            <LayoutGrid class="h-3 w-3" /> Image de couverture
                        </label>

                        <PictureUploader
                            @file-selected="handleFile"
                            :existing-image="props.collect.image ? `/storage/${props.collect.image}` : null"
                        />

                        <p class="mt-4 text-[10px] text-center text-gray-400 leading-relaxed italic">
                            Format recommandé : Carré (1:1). <br> Taille max : 2MB.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">

                    <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-gray-100 dark:border-zinc-800 shadow-sm space-y-6">
                        <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600 mb-2">
                            <Info class="h-3 w-3" /> Informations générales
                        </label>

                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-400 ml-2 mb-1 block uppercase">Titre de la collection</label>
                                <input
                                    v-model="form.name"
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-4 focus:ring-2 focus:ring-indigo-500 outline-none transition font-semibold"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1 ml-2 font-bold">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-400 ml-2 mb-1 block uppercase">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 dark:bg-zinc-800 dark:border-zinc-700 p-4 focus:ring-2 focus:ring-indigo-500 outline-none transition leading-relaxed"
                                    required
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-500 text-xs mt-1 ml-2 font-bold">{{ form.errors.description }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-50 dark:border-zinc-800">
                            <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-2xl">
                                <Users class="h-5 w-5 text-gray-400" />
                                <div>
                                    <span class="block text-[10px] font-black text-gray-400 uppercase">Club référent</span>
                                    <span class="text-sm font-bold">{{ clubs.find(c => c.id === form.club_id)?.name || 'Indépendant' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-2xl">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-black text-indigo-600 uppercase">
                                    {{ club_users.find(cu => cu.id === form.club_user_id)?.user?.username?.charAt(0) || 'U' }}
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black text-gray-400 uppercase">Propriétaire</span>
                                    <span class="text-sm font-bold">{{ club_users.find(cu => cu.id === form.club_user_id)?.user?.username || 'Inconnu' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-gray-100 dark:border-zinc-800 shadow-sm">
                        <label class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-6">
                            <Tag class="h-3 w-3" /> Catégories & Thématiques
                        </label>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div
                                v-for="category in categories"
                                :key="category.id"
                                class="relative"
                            >
                                <input
                                    type="checkbox"
                                    :id="`cat-${category.id}`"
                                    :value="category.id"
                                    v-model="form.categories"
                                    class="peer hidden"
                                />
                                <label
                                    :for="`cat-${category.id}`"
                                    class="flex h-full cursor-pointer items-center justify-center rounded-xl border-2 border-gray-50 bg-gray-50 px-3 py-3 text-center text-xs font-bold text-gray-500 transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 dark:bg-zinc-800 dark:border-zinc-800 dark:peer-checked:bg-indigo-900/20"
                                >
                                    {{ category.name }}
                                </label>
                            </div>
                        </div>
                        <div v-if="form.errors.categories" class="text-red-500 text-xs mt-2 ml-2 font-bold">{{ form.errors.categories }}</div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4">
                        <Link :href="route('collections.listeCollec')" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition">
                            Abandonner
                        </Link>
                        <Button
                            type="button"
                            @click="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-orange-600 px-10 py-7 font-black text-white shadow-xl shadow-orange-100 hover:bg-orange-700 dark:shadow-none transition-all active:scale-95"
                        >
                            <Save class="mr-2 h-5 w-5" />
                            {{ form.processing ? 'Enregistrement...' : 'Mettre à jour' }}
                        </Button>
                    </div>

                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
