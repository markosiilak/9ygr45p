<template>
  <div class="loading-overlay">
    <div class="loading-content">
      <!-- Simple spinner -->
      <div class="spinner" />

      <!-- Optional message -->
      <p
        v-if="displayMessage"
        class="loading-message"
      >
        {{ displayMessage }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from '@composables/useI18n'

const { t } = useI18n()

const props = withDefaults(defineProps<{
  message?: string
  showMessage?: boolean
}>(), {
  message: '',
  showMessage: true
})

const displayMessage = computed(() => {
  if (!props.showMessage) return ''
  return props.message || t('common.loading')
})
</script>

<style scoped>
@reference "tailwindcss";

.loading-overlay {
  @apply fixed inset-0 z-50;
  @apply flex items-center justify-center;
  @apply bg-white/80;
  @apply backdrop-blur-sm;
}

.loading-content {
  @apply flex flex-col items-center gap-4;
}

.spinner {
  @apply w-10 h-10 rounded-full;
  @apply border-3 border-gray-200;
  border-top-color: var(--color-primary-500);
  animation: spin 0.8s linear infinite;
}

.loading-message {
  @apply text-sm font-medium;
  @apply text-gray-600;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
