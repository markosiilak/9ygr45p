<template>
  <div
    v-if="hasDetails"
    class="content-card"
  >
    <h2 class="section-title">
      <UIcon
        name="i-heroicons-information-circle-20-solid"
        class="section-icon"
      />
      Details
    </h2>
    <div class="details-grid">
      <div
        v-if="organizer"
        class="detail-item"
      >
        <span class="detail-label">Organizer</span>
        <span class="detail-value">{{ organizer }}</span>
      </div>
      <div
        v-if="categories && Array.isArray(categories) && categories.length"
        class="detail-item"
      >
        <span class="detail-label">Categories</span>
        <div class="flex flex-wrap gap-1">
          <UBadge
            v-for="cat in categories"
            :key="cat"
            color="neutral"
            variant="soft"
            size="xs"
          >
            {{ cat }}
          </UBadge>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { UBadge, UIcon } from '#components'

const props = defineProps<{
  organizer?: string
  categories?: string[]
}>()

const hasDetails = computed(() => {
  return props.organizer || (props.categories && props.categories.length > 0)
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

.details-grid {
  @apply grid gap-4;
}

.detail-item {
  @apply flex flex-col gap-1;
}

.detail-label {
  @apply text-xs font-medium uppercase tracking-wide;
  @apply text-gray-500;
}

.detail-value {
  @apply text-sm font-medium text-gray-900;
}
</style>
