<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { UploadCloud, Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps({ settings: Object });

const form = useForm({
    logo: null,
});

const submit = () => {
    form.post(route('admin.settings.logo.update'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black uppercase tracking-tighter mb-8">Paramètres de la plateforme</h2>

            <div class="bg-white p-8 rounded-[2.5rem] border border-zinc-200 shadow-sm">
                <div class="flex items-center gap-6">
                    <div class="w-32 h-32 rounded-3xl bg-zinc-50 border border-dashed border-zinc-200 flex items-center justify-center overflow-hidden">
                        <img v-if="settings.site_logo" :src="settings.site_logo" class="max-h-24 w-auto" />
                        <ImageIcon v-else class="text-zinc-300 w-12 h-12" />
                    </div>

                    <div class="flex-grow">
                        <h3 class="font-bold text-zinc-900 text-lg">Logo principal</h3>
                        <p class="text-sm text-zinc-500 mb-4">Ce logo apparaîtra sur la page de connexion et le dashboard.</p>

                        <div class="flex items-center gap-3">
                            <input type="file" @input="form.logo = $event.target.files[0]" id="logo" class="hidden" />
                            <label for="logo" class="bg-zinc-900 text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest cursor-pointer hover:bg-indigo-600 transition flex items-center gap-2">
                                <UploadCloud class="w-4 h-4" /> Choisir une image
                            </label>
                            <button @click="submit" v-if="form.logo" class="text-indigo-600 font-bold text-xs uppercase underline">Enregistrer</button>
                        </div>
                        <p v-if="form.logo" class="mt-2 text-xs text-green-600 font-bold italic">Nouveau fichier prêt : {{ form.logo.name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
