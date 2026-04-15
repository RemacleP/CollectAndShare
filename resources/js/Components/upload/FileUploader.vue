<script setup lang="ts">
    import { ref } from 'vue';
    import FilePreview from '@/components/ui/file/FilePreview.vue';
    import FileProgress from '@/components/ui/file/FileProgress.vue';
    import FileValidator from '@/components/upload/FileValidator';
    import axios from 'axios';
    import { Input } from '@/components/ui/input';
    import { Button } from '@/components/ui/button';

    const files = ref<File[]>([]);
    const progresses = ref<number[]>([]);
    const dragOver = ref(false);
    const uploading = ref(false);
    const fileInput = ref<HTMLInputElement | null>(null);
    const emit = defineEmits<{
        (e: 'file-selected', file: File): void
    }>();
    interface FileWithPreview extends File {
        preview?: string;
    }

    function handleDrop(e: DragEvent) {
        dragOver.value = false;
        if (!e.dataTransfer) return;
        handleFiles(Array.from(e.dataTransfer.files));
    }

    function handleFiles(selectedFiles: File[]) {
        selectedFiles.forEach((file) => {
            const f = file as FileWithPreview;
            f.preview = URL.createObjectURL(f);

            const error = FileValidator.validate(f, {
                maxSize: 10 * 1024 * 1024,
                // allowedTypes: ['image/png', 'image/jpeg', 'application/pdf'],
            });

            if (!error) {
                files.value.push(f);
                emit('file-selected', files.value[0]);
            }
            else console.warn(error);
        });
    }

    function removeFile(index: number) {
        files.value.splice(index, 1);
        progresses.value.splice(index, 1);
    }

    async function uploadFile(file: File, index: number) {
        const formData = new FormData();
        formData.append('files[]', file);

        try {
            await axios.post('/api/upload', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (e: ProgressEvent) => {
                    progresses.value[index] = Math.round((e.loaded * 100) / e.total);
                },
            });
        } catch (err) {
            console.error('Erreur upload fichier', file.name, err);
        }
    }

    function onFileChange(event: Event) {
        const target = event.target as HTMLInputElement;
        if(!target.files) return;

        handleFiles(Array.from(target.files));
        target.value = '';
    }

    function openFileDialog(){
        fileInput.value?.click();
    }
</script>

<template>
    <div class="file-uploader space-y-4">

        <!-- Prévisualisation -->
        <div class="preview flex flex-wrap gap-3" v-if="files.length">
            <FilePreview
                v-for="(file, index) in files"
                :key="index"
                :file="file"
                @remove="removeFile(index)"
            />
        </div>

        <!-- Dropzone -->
        <div
            class="dropzone p-8 border-2 border-dashed rounded-xl cursor-pointer transition-colors flex flex-col items-center justify-center gap-3 text-center"
            :class="dragOver ? 'border-blue-500 bg-blue-50' : 'border-gray-400 hover:border-blue-500'"
            @click="openFileDialog"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop"
        >
            <svg
                aria-hidden="true"
                class="w-12 h-12 text-gray-400"
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

            <p class="text-gray-600">
                Glissez-déposez vos fichiers ici
                <br />
                ou cliquez ci-dessous pour les sélectionner
            </p>

            <Button variant="outline" type="button" @click="openFileDialog" class="cursor-pointer">
                Sélectionner des fichiers
            </Button>

            <!-- Input caché -->
            <input
                type="file"
                multiple
                ref="fileInput"
                class="hidden"
                @change="onFileChange"
                accept="*/*"
            />
        </div>



        <!-- Progression upload -->
        <div class="space-y-2" v-if="uploading">
            <FileProgress
                v-for="(file, index) in files"
                :key="index"
                :file="file"
                :progress="progresses[index] || 0"
            />
        </div>
    </div>
</template>

<style scoped></style>
