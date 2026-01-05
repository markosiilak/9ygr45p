<template>
  <NuxtLink
    :to="eventUrl"
    class="event-card group"
  >
    <!-- Full-size Background Image (optimized) -->
    <div class="card-background">
      <NuxtImg
        v-if="event.image_url && optimizedImageUrl"
        :src="optimizedImageUrl"
        :alt="event.title || event.name"
        class="bg-image"
        loading="lazy"
        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
      />
      <div
        v-else
        class="placeholder-bg"
      >
        <div class="placeholder-pattern" />
      </div>
      <div class="image-overlay" />
    </div>

    <!-- Floating badges (top left) -->
    <div class="badges-container">
      <UiStatusBadge
        v-if="eventStatus"
        :status="eventStatus"
        :label="statusLabel"
        variant="solid"
        class="status-badge"
      />
      <!-- Price tag -->
      <div
        v-if="priceRange"
        class="price-tag"
      >
        <ClientOnly fallback="from">
          <span class="price-from">{{ t('events.from') }}</span>
        </ClientOnly>
        <span class="price-amount">{{ priceRange }}</span>
      </div>
      <!-- Tickets available -->
      <div
        v-if="event.tickets_available !== undefined"
        class="tickets-pill"
      >
        <UIcon
          name="i-heroicons-ticket-20-solid"
          class="tickets-icon"
        />
        <span class="tickets-count">{{ event.tickets_available }}</span>
      </div>
    </div>

    <!-- Favorite button (top right) -->
    <UButton
      variant="ghost"
      color="neutral"
      class="favorite-btn"
      @click.prevent="toggleFavorite"
    >
      <UIcon
        :name="isFavorite ? 'i-heroicons-heart-solid' : 'i-heroicons-heart-20-solid'"
        class="favorite-icon"
      />
    </UButton>

    <!-- Content Section (bottom, overlaid on image) -->
    <div class="card-content">
      <h3 class="event-title">
        {{ event.title || event.name }}
      </h3>

      <div class="meta-row">
        <div
          v-if="event.location"
          class="meta-item"
        >
          <UIcon
            name="i-heroicons-map-pin-20-solid"
            class="meta-icon"
          />
          <span class="meta-text">{{ event.location }}</span>
        </div>
        <div class="meta-item">
          <UIcon
            name="i-heroicons-calendar-20-solid"
            class="meta-icon"
          />
          <span class="meta-text">{{ formatDateTime(event.start_time || event.date) }}</span>
        </div>
      </div>

      <!-- CTA Arrow -->
      <div class="cta-row">
        <ClientOnly fallback-tag="span" fallback="See details">
          <span class="cta-text">{{ t('events.viewEvent') }}</span>
        </ClientOnly>
        <div class="cta-arrow">
          <UIcon
            name="i-heroicons-arrow-right-20-solid"
            class="arrow-icon"
          />
        </div>
      </div>
    </div>

    <!-- Hover glow effect -->
    <div class="hover-glow" />
  </NuxtLink>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { UButton, UIcon } from '#components'
import formatDateTime from '@utils/formatDateTime'
import { getEventUrl } from '@utils/slug'
import { useI18n } from '@composables/useI18n'
import { useEventStatus } from '@composables/useEventStatus'
import { useEventPrice } from '@composables/useEventPrice'
import { usePresetImage } from '@composables/useImageOptimization'
import type { EventCardProps } from '@types'

const { t } = useI18n()

const props = defineProps<EventCardProps>()

// Use composables for reusable logic
const eventUrl = computed(() => {
  return getEventUrl(props.event.id, props.event.title || props.event.name)
})

const { statusLabel, eventStatus } = useEventStatus(computed(() => props.event))
const { priceRange } = useEventPrice(computed(() => props.event))
const { optimizedImageUrl } = usePresetImage(computed(() => props.event.image_url), 'card')

const isFavorite = ref(false)

const toggleFavorite = () => {
  isFavorite.value = !isFavorite.value
}
</script>

<style scoped>
@reference "tailwindcss";

.event-card {
  @apply relative overflow-hidden rounded-xl cursor-pointer;
  @apply transition-all duration-500 ease-out;
  aspect-ratio: 4 / 5;
  box-shadow:
    0 2px 8px rgba(0, 0, 0, 0.1),
    0 4px 20px rgba(0, 0, 0, 0.08);
}

.event-card:hover {
  @apply -translate-y-1;
  box-shadow:
    0 12px 40px rgba(0, 0, 0, 0.2),
    0 4px 20px rgba(var(--color-primary-500), 0.2);
}

/* Full-size Background Image */
.card-background {
  @apply absolute inset-0;
}

.bg-image {
  @apply w-full h-full object-cover;
  @apply transition-transform duration-700 ease-out;
}

.group:hover .bg-image {
  @apply scale-110;
}

.placeholder-bg {
  @apply absolute inset-0;
  background: linear-gradient(
    135deg,
    rgba(var(--color-primary-600), 0.3) 0%,
    rgba(var(--color-primary-800), 0.5) 50%,
    rgba(var(--color-primary-900), 0.7) 100%
  );
}

