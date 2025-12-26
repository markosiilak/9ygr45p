<template>
  <div class="mobile-cta">
    <div class="mobile-cta-content">
      <div class="mobile-cta-price">
        <span
          v-if="priceRange"
          class="text-sm text-gray-500"
        >
          <ClientOnly fallback="from">
            {{ t('events.from') }}
          </ClientOnly>
        </span>
        <span class="text-lg font-bold">{{ priceRange || 'Free' }}</span>
      </div>
      <UButton
        color="primary"
        size="lg"
        class="flex-1"
        @click="$emit('buy-tickets')"
      >
        {{ t('events.buyTickets') }}
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UButton } from '#components'
import { useI18n } from '@composables/useI18n'

defineProps<{
  priceRange: string | null
}>()

defineEmits<{
  'buy-tickets': []
}>()

const { t } = useI18n()
</script>

<style scoped>
@reference "tailwindcss";

.mobile-cta {
  @apply fixed bottom-0 left-0 right-0 z-40;
  @apply lg:hidden;
  @apply bg-white;
  @apply border-t border-gray-200;
  @apply p-4 shadow-2xl;
}

.mobile-cta-content {
  @apply flex items-center gap-4;
}

.mobile-cta-price {
  @apply flex flex-col;
}
</style>
