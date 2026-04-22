<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LogIn, Mail, Lock, Loader2, AlertCircle, Settings } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion" />

        <div class="mb-8 flex flex-col items-center">


            <h1 class="text-2xl font-black uppercase tracking-tighter text-zinc-900 dark:text-white">
                Ravi de vous revoir
            </h1>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 mt-2">
                Identifiez-vous pour continuer
            </p>
        </div>

        <div v-if="status" class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-sm font-bold text-emerald-600 shadow-sm text-center">
            {{ status }}
        </div>

        <div v-if="form.errors.email?.includes('suspendu') || form.errors.email?.includes('banni')"
             class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 animate-shake">
            <div class="bg-red-500 p-2 rounded-xl text-white shadow-lg">
                <AlertCircle class="w-5 h-5" />
            </div>
            <div>
                <p class="text-xs font-black uppercase text-red-600 leading-none mb-1">Accès refusé</p>
                <p class="text-sm font-bold text-red-700 tracking-tight">{{ form.errors.email }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1 ml-1">Adresse Email</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-indigo-600 transition-colors">
                        <Mail class="h-4 w-4" />
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full pl-11 !rounded-2xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 focus:ring-indigo-600 focus:border-indigo-600 transition-all shadow-sm h-12"
                        v-model="form.email"
                        required
                        autofocus
                        placeholder="votre@email.be"
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.email" v-if="!form.errors.email?.includes('suspendu') && !form.errors.email?.includes('banni')" />
            </div>

            <div class="mt-4">
                <div class="flex justify-between items-end mb-1 ml-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Mot de passe</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[9px] font-black uppercase tracking-widest text-zinc-500 hover:text-indigo-600 transition-colors"
                    >
                        Oublié ?
                    </Link>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-indigo-600 transition-colors">
                        <Lock class="h-4 w-4" />
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full pl-11 !rounded-2xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 focus:ring-indigo-600 focus:border-indigo-600 transition-all shadow-sm h-12"
                        v-model="form.password"
                        required
                        placeholder="••••••••"
                        autocomplete="current-password"
                    />
                </div>
                <InputError class="mt-2 ml-1" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between mt-2 ml-1">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" class="!rounded-md border-zinc-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span class="ms-2 text-[10px] font-black uppercase tracking-widest text-zinc-500 group-hover:text-zinc-700 transition-colors">Rester connecté</span>
                </label>
            </div>

            <div class="mt-8">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-zinc-900 dark:bg-white text-white dark:text-zinc-950 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl hover:bg-indigo-600 hover:dark:bg-indigo-500 hover:dark:text-white active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed group h-14"
                >
                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <LogIn v-else class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                    Se connecter
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-zinc-100 dark:border-zinc-800 text-center">
            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">
                Pas encore de compte ?
                <Link :href="route('register')" class="text-indigo-600 hover:underline ml-1 font-black">Rejoindre l'aventure</Link>
            </p>
        </div>
    </GuestLayout>
</template>

<style scoped>
.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.animate-spin-slow {
    animation: spin 3s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
