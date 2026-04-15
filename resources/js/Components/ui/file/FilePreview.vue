<script setup lang="ts">
   import { defineProps, defineEmits } from "vue";
   import ButtonClose from '@/components/ui/button/ButtonClose.vue';

   interface FileWithPreview extends File {
       preview?: string;
   }

   const props = defineProps<{
       file: FileWithPreview;
   }>();

   const emit = defineEmits<{
   (e: 'remove'): void;
   }>();

   function isImage(file: File) {
       return file.type.startsWith("image/");
   }
</script>

<template>
    <div class="preview-item">
        <div class="preview-content">
            <img v-if="isImage(file)" :src="file.preview" alt="preview" />
            <div v-else class="file-icon">
                <span>{{ file.name }}</span>
            </div>
        </div>
        <ButtonClose @click="$emit('remove')" class="bg-white text-black dark:text-white dark:bg-black" >x</ButtonClose>
    </div>
</template>

<style scoped>
    .preview-item {
        position: relative;
        display: inline-block;
        margin: 0.5rem;
        width: 120px;
        height: 120px;
        border: 1px solid #ccc;
        border-radius: 6px;
        overflow: hidden;
        text-align: center;
        padding: 4px;
        font-size: 12px;
    }

    .preview-content img {
        max-width: 100%;
        max-height: 80px;
        object-fit: cover;
    }

    .file-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 80px;
        /* background-color: #f0f0f0; */
        word-break: break-word;
    }

    .preview-item button {
        position: absolute;
        top: 0;
        right: 0;
        border: none;
        cursor: pointer;
    }
</style>
