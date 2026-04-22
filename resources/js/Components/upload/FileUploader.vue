<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import FilePreview from '@/Components/ui/file/FilePreview.vue';
import FileValidator from '@/Components/upload/FileValidator';
import { Button } from '@/Components/ui/button';

interface FileWithPreview extends File {
    preview?: string;
}

const files = ref<FileWithPreview[]>([]);
const dragOver = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const emit = defineEmits<{
    // On émet maintenant un tableau de fichiers pour le multi-images
    (e: 'files-selected', files: File[]): void
}>();

/**
 * Traitement des fichiers sélectionnés
 */
function handleFiles(selectedFiles: File[]) {
    selectedFiles.forEach((file) => {
        // Validation via ton utilitaire
        const error = FileValidator.validate(file, {
            maxSize: 10 * 1024 * 1024, // 10MB
            allowedTypes: ['image/png', 'image/jpeg', 'image/webp'],
        });

        if (!error) {
            const f = file as FileWithPreview;
            // Création de l'aperçu visuel
            f.preview = URL.createObjectURL(f);
            files.value.push(f);
        } else {
            console.error('Fichier refusé:', file.name, error);
        }
    });

    // On informe le composant parent (Formulaire)
    emit('files-selected', files.value);
}

/**
 * Suppression d'un fichier de la liste
 */
function removeFile(index: number) {
    const file = files.value[index];
    if (file.preview) {
        URL.revokeObjectURL(file.preview); // Libère la mémoire
    }
    files.value.splice(index, 1);

    // Mise à jour du parent
    emit('files-selected', files.value);
}

/**
 * Gestion du Drag & Drop
 */
function handleDrop(e: DragEvent) {
    dragOver.value = false;
    if (!e.dataTransfer) return;
    handleFiles(Array.from(e.dataTransfer.files));
}

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (!target.files) return;

    handleFiles(Array.from(target.files));
    target.value = ''; // Reset l'input pour pouvoir sélectionner le même fichier
}

function openFileDialog() {
    fileInput.value?.click();
}

// Nettoyage des URLs au démontage du composant
onUnmounted(() => {
    files.value.forEach(file => {
        if (file.preview) URL.revokeObjectURL(file.preview);
    });
});
</script>

<template>
    <div class="file-uploader space-y-4">

        <div v-if="files.length" class="preview flex flex-wrap gap-4 p-4 bg-gray-50 dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800">
            <div v-for="(file, index) in files" :key="index" class="relative group">
                <FilePreview
                    :file="file"
                    @remove="removeFile(index)"
                />
            </div>
        </div>

        <div
            class="dropzone group p-10 border-2 border-dashed rounded-[2rem] cursor-pointer transition-all flex flex-col items-center justify-center gap-4 text-center"
            :class="dragOver
                ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/10'
                : 'border-gray-300 dark:border-zinc-700 hover:border-indigo-400 hover:bg-gray-50 dark:hover:bg-zinc-900/50'"
            @click="openFileDialog"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop"
        >
            <div class="p-4 rounded-full bg-gray-100 dark:bg-zinc-800 text-gray-400 group-hover:text-indigo-500 transition-colors">
                <svg
                    aria-hidden="true"
                    class="w-8 h-8"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5 5 5M12 5v10"
                    />
                </svg>
            </div>

            <div class="space-y-1">
                <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                    Cliquez ou glissez vos photos ici
                </p>
                <p class="text-xs text-gray-400">
                    PNG, JPG ou WEBP (Max 10Mo par image)
                </p>
            </div>

            <Button variant="outline" type="button" class="rounded-xl font-bold">
                Choisir des fichiers
            </Button>

            <input
                type="file"
                multiple
                ref="fileInput"
                class="hidden"
                @change="onFileChange"
                accept="image/png, image/jpeg, image/webp"
            />
        </div>
    </div>
</template>

<style scoped>
.dropzone {
    /* Pour éviter la sélection de texte pendant le drag and drop */
    user-select: none;
}
</style>
