<script setup>
import { onMounted, ref } from 'vue';

// Cette ligne est CRUCIALE pour Vue 3.4+
const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-gray-300 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm transition-all"
        v-model="model"
        ref="input"
    />
</template>
