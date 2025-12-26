<template>
  <UAlert
    :color="color"
    :variant="variant"
    :icon="icon"
  >
    <slot>{{ message }}</slot>
  </UAlert>
</template>

<script setup lang="ts">
import { UAlert } from '#components'

const props = withDefaults(defineProps<{
  message?: string
  icon?: string
  color?: 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'error' | 'neutral'
  variant?: 'soft' | 'solid' | 'outline' | 'subtle'
}>(), {
  message: 'Loading...',
  icon: '',
  color: 'primary',
  variant: 'soft'
})

const { message, icon, color, variant } = props
</script>
