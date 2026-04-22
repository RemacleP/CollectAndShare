<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    club: Object
});

const form = useForm({
    _method: 'put', // Important pour l'envoi de fichiers en mode edit
    name: props.club.name,
    description: props.club.description || '',
    email: props.club.email || '',
    phone: props.club.phone || '',
    logo: null,
    // Adresse
    street: props.club.address?.street || '',
    number: props.club.address?.number || '',
    box: props.club.address?.box || '',
    postal_code: props.club.address?.postal_code || '',
    city: props.club.address?.city || '',
    country: props.club.address?.country || 'Belgique',
});

const submit = () => {
    // On envoie en POST car il y a un fichier, le _method: 'put' fera le reste côté Laravel
    form.post(route('clubs.update', props.club.slug), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Modifier ${club.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Modifier le club : {{ club.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white p-8 shadow sm:rounded-lg space-y-8">

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Informations Générales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="name" value="Nom du club" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="logo" value="Logo (laisser vide pour ne pas changer)" />
                                <input id="logo" type="file" @input="form.logo = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500" />
                                <InputError :message="form.errors.logo" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <InputLabel for="description" value="Description" />
                                <textarea id="description" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" v-model="form.description"></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Contact</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="email" value="Email du club" />
                                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="phone" value="Téléphone" />
                                <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" />
                                <InputError :message="form.errors.phone" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Adresse</h3>
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                            <div class="md:col-span-4">
                                <InputLabel for="street" value="Rue" />
                                <TextInput id="street" type="text" class="mt-1 block w-full" v-model="form.street" required />
                            </div>
                            <div class="md:col-span-1">
                                <InputLabel for="number" value="N°" />
                                <TextInput id="number" type="text" class="mt-1 block w-full" v-model="form.number" required />
                            </div>
                            <div class="md:col-span-1">
                                <InputLabel for="box" value="Boîte" />
                                <TextInput id="box" type="text" class="mt-1 block w-full" v-model="form.box" />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="postal_code" value="Code Postal" />
                                <TextInput id="postal_code" type="text" class="mt-1 block w-full" v-model="form.postal_code" required />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="city" value="Ville" />
                                <TextInput id="city" type="text" class="mt-1 block w-full" v-model="form.city" required />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="country" value="Pays" />
                                <TextInput id="country" type="text" class="mt-1 block w-full" v-model="form.country" required />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Enregistrer les modifications
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
