<template>
  <div class="home-page">
    <div class="main-layout">
      <section class="hero-section p-2">
        <HeroSwiper :slides="heroSlides" />
      </section>
      <FeaturedEventsSidebar :events="featuredEvents" />
    </div>

    <section class="events-section">
      <EventsList :events="ssrEvents" />
    </section>
  </div>
</template>

<script setup lang="ts">
import { resolveComponent, computed, type Component } from 'vue'
import HeroSwiper from '@components/events/HeroSwiper.vue'
import EventsList from '@components/events/EventsList.vue'
import FeaturedEventsSidebar from '@components/events/FeaturedEventsSidebar.vue'
import type { HeroEvent, Event } from '@types'
import { useFetch } from 'nuxt/app'

// resolve auto-imported Nuxt UI component at runtime
const UPageHero = resolveComponent('UPageHero') as Component

// Fetch hero events server-side from our Nitro endpoint and map to slides
const { data: heroData } = useFetch<HeroEvent[]>('/api/hero-events')
const heroSlides = computed(() => {
  const events = (heroData.value || []) as HeroEvent[]
  return events.map((ev: HeroEvent) => ({
    component: UPageHero as Component,
    props: {
      id: ev.id,
      title: ev.title || ev.name,
      description: ev.description || ev.summary || '',
      image: ev.image_url || ev.image || undefined,
      links: []
    }
  }))
})

// Fetch events server-side and pass into EventsList so markup is SSR-rendered
const { data: eventsData } = useFetch<Event[] | { data: Event[] }>('/api/events')

// Extract events array (handle both direct array and wrapped response)
const allEvents = computed<Event[]>(() => {
  const data = eventsData.value
  if (!data) return []
  // Handle Laravel's wrapped response { data: [...] }
  if (data && typeof data === 'object' && 'data' in data && Array.isArray(data.data)) {
    return data.data as Event[]
  }
  // Handle direct array response
  if (Array.isArray(data)) {
    return data as Event[]
  }
  return []
})

// SSR events for EventsList component
const ssrEvents = computed<Event[]>(() => allEvents.value)

// Featured events for sidebar (most popular = least tickets available)
const featuredEvents = computed<Event[]>(() => {
  const events = [...allEvents.value]

  // Sort by tickets_available (ascending - fewer tickets = more popular)
  // Events with undefined/null tickets_available go to the end
  events.sort((a, b) => {
    const aTickets = a.tickets_available ?? Infinity
    const bTickets = b.tickets_available ?? Infinity

    // If both are Infinity (no tickets_available), keep original order
    if (aTickets === Infinity && bTickets === Infinity) return 0

    return aTickets - bTickets
  })

  // Return top 3 most popular events (least tickets available)
  return events.slice(0, 3)
})
</script>

<style scoped>
@reference "tailwindcss";

.home-page {
  @apply flex flex-col w-full;
}

.main-layout {
  @apply flex flex-col lg:flex-row w-full;
  min-height: 60vh;
}

.hero-section {
  @apply w-full lg:w-2/3;
}

@media (max-width: 1023px) {
  .hero-section {
    @apply w-full;
  }
}

.events-section {
  @apply flex-1 w-full py-8;
}
</style>
