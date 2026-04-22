<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, X } from 'lucide-vue-next';

const page = usePage();
const show = ref(false);
const message = ref('');
let timeout = null;

const hideMessage = () => {
    show.value = false;
};

// On surveille les changements de flash messages venant du serveur
watch(() => page.props.flash.success, (newSuccess) => {
    if (newSuccess) {
        message.value = newSuccess;
        show.value = true;

        // On annule le timer précédent si un nouveau message arrive
        if (timeout) clearTimeout(timeout);

        // Disparition automatique après 5 secondes
        timeout = setTimeout(() => {
            show.value = false;
        }, 5000);
    }
}, { immediate: true });
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show && message" class="fixed bottom-5 right-5 z-[100] max-w-sm w-full bg-white border border-emerald-100 shadow-2xl rounded-2xl p-4 pointer-events-auto">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <CheckCircle class="h-6 w-6 text-emerald-500" />
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-bold text-zinc-900">Succès !</p>
                    <p class="mt-1 text-sm text-zinc-500">{{ message }}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="hideMessage" class="rounded-md inline-flex text-zinc-400 hover:text-zinc-500">
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
