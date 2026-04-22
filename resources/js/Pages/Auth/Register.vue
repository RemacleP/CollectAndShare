<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { User, Building2, Loader2, Info, AtSign, PlusCircle, MapPin, Notebook } from 'lucide-vue-next';
import axios from 'axios';

const clubs = ref([]);
const isLoaded = ref(false);

const form = useForm({
    firstname: '',
    lastname: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    registration_type: 'user', // Déclencheur principal
    club_id: '',
    // Champs spécifiques à la création de club (Mode Responsable)
    new_club_name: '',
    club_description: '',
    street: '',
    number: '',
    postal_code: '',
    city: '',
    message: '',
    mentions_legales: false,
});

onMounted(async () => {
    try {
        const response = await axios.get('/clubs/api/all');
        clubs.value = response.data.clubs || [];
        isLoaded.value = true;
    } catch (e) {
        console.error("Erreur:", e);
        isLoaded.value = true;
    }
});

// Filtrer les clubs pour les utilisateurs (uniquement ceux qui ont un manager)
const clubOptions = computed(() => {
    return clubs.value.filter(c => c.has_manager);
});

// Surveillance du déclencheur pour nettoyer les données
watch(() => form.registration_type, (newType) => {
    form.club_id = '';
    form.new_club_name = '';
    form.street = '';
    form.city = '';
    // En mode responsable, on force l'ID à 'new' car il propose son club
    if (newType === 'club_manager') {
        form.club_id = 'new';
    }
});

const submit = () => {
    form.post(route('register.request'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Demande d'inscription" />

        <div class="pt-8 text-center flex flex-col items-center">
            <h1 class="text-2xl font-black uppercase tracking-tighter text-zinc-900 dark:text-white">Collect & Share</h1>
            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mt-2">Formulaire de demande d'accès</p>
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-6 pb-12">

            <div class="grid grid-cols-2 gap-4">
                <button type="button" @click="form.registration_type = 'user'"
                        :class="form.registration_type === 'user' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 ring-4 ring-indigo-50' : 'border-zinc-200 dark:border-zinc-800 opacity-60'"
                        class="flex flex-col items-center gap-2 p-4 rounded-3xl border-2 transition-all duration-300">
                    <User :class="form.registration_type === 'user' ? 'text-indigo-600' : 'text-zinc-400'" />
                    <span class="text-[10px] font-black uppercase tracking-widest">Je suis un Membre</span>
                </button>

                <button type="button" @click="form.registration_type = 'club_manager'"
                        :class="form.registration_type === 'club_manager' ? 'border-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 ring-4 ring-emerald-50' : 'border-zinc-200 dark:border-zinc-800 opacity-60'"
                        class="flex flex-col items-center gap-2 p-4 rounded-3xl border-2 transition-all duration-300">
                    <Building2 :class="form.registration_type === 'club_manager' ? 'text-emerald-600' : 'text-zinc-400'" />
                    <span class="text-[10px] font-black uppercase tracking-widest">Je suis Responsable</span>
                </button>
            </div>

            <div class="space-y-4">
                <h3 class="text-[10px] font-black uppercase text-zinc-400 border-b pb-2">Informations personnelles</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Prénom</label>
                        <TextInput v-model="form.firstname" type="text" class="block w-full !rounded-2xl" required />
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Nom</label>
                        <TextInput v-model="form.lastname" type="text" class="block w-full !rounded-2xl" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Pseudo</label>
                        <div class="relative">
                            <AtSign class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                            <TextInput v-model="form.username" type="text" class="block w-full !rounded-2xl pl-11" required />
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Email</label>
                        <TextInput v-model="form.email" type="email" class="block w-full !rounded-2xl" required />
                    </div>
                </div>
            </div>

            <div v-if="form.registration_type === 'user'" class="space-y-4 animate-in fade-in slide-in-from-left-4">
                <h3 class="text-[10px] font-black uppercase text-indigo-600 border-b border-indigo-100 pb-2">Quel club souhaitez-vous rejoindre ?</h3>
                <div>
                    <select v-model="form.club_id" class="w-full rounded-2xl border-zinc-200 dark:bg-zinc-950 h-12 text-sm font-bold shadow-sm focus:ring-indigo-600">
                        <option value="">-- Sélectionner un club existant --</option>
                        <option v-for="club in clubOptions" :key="club.id" :value="club.id">{{ club.name }}</option>
                    </select>
                    <InputError :message="form.errors.club_id" class="mt-2" />
                </div>
            </div>

            <div v-if="form.registration_type === 'club_manager'" class="p-6 bg-emerald-50/30 dark:bg-emerald-900/10 rounded-[2rem] border-2 border-emerald-100 dark:border-emerald-900/30 space-y-4 animate-in fade-in slide-in-from-right-4">
                <div class="flex items-center gap-2 mb-2">
                    <PlusCircle class="w-5 h-5 text-emerald-600" />
                    <h3 class="text-xs font-black uppercase tracking-tighter text-emerald-700">Proposer un nouveau club</h3>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-emerald-600 ml-1">Nom du club</label>
                    <TextInput v-model="form.new_club_name" type="text" class="block w-full !rounded-xl border-emerald-200 shadow-none" placeholder="Ex: ASBL Sport et Passion" required />
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-emerald-600 ml-1">Description / Activités</label>
                    <textarea v-model="form.club_description" class="w-full rounded-xl border-emerald-200 dark:bg-zinc-950 text-sm" rows="2"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <label class="text-[10px] font-black uppercase text-emerald-600 ml-1">Rue</label>
                        <TextInput v-model="form.street" type="text" class="block w-full !rounded-xl border-emerald-200" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-emerald-600 ml-1">N°</label>
                        <TextInput v-model="form.number" type="text" class="block w-full !rounded-xl border-emerald-200" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] font-black uppercase text-emerald-600 ml-1">Code Postal</label>
                        <TextInput v-model="form.postal_code" type="text" class="block w-full !rounded-xl border-emerald-200" />
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-emerald-600 ml-1">Ville</label>
                        <TextInput v-model="form.city" type="text" class="block w-full !rounded-xl border-emerald-200" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Mot de passe</label>
                    <TextInput v-model="form.password" type="password" class="block w-full !rounded-2xl" required />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-zinc-400 ml-1">Confirmation</label>
                    <TextInput v-model="form.password_confirmation" type="password" class="block w-full !rounded-2xl" required />
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-zinc-50 dark:bg-zinc-900/40 p-4 rounded-2xl flex gap-3 border border-zinc-100">
                    <Notebook class="w-4 h-4 text-zinc-400 shrink-0 mt-0.5" />
                    <p class="text-[10px] font-bold text-zinc-500 leading-tight uppercase tracking-tight">
                        {{ form.registration_type === 'user' ? 'Votre demande sera validée par le responsable du club choisi.' : 'Votre demande de création sera validée par un administrateur système.' }}
                    </p>
                </div>

                <label class="flex items-center gap-3 cursor-pointer group">
                    <Checkbox v-model:checked="form.mentions_legales" required />
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500">J'accepte les mentions légales</span>
                </label>

                <button type="submit" :disabled="form.processing || !isLoaded"
                        class="w-full flex items-center justify-center gap-2 py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-950 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl hover:bg-indigo-600 transition-all active:scale-95 disabled:opacity-50">
                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Soumettre ma demande
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
