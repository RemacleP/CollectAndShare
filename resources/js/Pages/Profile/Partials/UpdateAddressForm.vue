<<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import {onMounted, ref, watch} from 'vue';

const props = defineProps({
    user: Object,    // L'objet User chargé avec ses relations
    address: Object, // L'adresse principale passée par le parent
});

const activeType = ref('primary');

// Initialisation du formulaire
const form = useForm({
    type: 'primary',
    street: '',
    number: '',
    box: '',
    city: '',
    postal_code: '',
    country: 'Belgique',
});

/**
 * Cette fonction est la SEULE qui doit toucher aux données du formulaire
 */
const switchAddress = (type) => {
    activeType.value = type;

    // On cherche la source de données dans props.user
    // Note: Inertia transforme CamelCase (shippingAddress) en snake_case (shipping_address)
    let data = null;
    if (type === 'primary') data = props.user?.address || props.address;
    else if (type === 'shipping') data = props.user?.shipping_address;
    else if (type === 'billing') data = props.user?.billing_address;

    // Mise à jour manuelle champ par champ pour forcer la réactivité
    form.type = type;
    form.street = data?.street ?? '';
    form.number = data?.number ?? '';
    form.box = data?.box ?? '';
    form.city = data?.city ?? '';
    form.postal_code = data?.postal_code ?? '';
    form.country = data?.country ?? 'Belgique';

    form.clearErrors();
};

// CRUCIAL: On observe l'arrivée de l'user.
// Dès que props.user change (chargement initial ou retour de PATCH), on rafraîchit.
watch(() => props.user, (val) => {
    if (val) {
        switchAddress(activeType.value);
    }
}, { immediate: true, deep: true });

const submit = () => {
    form.patch(route('address.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Pas besoin de switchAddress ici, le watcher s'en chargera
        },
    });
};

onMounted(() => {
    switchAddress('primary');
});
</script>

<template>

    <section>
        <header>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Gestion des adresses</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                Configurez vos adresses de résidence, de livraison et de facturation.
            </p>
        </header>

        <div class="flex p-1 bg-gray-100 dark:bg-zinc-800 rounded-xl mt-6 w-fit border border-gray-200 dark:border-zinc-700">
            <button
                v-for="(label, key) in { primary: 'Principale', shipping: 'Livraison', billing: 'Facturation' }"
                :key="key"
                @click="switchAddress(key)"
                type="button"
                :class="['px-4 py-2 text-xs font-bold uppercase rounded-lg transition-all', activeType === key ? 'bg-white dark:bg-zinc-700 shadow text-indigo-600 dark:text-white' : 'text-gray-500']"
            >
                {{ label }}
            </button>
        </div>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="md:col-span-3">
                    <InputLabel for="street" value="Rue" />
                    <TextInput id="street" v-model="form.street" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.street" class="mt-2" />
                </div>
                <div class="md:col-span-1">
                    <InputLabel for="number" value="N°" />
                    <TextInput id="number" v-model="form.number" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.number" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="box" value="Boîte" />
                    <TextInput id="box" v-model="form.box" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.box" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <InputLabel for="postal_code" value="Code Postal" />
                    <TextInput id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.postal_code" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="city" value="Ville" />
                    <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.city" class="mt-2" />
                </div>
            </div>

            <div>
                <InputLabel for="country" value="Pays" />
                <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full" />
                <InputError :message="form.errors.country" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    Enregistrer {{ activeType === 'primary' ? 'l\'adresse principale' : activeType === 'shipping' ? 'de livraison' : 'de facturation' }}
                </PrimaryButton>

                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-bold">Enregistré.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
