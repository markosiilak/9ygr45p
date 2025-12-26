<template>
  <aside class="featured-sidebar">
    <div class="sidebar-header">
      <h2 class="sidebar-title">
        {{ t('home.mostPopularEvents') }}
      </h2>
    </div>
    <div class="sidebar-events">
      <template v-if="events.length > 0">
        <EventCard
          v-for="event in events"
          :key="event.id"
          :event="event"
          class="sidebar-event-card"
        />
      </template>
      <div
        v-else
        class="flex items-center justify-center h-32 text-gray-500"
      >
        {{ t('common.loading') }}...
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import EventCard from '@components/events/EventCard.vue'
import { useI18n } from '@composables/useI18n'

import type { FeaturedEventsSidebarProps } from '@types'

const { t } = useI18n()

defineProps<FeaturedEventsSidebarProps>()
</script>

<style scoped>
@reference "tailwindcss";

/* Featured Sidebar - 1/3 width on desktop */
.featured-sidebar {
  @apply w-full lg:w-1/3;
  @apply bg-white border-l border-gray-200;
  @apply flex flex-col;
  @apply overflow-hidden;
  height: 60vh;
  min-height: 400px;
  max-height: 700px;
}

.sidebar-header {
  @apply px-6 py-5 border-b border-gray-200;
  @apply bg-white;
  @apply shadow-sm;
  flex-shrink: 0;
}

.sidebar-title {
  @apply text-xl font-bold text-gray-900;
}

.sidebar-events {
  @apply flex-1 overflow-hidden;
  @apply px-4 py-4;
  @apply flex flex-col;
  gap: 1rem;
}

.sidebar-event-card {
  @apply w-full flex-1;
  min-height: 0;
}

/* On mobile, hide sidebar and show full-width hero */
@media (max-width: 1023px) {
  .featured-sidebar {
    @apply hidden;
  }
}
</style>