.placeholder-pattern {
  @apply absolute inset-0 opacity-20;
  background-image: radial-gradient(
    rgba(255, 255, 255, 0.3) 1px,
    transparent 1px
  );
  background-size: 20px 20px;
}

.image-overlay {
  @apply absolute inset-0;
  @apply transition-all duration-500;
  background: linear-gradient(
    180deg,
    rgba(0, 0, 0, 0.1) 0%,
    rgba(0, 0, 0, 0.05) 30%,
    rgba(0, 0, 0, 0.4) 70%,
    rgba(0, 0, 0, 0.85) 100%
  );
}

.group:hover .image-overlay {
  background: linear-gradient(
    180deg,
    rgba(84, 0, 180, 0.1) 0%,
    rgba(84, 0, 180, 0.05) 30%,
    rgba(72, 0, 160, 0.5) 70%,
    rgba(18, 0, 46, 0.9) 100%
  );
}

/* Badges (top left) */
.badges-container {
  @apply absolute top-3 left-3 flex items-center gap-2 z-10;
}

.status-badge {
  @apply text-xs backdrop-blur-md shadow-lg;
}

.tickets-pill {
  @apply flex items-center gap-1 px-2.5 py-1 rounded-full;
  @apply text-xs font-semibold;
  @apply bg-white/90 backdrop-blur-md;
  @apply text-gray-800;
  @apply shadow-lg;
}

.tickets-icon {
  @apply w-3.5 h-3.5;
  color: rgb(var(--color-primary-500));
}

.tickets-count {
  @apply tabular-nums;
}

/* Favorite button (top right) */
.favorite-btn {
  @apply absolute top-3 right-3 z-10;
  @apply flex items-center justify-center w-9 h-9 rounded-full;
  @apply bg-white/90 backdrop-blur-md;
  @apply transition-all duration-300 shadow-lg;
}

.favorite-btn:hover {
  @apply bg-white scale-110;
}

.favorite-icon {
  @apply w-5 h-5 text-gray-500;
  @apply transition-all duration-300;
}

.favorite-btn:hover .favorite-icon {
  @apply text-red-500;
}

.favorite-icon.text-red-500 {
  @apply text-red-500;
  animation: heartPop 0.4s ease-out;
}

@keyframes heartPop {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

/* Price tag */
.price-tag {
  @apply flex items-baseline gap-1 px-2.5 py-1 rounded-full;
  @apply bg-white/90 backdrop-blur-md shadow-lg;
}

.price-from {
  @apply text-[10px] uppercase tracking-wide font-medium;
  @apply text-gray-500;
}

.price-amount {
  @apply text-xs font-bold;
  @apply text-gray-800;
}

/* Content Section (overlaid at bottom) */
.card-content {
  @apply absolute bottom-0 left-0 right-0 z-10;
  @apply flex flex-col p-4 gap-2;
}

.event-title {
  @apply text-lg font-bold leading-tight line-clamp-2;
  @apply text-white;
  @apply drop-shadow-lg;
  @apply transition-colors duration-300;
}

.group:hover .event-title {
  @apply text-white;
}

/* Meta row */
.meta-row {
  @apply flex flex-wrap gap-x-3 gap-y-1;
}

.meta-item {
  @apply flex items-center gap-1.5;
}

.meta-icon {
  @apply w-3.5 h-3.5 text-gray-300;
}

.meta-text {
  @apply text-xs text-gray-200 line-clamp-1;
}

/* CTA Row */
.cta-row {
  @apply flex items-center justify-between pt-2 mt-1;
  @apply border-t border-white/20;
}

.cta-text {
  @apply text-xs font-semibold uppercase tracking-wide;
  @apply text-white/70;
  @apply opacity-0 -translate-x-2;
  @apply transition-all duration-300;
}

.group:hover .cta-text {
  @apply opacity-100 translate-x-0 text-white;
}

.cta-arrow {
  @apply flex items-center justify-center w-8 h-8 rounded-full;
  @apply transition-all duration-300;
  @apply bg-white/20 backdrop-blur-sm;
  @apply border border-white/30;
}

.group:hover .cta-arrow {
  @apply translate-x-1.5 border-transparent;
  background-color: rgb(var(--color-primary-500));
  box-shadow: 0 4px 15px rgba(var(--color-primary-500), 0.5);
}

.arrow-icon {
  @apply w-4 h-4 text-white;
  @apply transition-all duration-300;
}

/* Hover glow */
.hover-glow {
  @apply absolute -inset-px rounded-xl pointer-events-none;
  @apply opacity-0 transition-opacity duration-500;
  background: linear-gradient(
    135deg,
    rgba(var(--color-primary-500), 0.2),
    transparent 40%,
    transparent 60%,
    rgba(var(--color-primary-400), 0.15)
  );
}

.group:hover .hover-glow {
  @apply opacity-100;
}
</style>
