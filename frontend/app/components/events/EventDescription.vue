<template>
  <div class="content-card">
    <h2 class="section-title">
      <UIcon
        name="i-heroicons-document-text-20-solid"
        class="section-icon"
      />
      {{ t('events.description') }}
    </h2>
    <!-- Content is sanitized via sanitizeHtml utility to prevent XSS -->
    <!-- eslint-disable vue/no-v-html -->
    <div
      class="prose prose-lg max-w-none"
      v-html="sanitizedDescription"
    />
    <!-- eslint-enable vue/no-v-html -->
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { UIcon } from '#components'
import sanitizeHtml from '@utils/sanitizeHtml'
import { useI18n } from '@composables/useI18n'

const props = defineProps<{
  description: string | undefined
}>()

const { t } = useI18n()

const sanitizedDescription = computed(() => {
  const raw = props.description || ''
  return sanitizeHtml(raw)
})
</script>

<style scoped>
@reference "tailwindcss";

.content-card {
  @apply bg-white rounded-2xl;
  @apply p-6 shadow-lg;
  @apply border border-gray-100;
}

.section-title {
  @apply flex items-center gap-2 text-xl font-bold mb-4;
  @apply text-gray-900;
}

.section-icon {
  @apply w-5 h-5;
  color: rgb(var(--color-primary-500));
}

/* Prose Overrides */
.prose {
  @apply text-gray-700;
}

.prose :deep(p) {
  @apply mb-4;
}

.prose :deep(h2),
.prose :deep(h3) {
  @apply text-gray-900;
}

.prose :deep(a) {
  color: rgb(var(--color-primary-500));
}

.prose :deep(a:hover) {
  color: rgb(var(--color-primary-600));
}
</style>
