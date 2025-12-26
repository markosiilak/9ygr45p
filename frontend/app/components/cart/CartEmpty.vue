<template>
  <div class="empty-state">
    <UIcon
      name="i-heroicons-shopping-cart"
      class="empty-icon"
    />
    <p class="empty-text">
      {{ t('cart.empty') }}
    </p>
    <UButton
      color="primary"
      variant="soft"
      size="sm"
      to="/events"
      @click="$emit('close')"
    >
      {{ t('cart.browseEvents') }}
    </UButton>
  </div>
</template>

<script setup lang="ts">
import { UButton, UIcon } from '#components'
import { useI18n } from '@composables/useI18n'

const { t } = useI18n()

defineEmits<{
  close: []
}>()
</script>

<style scoped>
@reference "tailwindcss";

.empty-state {
  @apply flex flex-col items-center justify-center;
  @apply p-12 text-center h-full;
}

.empty-icon {
  @apply w-20 h-20 text-gray-300 mb-4;
}

.empty-text {
  @apply text-base text-gray-500 mb-6;
}
</style>
