<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps<{
    legal: any;
}>()

const page = usePage();

// On récupère l'info admin depuis la prop globale auth (configurée dans le middleware)
const isAdmin = computed(() => !!page.props.auth.user?.is_admin);

const editMode = ref(false);

// Initialisation du formulaire avec les données reçues
const form = useForm({
    legal: JSON.parse(JSON.stringify(props.legal))
});

function submit() {
    form.put(route('legals.update'), { // Utilise PUT ou POST selon ton web.php
        onSuccess: () => {
            editMode.value = false;
        }
    });
}

function addItem(list: string[]) {
    list.push('');
}

function removeItem(list: string[], index: number) {
    list.splice(index, 1);
}

// Pour annuler, on réinitialise simplement le formulaire avec les props d'origine
function cancelEdit() {
    form.legal = JSON.parse(JSON.stringify(props.legal));
    editMode.value = false;
}
const breadcrumbs = [
    { name: 'Administration', href: '#' },
    { name: 'Mentions Légales', href: route('legals.mentionsLegales') },
];

onMounted(() => {
    // On vérifie si "?edit=true" est présent dans l'adresse URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('edit') === 'true' && isAdmin.value) {
        editMode.value = true;
    }
});
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-6 space-y-8">

            <!-- BOUTON MODIFIER -->
            <div v-if="!editMode && isAdmin" class="text-right">
                <button
                    @click="editMode = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Modifier
                </button>
            </div>

            <!-- ================= MODE LECTURE ================= -->
            <div v-if="!editMode">

                <!---Partie Mentions Légales--->
                <h1 class="text-5xl text-blue-700 font-bold mb-4">
                    {{ legal.mentions_legales.titre }}
                </h1>

                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Nom :</strong> {{ legal.mentions_legales.informations_association.nom_association }}</li>
                    <li><strong>Statut :</strong> {{ legal.mentions_legales.informations_association.statut_juridique }}</li>
                    <li><strong>Adresse :</strong> {{ legal.mentions_legales.informations_association.adresse }}</li>
                    <li><strong>Num entreprise : </strong>{{legal.mentions_legales.informations_association.numero_entreprise}}</li>
                    <li><strong>Téléphone :</strong> {{ legal.mentions_legales.informations_association.telephone }}</li>
                    <li><strong>Email :</strong> {{ legal.mentions_legales.informations_association.email }}</li>
                    <li><strong>Site Web :</strong> {{ legal.mentions_legales.informations_association.site_web }}</li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1 "><strong>Directeur de la publication :</strong></h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Contact : </strong> {{legal.mentions_legales.directeur_publication.contact}}</li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1 "><strong>Hébergeur du site :</strong></h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Nom : </strong>{{legal.mentions_legales.hebergeur.nom}}</li>
                    <li><strong>Adresse de l'hébergeur : </strong>{{legal.mentions_legales.hebergeur.adresse}}</li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1 ">Propriété intellectuelle :</h2>
                <p class="mt-2">{{ legal.mentions_legales.propriete_intellectuelle }}</p>

                <h2 class="text-2xl font-bold mb-1 py-1 "><strong>Limitation de responsabilité : </strong></h2>
                <p class="mt-2">{{ legal.mentions_legales.limitation_responsabilite }}</p>

                <h2 class="text-2xl font-bold mb-1 py-1 "><strong>Utilisation de l'intelligence artificielle (IA Act) : </strong></h2>
                <p class="mt-2">{{ legal.mentions_legales.ia_act.description }}</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.mentions_legales.ia_act.points" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <!---Partie Protection des données personnelles--->
                <h1 class="text-5xl text-blue-700 font-bold mb-4">
                    {{ legal.protection_donnees.titre }}
                </h1>
                <p class="mt-2">{{ legal.protection_donnees.introduction }}</p>
                <h2 class="text-2xl font-bold mb-1 py-1">Collecte des données :</h2>

                <h3><strong>Lors de l’inscription : </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.protection_donnees.collecte_donnees.inscription" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h3><strong>Lors de l’utilisation de la plateforme :  </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.protection_donnees.collecte_donnees.utilisation_plateforme" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1"><strong>Finalités de traitement :</strong></h2>
                <h3><strong>Les données sont utilisées pour : </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.protection_donnees.finalites" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1"><strong>Base légale du traitement :</strong></h2>
                <h3><strong>Les traitements reposent sur : </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.protection_donnees.base_legale" :key="index">
                        {{ point }}
                    </li>

                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1"><strong>Durée de conservation des données :</strong></h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Compte utilisateur : </strong> {{legal.protection_donnees.duree_conservation.compte_utilisateur}}</li>
                    <li><strong>Données de connexion : </strong> {{legal.protection_donnees.duree_conservation.donnees_connexion}}</li>
                    <li><strong>Informations nécessaires aux obligations légales : </strong>{{legal.protection_donnees.duree_conservation.obligations_legales}}</li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1"><strong>Partage des données : </strong></h2>
                <h3><strong>Les données ne sont jamais revendues.</strong></h3>
                <h3><strong>Elles peuvent être partagées avec :</strong></h3>

                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.protection_donnees.partage_donnees.partenaires" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1"><strong>Droits des utilisateurs : </strong></h2>
                <h3><strong>Chaque utilisateur peut à tout moment :</strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.protection_donnees.droits_utilisateurs" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1"><strong>Sécurité des données : </strong></h2>
                <h3><strong>La plateforme est protégée contre :</strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.protection_donnees.securite" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <!---Partie Cookies--->
                <h1 class="text-5xl text-blue-700 font-bold mb-4">
                    {{ legal.cookies.titre }}
                </h1>
                <p>{{legal.cookies.introduction}}</p>
                <h2 class="text-2xl font-bold mb-1 py-1"><strong> Cookies utilisés : </strong></h2>

                <h3><strong>Cookies nécessaires au fonctionnement :</strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.cookies.types.necessaires" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h3><strong>Cookies de mesure d’audience : </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.cookies.types.mesure_audience" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h3><strong>Cookies de réseaux sociaux : </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.cookies.types.reseaux_sociaux" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h3><strong>Cookies tiers : </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.cookies.types.tiers" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1"><strong>Consentement : </strong></h2>
                <h3><strong>À la première visite, une bannière demande : </strong></h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li v-for="(point, index) in legal.cookies.consentement.options" :key="index">
                        {{ point }}
                    </li>
                </ul>

                <h2 class="text-2xl font-bold mb-1 py-1" ><strong>Durée de vie des cookies</strong></h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Cookies essentiels : </strong>{{legal.cookies.duree_vie.essentiels}}</li>
                    <li><strong>Cookies analytiques : </strong>{{legal.cookies.duree_vie.analytiques}}</li>
                </ul>

                <!---Partie Version--->
                <h1 class="text-5xl text-blue-700 font-bold mb-4">
                    {{ legal.version.titre }}
                </h1>
                <p>Dernière mise à jour : <strong>{{legal.version.derniere_mise_a_jour}}</strong></p>
                <p>{{legal.version.note}}</p>
            </div>

            <!-- ================= MODE EDITION ================= -->
            <form v-else @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="text-5xl font-semibold block mb-1">Titre de la page mentions</label>
                    <input
                        v-model="form.legal.mentions_legales.titre"
                        class="w-full border rounded p-2"
                    />
                </div>

                <!--- Partie Mentions légales --->
                <div>
                    <h2 class="font-semibold mb-2">---Informations association---</h2>
                    <label>Nom de l'association :</label>
                    <input
                        v-model="form.legal.mentions_legales.informations_association.nom_association"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Nom"
                    />

                    <label>Statut juridique :</label>
                    <input
                        v-model="form.legal.mentions_legales.informations_association.statut_juridique"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Statut"
                    />
                    <label>Adresse :</label>
                    <input
                        v-model="form.legal.mentions_legales.informations_association.adresse"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Adresse"
                    />

                    <label>Numéro de l'entreprise : </label>
                    <input
                        v-model="form.legal.mentions_legales.informations_association.numero_entreprise"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Numéro d'entreprise"
                    />

                    <label>Téléphone :</label>
                    <input
                        v-model="form.legal.mentions_legales.informations_association.telephone"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Téléphone"
                    />

                    <label>Email :</label>
                    <input
                        v-model="form.legal.mentions_legales.informations_association.email"
                        class="w-full border rounded p-2"
                        placeholder="Email"
                    />

                    <label>Site Web :</label>
                    <input
                        v-model="form.legal.mentions_legales.informations_association.site_web"
                        class="w-full border rounded p-2 mt-2"
                        placeholder="Site Web"
                    />
                </div>

                <div>
                    <h2 class="font-semibold mb-2">---Directeur de la publication---</h2>
                    <input
                        v-model="form.legal.mentions_legales.directeur_publication.contact"
                        class="w-full border rounded p-2"
                        placeholder="Contact"
                    />
                </div>

                <div>
                    <h2 class="font-semibold mb-2">---Hébergeur du site---</h2>
                    <input
                        v-model="form.legal.mentions_legales.hebergeur.nom"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Nom de l'hébergeur"
                    />
                    <input
                        v-model="form.legal.mentions_legales.hebergeur.adresse"
                        class="w-full border rounded p-2"
                        placeholder="Adresse de l'hébergeur"
                    />
                </div>

                <div>
                    <label class="font-semibold block mb-1">---Propriété intellectuelle---</label>
                    <textarea
                        v-model="form.legal.mentions_legales.propriete_intellectuelle"
                        rows="4"
                        class="w-full border rounded p-2"
                    />
                </div>
                <div>
                    <label class="font-semibold block mb-1">---Limitation de responsabilité---</label>
                    <textarea
                        v-model="form.legal.mentions_legales.limitation_responsabilite"
                        rows="4"
                        class="w-full border rounded p-2"
                    />
                </div>

                <h2 class="font-semibold mb-2">---IA Act---</h2>
                <textarea
                    v-model="form.legal.mentions_legales.ia_act.description"
                    class="w-full border rounded p-2"
                />
                <!---Bouton d'ajout/suppression--->
                <div
                    v-for="(point, index) in form.legal.mentions_legales.ia_act.points"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.mentions_legales.ia_act.points[index]"
                        class="input flex-1"
                    />
                    <button
                        type="button"
                        @click="removeItem(form.legal.mentions_legales.ia_act.points, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.mentions_legales.ia_act.points)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <!--- Partie Protection des données personnelles --->
                <div>
                    <label class="text-3xl font-semibold block mb-1">Titre Protection des données</label>
                    <input
                        v-model="form.legal.protection_donnees.titre"
                        class="w-full border rounded p-2 mb-2"
                    />
                    <label class="font-semibold block mb-1">Introduction Protection des données</label>
                    <textarea
                        v-model="form.legal.protection_donnees.introduction"
                        rows="4"
                        class="w-full border rounded p-2"
                    />
                </div>


                <h2 class="font-semibold mb-2">---Collecte des données---</h2>
                <h3 class="font-semibold mb-2">Lors de l’inscription : </h3>
                <div
                    v-for="(point, index) in form.legal.protection_donnees.collecte_donnees.inscription"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.protection_donnees.collecte_donnees.inscription[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.protection_donnees.collecte_donnees.inscription, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.protection_donnees.collecte_donnees.inscription)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h3 class="font-semibold mb-2">Lors de l’utilisation de la plateforme :  </h3>
                <div
                    v-for="(point, index) in form.legal.protection_donnees.collecte_donnees.utilisation_plateforme"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.protection_donnees.collecte_donnees.utilisation_plateforme[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.protection_donnees.collecte_donnees.utilisation_plateforme, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.protection_donnees.collecte_donnees.utilisation_plateforme)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h2 class="font-semibold mb-2">---Finalités de traitement---</h2>
                <h3 class="font-semibold mb-2">Les données sont utilisées pour :</h3>
                <div
                    v-for="(point, index) in form.legal.protection_donnees.finalites"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.protection_donnees.finalites[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.protection_donnees.finalites, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.protection_donnees.finalites)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h2 class="font-semibold mb-2">---Base légale du traitement---</h2>
                <div
                    v-for="(point, index) in form.legal.protection_donnees.base_legale"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.protection_donnees.base_legale[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.protection_donnees.base_legale, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.protection_donnees.base_legale)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h2 class="font-semibold mb-2">---Durée de conservation des données---</h2>
                <label>Compte utilisateur :</label>
                <input
                    v-model="form.legal.protection_donnees.duree_conservation.compte_utilisateur"
                    class="w-full border rounded p-2 mb-2"
                    placeholder="Compte utilisateur"
                />
                <label>Données de connexion :</label>
                <input
                    v-model="form.legal.protection_donnees.duree_conservation.donnees_connexion"
                    class="w-full border rounded p-2 mb-2"
                    placeholder="Données de connexion"
                />
                <label>Informations nécessaires aux obligations légales :</label>
                <input
                    v-model="form.legal.protection_donnees.duree_conservation.obligations_legales"
                    class="w-full border rounded p-2 mb-2"
                    placeholder="Informations nécessaires aux obligations légales"
                />

                <h2 class="font-semibold mb-2">---Partage des données---</h2>
                <div
                    v-for="(point, index) in form.legal.protection_donnees.partage_donnees.partenaires"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.protection_donnees.partage_donnees.partenaires[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.protection_donnees.partage_donnees.partenaires, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.protection_donnees.partage_donnees.partenaires)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h2 class="font-semibold mb-2">---Droits des utilisateurs---</h2>
                <div
                    v-for="(point, index) in form.legal.protection_donnees.droits_utilisateurs"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.protection_donnees.droits_utilisateurs[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.protection_donnees.droits_utilisateurs, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.protection_donnees.droits_utilisateurs)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h2 class="font-semibold mb-2">---Sécurité des données---</h2>
                <div
                    v-for="(point, index) in form.legal.protection_donnees.securite"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.protection_donnees.securite[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.protection_donnees.securite, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.protection_donnees.securite)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <!--- Partie Cookies --->
                <h1 class="text-3xl font-semibold block mb-1">Titre Cookies</h1>
                <input
                    v-model="form.legal.cookies.titre"
                    class="w-full border rounded p-2 mb-2"
                />
                <label class="font-semibold block mb-1">Introduction Cookies</label>
                <textarea
                    v-model="form.legal.cookies.introduction"
                    rows="4"
                    class="w-full border rounded p-2 mb-4"
                />

                <h2 class="font-semibold mb-2">---Types de cookies---</h2>
                <h3 class="font-semibold mb-2">Cookies nécessaires au fonctionnement :</h3>
                <div
                    v-for="(point, index) in form.legal.cookies.types.necessaires"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.cookies.types.necessaires[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.cookies.types.necessaires, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.cookies.types.necessaires)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h3 class="font-semibold mb-2">Cookies de mesure d’audience :</h3>
                <div
                    v-for="(point, index) in form.legal.cookies.types.mesure_audience"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.cookies.types.mesure_audience[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.cookies.types.mesure_audience, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.cookies.types.mesure_audience)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h3 class="font-semibold mb-2">Cookies de réseaux sociaux :</h3>
                <div
                    v-for="(point, index) in form.legal.cookies.types.reseaux_sociaux"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.cookies.types.reseaux_sociaux[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.cookies.types.reseaux_sociaux, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.cookies.types.reseaux_sociaux)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h3 class="font-semibold mb-2">Cookies tiers :</h3>
                <div
                    v-for="(point, index) in form.legal.cookies.types.tiers"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.cookies.types.tiers[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.cookies.types.tiers, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.cookies.types.tiers)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h2 class="font-semibold mb-2">---Consentement---</h2>
                <div
                    v-for="(point, index) in form.legal.cookies.consentement.options"
                    :key="index"
                    class="flex gap-2 items-center mb-2"
                >
                    <input
                        v-model="form.legal.cookies.consentement.options[index]"
                        class="input flex-1"
                    />
                    <!---Bouton d'ajout/suppression--->
                    <button
                        type="button"
                        @click="removeItem(form.legal.cookies.consentement.options, index)"
                        class="text-red-600 font-bold"
                    >
                        ✕
                    </button>
                </div>
                <button
                    type="button"
                    @click="addItem(form.legal.cookies.consentement.options)"
                    class="text-blue-600"
                >
                    + Ajouter un point
                </button>

                <h2 class="font-semibold mb-2">---Durée de vie des cookies---</h2>
                <label>Cookies essentiels :</label>
                <input
                    v-model="form.legal.cookies.duree_vie.essentiels"
                    class="w-full border rounded p-2 mb-2"
                    placeholder="Cookies essentiels"
                />
                <label>Cookies analytiques :</label>
                <input
                    v-model="form.legal.cookies.duree_vie.analytiques"
                    class="w-full border rounded p-2 mb-2"
                    placeholder="Cookies analytiques"
                />

                <!--- Partie Version --->
                <div>
                    <label class="text-3xl font-semibold block mb-1">Titre Version</label>
                    <input
                        v-model="form.legal.version.titre"
                        class="w-full border rounded p-2 mb-2"
                    />
                    <label class="font-semibold block mb-1">Dernière mise à jour</label>
                    <input
                        v-model="form.legal.version.derniere_mise_a_jour"
                        class="w-full border rounded p-2 mb-2"
                    />
                    <label class="font-semibold block mb-1">Note de version</label>
                    <textarea
                        v-model="form.legal.version.note"
                        rows="4"
                        class="w-full border rounded p-2"
                    />
                </div>


                <!--============= BOUTONS =============-->
                <div class="flex gap-4">
                    <button
                        type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded"
                    >
                        Enregistrer
                    </button>

                    <button
                        type="button"
                        @click="close()"
                        class="bg-gray-300 px-4 py-2 rounded"
                    >
                        Annuler
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
