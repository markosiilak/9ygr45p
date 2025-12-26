<template>
  <div class="swiper-container">
    <Swiper
      :modules="[Pagination, Navigation, EffectFade, Autoplay]"
      :slides-per-view="1"
      :pagination="{ clickable: true }"
      :navigation="true"
      :effect="'fade'"
      :fade-effect="{ crossFade: true }"
      :autoplay="{ delay: 5000, disableOnInteraction: false }"
      :loop="true"
      class="swiper-hover-arrows"
    >
      <SwiperSlide
        v-for="(item, idx) in slides"
        :key="idx"
      >
        <NuxtLink
          :to="getSlideUrl(item.props)"
          class="slide-wrapper"
        >
          <div
            v-if="item.props && item.props.image && getOptimizedUrl(item.props.image as string)"
            class="hero-image-container"
          >
            <NuxtImg
              :src="getOptimizedUrl(item.props?.image as string)"
              sizes="100vw"
              :alt="(item.props?.title as string) || 'Hero image'"
              class="hero-image"
              loading="lazy"
            />
            <div class="hero-overlay" />
            <div class="hero-vignette" />
            <div class="hero-grain" />
          </div>
          <div class="hero-content">
            <component
              :is="item.component"
              v-bind="item.props"
            />
          </div>
        </NuxtLink>
      </SwiperSlide>
    </Swiper>
  </div>
</template>

<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination, Navigation, EffectFade, Autoplay } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'
import 'swiper/css/effect-fade'
import { getOptimizedImageUrl } from '@utils/imageUrl'
import { getEventUrl } from '@utils/slug'

import type { Component } from 'vue'

type Slide = {
  component: Component
  props?: Record<string, unknown>
}

defineProps<{ slides: Slide[] }>()

// Helper functions for optimized images (hero needs larger sizes)
const getOptimizedUrl = (url: string) => getOptimizedImageUrl(url, 1600)

// Generate SEO-friendly URL for slide
const getSlideUrl = (props?: Record<string, unknown>) => {
  if (!props?.id) return '#'
  const id = props.id as number | string
  const title = props.title as string | undefined
  return getEventUrl(id, title)
}
</script>

<style scoped>
@reference "tailwindcss";

.swiper-container {
  @apply relative overflow-hidden w-full rounded-2xl;
}

.swiper-hover-arrows {
  @apply [&_.swiper-button-next]:opacity-0 [&_.swiper-button-prev]:opacity-0 [&_.swiper-button-next]:transition-opacity [&_.swiper-button-prev]:transition-opacity hover:[&_.swiper-button-next]:opacity-100 hover:[&_.swiper-button-prev]:opacity-100;
  @apply overflow-hidden;
}

/* Keep arrows inside container */
.swiper-hover-arrows :deep(.swiper-button-prev),
.swiper-hover-arrows :deep(.swiper-button-next) {
  @apply left-4 right-auto;
  @apply transition-all duration-300;
}

.swiper-hover-arrows :deep(.swiper-button-prev),
.swiper-hover-arrows :deep(.swiper-button-next) {
  --swiper-navigation-color: var(--color-green-500);
}

.swiper-hover-arrows :deep(.swiper-button-next) {
  @apply left-auto right-4;
}

/* Pagination bullets */
.swiper-hover-arrows :deep(.swiper-pagination-bullet) {
  --swiper-pagination-color: var(--color-green-500);
  --swiper-pagination-bullet-inactive-color: var(--color-green-300);
  --swiper-pagination-bullet-inactive-opacity: 0.5;
}

.slide-wrapper {
  @apply relative block cursor-pointer;
}

.hero-image-container {
  @apply w-full overflow-hidden relative h-[60vh] min-h-[400px] max-h-[700px];
}

.hero-image {
  @apply w-full h-full object-cover block;
  @apply transition-transform duration-[8000ms] ease-out;
  animation: slowZoom 12s ease-out infinite alternate;
}

@keyframes slowZoom {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(1.08);
  }
}

/* Gradient overlay for text readability */
.hero-overlay {
  @apply absolute inset-0 pointer-events-none;
  @apply bg-gradient-to-b from-white/30 via-white/70 to-white/85;
}

/* Vignette effect */
.hero-vignette {
  @apply absolute inset-0 pointer-events-none;
  @apply bg-[radial-gradient(ellipse_at_center,transparent_40%,rgba(0,0,0,0.15)_100%)];
}

/* Subtle grain texture */
.hero-grain {
  @apply absolute inset-0 pointer-events-none opacity-[0.03];
  @apply bg-[url("data:image/svg+xml,%3Csvg_viewBox='0_0_256_256'_xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter_id='noise'%3E%3CfeTurbulence_type='fractalNoise'_baseFrequency='0.8'_numOctaves='4'_stitchTiles='stitch'/%3E%3C/filter%3E%3Crect_width='100%25'_height='100%25'_filter='url(%23noise)'/%3E%3C/svg%3E")];
}

.hero-content {
  @apply absolute inset-0 flex items-center justify-center pointer-events-none;
  @apply px-8 sm:px-12 md:px-16 lg:px-24 text-black;
}

.hero-content :deep(> *) {
  @apply max-w-2xl mx-auto;
}
</style>
