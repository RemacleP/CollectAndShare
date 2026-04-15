<script setup lang="ts">
    import { X } from 'lucide-vue-next';

    const emit = defineEmits<{
    (event: 'click'): void
    }>();
</script>

<template>
    <button
        @click="$emit('click')"
        class="ring-offset-background focus:ring-ring data-[state=open]:bg-accent data-[state=open]:text-muted-foreground absolute top-4 right-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4"
        aria-label="Close"
        title="Close"
    >
        <X />
    </button>
</template>

<style scoped>

</style>
