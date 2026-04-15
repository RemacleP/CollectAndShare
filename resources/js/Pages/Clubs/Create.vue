<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    // Infos Club
    name: '',
    description: '',
    email: '',
    phone: '',
    logo: null,
    // Infos Adresse (Table indépendante)
    street: '',
    number: '',
    box: '',
    postal_code: '',
    city: '',
    country: 'Belgique',
});

const submit = () => {
    // On utilise post pour l'envoi du formulaire (et du fichier logo)
    form.post(route('clubs.store'), {
        forceFormData: true, // Nécessaire pour l'upload d'image
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Créer un Club" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouveau Club</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white p-8 shadow-sm rounded-xl space-y-8">

                    <div class="border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900">Identité du club</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="md:col-span-2">
                                <InputLabel for="name" value="Nom du club *" />
                                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="email" value="Email de contact" />
                                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel for="phone" value="Téléphone" />
                                <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                            </div>
                        </div>
                    </div>

                    <div class="border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900">Localisation</h3>
                        <div class="grid grid-cols-6 gap-4 mt-4">
                            <div class="col-span-4">
                                <InputLabel for="street" value="Rue *" />
                                <TextInput id="street" v-model="form.street" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div class="col-span-1">
                                <InputLabel for="number" value="N° *" />
                                <TextInput id="number" v-model="form.number" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div class="col-span-1">
                                <InputLabel for="box" value="Bte" />
                                <TextInput id="box" v-model="form.box" type="text" class="mt-1 block w-full" />
                            </div>
                            <div class="col-span-2">
                                <InputLabel for="postal_code" value="Code Postal *" />
                                <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div class="col-span-4">
                                <InputLabel for="city" value="Ville *" />
                                <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" required />
                            </div>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="logo" value="Logo du club" />
                        <input type="file" @input="form.logo = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <progress v-if="form.progress" :value="form.progress.percentage" max="100">{{ form.progress.percentage }}%</progress>
                    </div>

                    <div class="flex justify-end gap-4">
                        <PrimaryButton :disabled="form.processing">Créer le club</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
