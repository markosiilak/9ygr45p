<template>
  <UButton
    color="neutral"
    variant="ghost"
    class="cart-button"
    @click="$emit('toggle')"
  >
    <div class="relative">
      <UIcon
        name="i-heroicons-shopping-cart-20-solid"
        class="w-5 h-5"
      />
      <span
        v-if="itemCount > 0"
        class="cart-badge"
      >
        {{ itemCount }}
      </span>
    </div>
  </UButton>
</template>

<script setup lang="ts">
import { UButton, UIcon } from '#components'

defineProps<{
  itemCount: number
}>()

defineEmits<{
  toggle: []
}>()
</script>

<style scoped>
@reference "tailwindcss";

.cart-button {
  @apply relative p-2;
}

.cart-badge {
  @apply absolute -top-1 -right-1;
  @apply bg-red-500 text-white;
  @apply text-xs font-bold;
  @apply w-5 h-5 rounded-full;
  @apply flex items-center justify-center;
  @apply animate-pulse;
}
</style>
