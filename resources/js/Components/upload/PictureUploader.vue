<script setup lang="ts">
import { ref, defineProps, defineEmits, watch } from 'vue';
import { Image as ImageIcon, X, UploadCloud } from 'lucide-vue-next';

const props = defineProps<{
    existingImage?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'file-selected', file: File | null): void;
}>();

const dragOver = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const MAX_SIZE = 2 * 1024 * 1024; // 2 Mo

const hasImage = ref(!!props.existingImage);
const imagePreview = ref(props.existingImage ?? '');
const selectedFile = ref<File | null>(null);

// Surveille les changements de l'image existante (utile lors du passage en mode édition)
watch(
    () => props.existingImage,
    (val) => {
        if (val) {
            hasImage.value = true;
            imagePreview.value = val;
        }
    }
);

function openFileDialog() {
    if (props.disabled || hasImage.value) return;
    fileInput.value?.click();
}

function handleDrop(e: DragEvent) {
    dragOver.value = false;
    if (props.disabled || hasImage.value) return;
    if (!e.dataTransfer?.files?.length) return;

    handleFile(e.dataTransfer.files[0]);
}

function onFileChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (!files || !files.length) return;

    handleFile(files[0]);
}

function handleFile(file: File) {
    if (file.size > MAX_SIZE) {
        alert('L’image est trop lourde (max 2 Mo).');
        return;
    }

    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner une image valide.');
        return;
    }

    selectedFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
    hasImage.value = true;
    emit('file-selected', file);
}

function removeImage() {
    selectedFile.value = null;
    imagePreview.value = '';
    hasImage.value = false;
    emit('file-selected', null);
    // On vide l'input file pour permettre de re-sélectionner le même fichier si besoin
    if (fileInput.value) fileInput.value.value = '';
}
</script>

<template>
    <div class="w-full space-y-3">
        <div v-if="hasImage" class="relative inline-block group">
            <img
                :src="imagePreview"
                class="h-40 w-40 rounded-xl object-cover shadow-md ring-1 ring-gray-200 dark:ring-gray-700"
                alt="Aperçu"
            />
            <button
                v-if="!disabled"
                type="button"
                class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow-lg transition-transform hover:scale-110 hover:bg-red-600 focus:outline-none"
                @click="removeImage"
            >
                <X class="h-5 w-5" />
            </button>
            <div class="mt-2 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ selectedFile ? 'Nouvelle image sélectionnée' : 'Image actuelle' }}
                </p>
            </div>
        </div>

        <div
            v-else
            class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 transition-all"
            :class="[
                dragOver
                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/20'
                    : 'border-gray-300 bg-gray-50 hover:border-indigo-400 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-indigo-500',
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
            ]"
            @click="openFileDialog"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop"
        >
            <div class="mb-4 rounded-full bg-white p-3 shadow-sm dark:bg-gray-800">
                <UploadCloud class="h-8 w-8 text-indigo-500" />
            </div>

            <div class="text-center">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    Cliquez ou glissez une image ici
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    PNG, JPG ou WEBP (max. 2 Mo)
                </p>
            </div>
        </div>

        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="onFileChange"
        />
    </div>
</template>
