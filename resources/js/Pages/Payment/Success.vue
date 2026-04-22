<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
const page = usePage();

// On récupère le session_id présent dans l'URL (Stripe le rajoute via success_url)
const urlParams = new URLSearchParams(window.location.search);
const sessionId = urlParams.get('session_id');
</script>

<template>
    <Head title="Paiement réussi" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <CheckCircle class="w-20 h-20 text-green-500 mx-auto mb-4" />
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Merci pour votre achat !</h1>
                    <p class="text-gray-600 mb-8">
                        Votre paiement a été validé avec succès. Vous allez recevoir un e-mail de confirmation d'ici quelques instants.
                    </p>
                    <div class="flex justify-center gap-4">
                        <Link :href="route('dashboard')" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                            Retour à l'accueil
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center gap-4">
            <Link :href="route('dashboard')" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-300">
                Retour à l'accueil
            </Link>

            <a :href="route('invoice.download', { session_id: sessionId })"
               target="_blank"
               class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                Télécharger ma facture (PDF)
            </a>
        </div>
    </AuthenticatedLayout>
</template>
