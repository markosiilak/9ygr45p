<template>
  <div class="events-list">
    <div class="events-header">
      <h1 class="events-title">
        {{ t('events.title') }}
      </h1>
    </div>

    <!-- Initial loading state -->
    <AppLoading
      v-if="initialLoading"
      :message="t('events.loadingEvents')"
    />

    <!-- Error state -->
    <UAlert
      v-else-if="error"
      color="error"
      variant="soft"

      class="mx-4"
    >
      {{ t('errors.serverError') }}: {{ error }}
    </UAlert>

    <!-- Events grid -->
    <div
      v-else
      class="events-content"
    >
      <div v-if="allEvents.length === 0">
        <UAlert
          color="neutral"
          variant="soft"

        >
          {{ t('events.noEvents') }}
        </UAlert>
      </div>

      <div v-else>
        <div class="events-grid">
          <EventCard
            v-for="(event, index) in allEvents"
            :key="index"
            :event="event"
          />

          <!-- Infinite scroll trigger -->
          <div
            ref="loadMoreTrigger"
            class="load-more-trigger"
          >
            <!-- Loading more indicator -->
            <div
              v-if="loadingMore"
              class="loading-indicator"
            >
              <AppLoading :message="t('events.loadingMore')" />
            </div>

            <!-- End of list indicator -->
            <div
              v-else-if="!hasMore && allEvents.length > 0"
              class="end-indicator"
            >
              <UIcon
                name="i-heroicons-check-circle-20-solid"
                class="w-5 h-5 mr-2"
              />
              <span>{{ t('events.seenAll', { count: allEvents.length }) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import AppLoading from '@components/layout/AppLoading.vue'
import EventCard from './EventCard.vue'
import { useI18n } from '@composables/useI18n'

import type { EventsListProps, Event } from '@types'

const { t } = useI18n()

const props = defineProps<EventsListProps>()

// Pagination state
const ITEMS_PER_PAGE = 12
const displayedCount = ref(ITEMS_PER_PAGE)
const loadingMore = ref(false)
const initialLoading = ref(false)
const error = ref('')

// All events from props (SSR data)
const sourceEvents = computed<Event[]>(() => props.events || [])

// Events currently displayed
const allEvents = computed(() => sourceEvents.value.slice(0, displayedCount.value))

// Check if there are more events to load
const hasMore = computed(() => displayedCount.value < sourceEvents.value.length)

// Intersection Observer for infinite scroll
const loadMoreTrigger = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

const loadMore = () => {
  if (loadingMore.value || !hasMore.value) return

  loadingMore.value = true

  // Simulate network delay for smooth UX
  setTimeout(() => {
    displayedCount.value += ITEMS_PER_PAGE
    loadingMore.value = false
  }, 300)
}

onMounted(() => {
  // Create intersection observer
  observer = new IntersectionObserver(
    (entries) => {
      const entry = entries[0]
      if (entry?.isIntersecting && hasMore.value) {
        loadMore()
      }
    },
    {
      rootMargin: '100px', // Start loading before reaching the bottom
      threshold: 0.1
    }
  )

  // Observe the trigger element
  if (loadMoreTrigger.value) {
    observer.observe(loadMoreTrigger.value)
  }
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
  }
})

// Re-observe when trigger element changes
watch(loadMoreTrigger, (el) => {
  if (observer && el) {
    observer.observe(el)
  }
})
</script>

<style scoped>
@reference "tailwindcss";

.events-list {
  width: 100%;
  max-width: 100%;
}

.events-header {
  @apply px-4 sm:px-6 lg:px-8 mb-6;
  margin-left: auto;
  margin-right: auto;
}

.events-title {
  @apply text-3xl font-bold text-gray-900;
}

.events-content {
  @apply px-4 sm:px-6 lg:px-8;
  margin-left: auto;
  margin-right: auto;
}

.events-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

@media (min-width: 640px) {
  .events-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .events-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1280px) {
  .events-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (min-width: 1536px) {
  .events-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

@media (min-width: 1920px) {
  .events-grid {
    grid-template-columns: repeat(6, 1fr);
  }
}

.load-more-trigger {
  min-height: 1px;
}

.loading-indicator {
  @apply flex items-center justify-center py-8;
}

.end-indicator {
  @apply flex items-center justify-center py-8 text-gray-500;
}
</style>
