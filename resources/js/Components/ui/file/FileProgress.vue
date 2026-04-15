<script setup lang="ts">
    import { defineProps } from "vue";

    interface FileWithPreview extends File {
        preview?: string;
    }

    const props = defineProps<{
        file: FileWithPreview;
        progress?: number;
    }>();
</script>

<template>
    <div class="file-progress">
        <p>{{ file.name }}</p>
        <div class="progress-bar">
            <div class="progress" :style="{ width: progress + '%'}"></div>
        </div>
    </div>
</template>

<style scoped>
    .progress {
        height: 100%;
        background: #3490dc;
        transition: width 0.2s;
    }
</style>
