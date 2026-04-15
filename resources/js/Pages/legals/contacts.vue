<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});


function submit() {

}
</script>

<template>
    <Head title="Contact">
        <link rel="preconnect" href="https://rsms.me" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">

                    <h1 class="text-3xl font-bold text-gray-800 mb-6">Contactez-nous</h1>
                    <p class="text-gray-600 mb-8">
                        Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.
                    </p>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Nom</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm shadow-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-gray-700"
                                required
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm shadow-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-gray-700"
                                required
                            />
                            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label for="subject" class="block font-medium text-sm text-gray-700">Sujet</label>
                            <input
                                id="subject"
                                v-model="form.subject"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm shadow-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-gray-700"
                            />
                        </div>

                        <div>
                            <label for="message" class="block font-medium text-sm text-gray-700">Message</label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm shadow-gray-700 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-gray-700"
                                required
                            ></textarea>
                            <div v-if="form.errors.message" class="text-red-500 text-sm mt-1">{{ form.errors.message }}</div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-300 disabled:opacity-50 transition"
                            >
                                Envoyer le message
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
