<template>
  <div class="hero-section">
    <div class="hero-background">
      <NuxtImg
        v-if="event.image_url && optimizedImageUrl"
        :src="optimizedImageUrl"
        :alt="event.title || event.name"
        class="hero-image"
        loading="lazy"
        sizes="100vw"
      />
      <div
        v-else
        class="hero-placeholder"
      />
      <div class="hero-overlay" />
      <div class="hero-vignette" />
    </div>

    <!-- Hero Content -->
    <div class="hero-content">
      <div class="hero-container">
        <div class="hero-inner container mx-auto">
          <!-- Breadcrumb -->
          <nav class="breadcrumb">
            <NuxtLink
              to="/events"
              class="breadcrumb-link"
            >
              <UIcon
                name="i-heroicons-arrow-left-20-solid"
                class="w-4 h-4"
              />
              {{ t('nav.events') }}
            </NuxtLink>
          </nav>

          <!-- Event Title & Meta -->
          <div class="hero-text">
            <div class="badges-row">
              <UBadge
                v-if="eventStatus"
                :color="statusColor"
                variant="solid"
                class="status-badge"
              >
                {{ statusLabel }}
              </UBadge>
              <UBadge
                v-if="event.location"
                color="neutral"
                variant="soft"
                class="location-badge"
              >
                <UIcon
                  name="i-heroicons-map-pin-20-solid"
                  class="w-3.5 h-3.5 mr-1"
                />
                {{ event.location }}
              </UBadge>
            </div>

            <h1 class="event-title">
              {{ event.title || event.name }}
            </h1>

            <p
              v-if="event.subtitle"
              class="event-subtitle"
            >
              {{ event.subtitle }}
            </p>

            <div class="event-meta">
              <div class="meta-item">
                <UIcon
                  name="i-heroicons-calendar-20-solid"
                  class="meta-icon"
                />
                <span>{{ formatDateTime(String(event.start_time || event.date || '')) }}</span>
              </div>
              <div
                v-if="event.capacity"
                class="meta-item"
              >
                <UIcon
                  name="i-heroicons-ticket-20-solid"
                  class="meta-icon"
                />
                <span>{{ event.capacity }} {{ t('events.ticketsAvailable') }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="hero-actions">
            <UButton
              variant="ghost"
              color="neutral"
              class="action-btn favorite-btn"
              :class="{ active: isFavorite }"
              @click="toggleFavorite"
            >
              <UIcon
                :name="isFavorite ? 'i-heroicons-heart-solid' : 'i-heroicons-heart'"
                class="w-5 h-5 text-white"
              />
            </UButton>
            <UButton
              variant="ghost"
              color="neutral"
              class="action-btn share-btn"
              @click="shareEvent"
            >
              <UIcon
                name="i-heroicons-share-20-solid"
                class="w-5 h-5 text-white"
              />
            </UButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { UBadge, UIcon } from '#components'
import formatDateTime from '@utils/formatDateTime'
import { getOptimizedImageUrl } from '@utils/imageUrl'
import { useI18n } from '@composables/useI18n'
import type { Event } from '@types'

const props = defineProps<{
  event: Event
  eventStatus: string | null
  statusLabel: string
  statusColor: string
}>()

const { t } = useI18n()

const isFavorite = ref(false)

const optimizedImageUrl = computed(() => getOptimizedImageUrl(props.event?.image_url, 1600))

const toggleFavorite = () => {
  isFavorite.value = !isFavorite.value
}

const shareEvent = async () => {
  if (navigator.share) {
    try {
      await navigator.share({
        title: props.event?.title || props.event?.name || 'Event',
        url: window.location.href
      })
    } catch {
      // User cancelled or error
    }
  } else {
    // Fallback: copy to clipboard
    await navigator.clipboard.writeText(window.location.href)
  }
}
</script>

<style scoped>
@reference "tailwindcss";

/* Hero Section */
.hero-section {
  @apply relative w-full;
  min-height: 50vh;
  max-height: 70vh;
}

.hero-background {
  @apply absolute inset-0;
}

.hero-image {
  @apply w-full h-full object-cover;
}

.hero-placeholder {
  @apply w-full h-full;
  background: linear-gradient(
    135deg,
    rgba(84, 0, 180, 0.4) 0%,
    rgba(54, 0, 120, 0.6) 50%,
    rgba(24, 0, 60, 0.8) 100%
  );
}

.hero-overlay {
  @apply absolute inset-0;
  background: linear-gradient(
    180deg,
    rgba(0, 0, 0, 0.2) 0%,
    rgba(0, 0, 0, 0.1) 40%,
    rgba(0, 0, 0, 0.6) 80%,
    rgba(0, 0, 0, 0.9) 100%
  );
}

.hero-vignette {
  @apply absolute inset-0 pointer-events-none;
  background: radial-gradient(
    ellipse at center,
    transparent 40%,
    rgba(0, 0, 0, 0.3) 100%
  );
}

.hero-content {
  @apply absolute inset-0 flex items-end;
  @apply pb-8 sm:pb-12;
}

.hero-container {
  @apply w-full px-4 sm:px-6 lg:px-8;
  max-width: 1920px;
  margin-left: auto;
  margin-right: auto;
}

.hero-inner {
  @apply flex flex-col gap-4;
}

/* Breadcrumb */
.breadcrumb {
  @apply mb-2;
}

.breadcrumb-link {
  @apply inline-flex items-center gap-2;
  @apply text-sm text-white/70 hover:text-white;
  @apply transition-colors duration-200;
}

/* Hero Text */
.hero-text {
  @apply flex flex-col gap-3;
}

.badges-row {
  @apply flex flex-wrap items-center gap-2;
}

.status-badge {
  @apply backdrop-blur-md;
}

.location-badge {
  @apply backdrop-blur-md bg-white/10 text-white border-white/20;
}

.event-title {
  @apply text-3xl sm:text-4xl md:text-5xl font-bold text-white;
  @apply leading-tight;
  text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
}

.event-subtitle {
  @apply text-lg text-white/80;
}

.event-meta {
  @apply flex flex-wrap items-center gap-4 mt-2;
}

.meta-item {
  @apply flex items-center gap-2 text-white/90;
}

.meta-icon {
  @apply w-5 h-5 text-white/70;
}

/* Hero Actions */
.hero-actions {
  @apply flex items-center gap-2 mt-4;
}

.action-btn {
  @apply flex items-center justify-center;
  @apply w-10 h-10 rounded-full;
  @apply bg-white/20 backdrop-blur-md;
  @apply text-white border border-white/30;
  @apply transition-all duration-300;
}

.action-btn:hover {
  @apply bg-white/30 scale-110;
}

.favorite-btn.active {
  @apply bg-red-500/80 border-red-400;
}

.favorite-btn.active:hover {
  @apply bg-red-500;
}
</style>
