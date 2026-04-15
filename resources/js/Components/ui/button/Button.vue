<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Primitive, type PrimitiveProps } from 'reka-ui' // Vérifie bien que c'est reka-ui
import { type ButtonVariants, buttonVariants } from '.'

// On ajoute l'ignore ici pour que le compilateur ne bloque pas sur l'extension
interface Props extends /* @vue-ignore */ PrimitiveProps {
    variant?: ButtonVariants['variant']
    size?: ButtonVariants['size']
    class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
})
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant, size }), props.class)"
  >
    <slot />
  </Primitive>
</template>
