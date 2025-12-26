<template>
  <div class="event-page">
    <AppLoading v-if="loading" />

    <UAlert
      v-else-if="error"
      color="error"
      variant="soft"
      class="mx-4 mt-4"
    >
      {{ t('errors.serverError') }}: {{ error }}
    </UAlert>

    <template v-if="event">
      <!-- Hero Section -->
      <EventHero
        :event="event"
        :event-status="eventStatus"
        :status-label="statusLabel"
        :status-color="statusColor"
      />

      <!-- Main Content -->
      <div class="main-content">
        <div class="container mx-auto">
          <div class="content-grid">
            <!-- Left Column: Description & Details -->
            <div class="description-column">
              <EventDescription :description="event.description" />
              <EventDetails
                :organizer="event.organizer"
                :categories="event.categories"
              />
            </div>

            <!-- Right Column: Tickets -->
            <div class="tickets-column">
              <EventTickets
                :ticket-types="event.ticket_types"
                :event-id="event.id"
                :event-title="event.title || event.name || 'Event'"
                :price-range="priceRange"
                @added="handleAddToCart"
                @ticket-selected="handleTicketSelected"
              />

              <!-- Mobile CTA -->
              <EventMobileCTA
                v-if="!hasSelectedTicket"
                :price-range="priceRange"
                @buy-tickets="scrollToTickets"
              />
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, watchEffect, computed, onMounted } from 'vue'
import { UAlert } from '#components'
import formatPrice from '@utils/formatPrice'
import { getEventUrl } from '@utils/slug'
import AppLoading from '@components/layout/AppLoading.vue'
import EventHero from '@components/events/EventHero.vue'
import EventDescription from '@components/events/EventDescription.vue'
import EventDetails from '@components/events/EventDetails.vue'
import EventTickets from '@components/events/EventTickets.vue'
import EventMobileCTA from '@components/events/EventMobileCTA.vue'
import { useI18n } from '@composables/useI18n'
import type { Event } from '@types'
import { navigateTo, useFetch, useHead, useRoute } from 'nuxt/app'
import useCart from '@composables/useCart'
import { useAuth } from '@composables/useAuth'
import { useEventSEO } from '@composables/useEventSEO'

const { t } = useI18n()

// route + state
const route = useRoute()
// Extract ID from route params (handles both /events/33 and /events/33-slug)
const idParam = route.params.id as string
const id = idParam.split('-')[0] // Extract just the numeric ID

const { user, token, fetchUser } = useAuth()

const event = ref<Event | null>(null)
const loading = ref(true)
const error = ref('')
const userLoading = ref<boolean>(false)
const hasSelectedTicket = ref(false)

// Cart state (uses Nuxt auto-imported composable)
const cart = useCart()

const handleAddToCart = (item: {
  eventId: number | string
  ticketTypeId: number | string
  title: string
  ticketName: string
  price: number
  quantity: number
}) => {
  cart.addToCart(item)
}

const handleTicketSelected = (ticketId: number) => {
  hasSelectedTicket.value = true
}

// Scroll to tickets section
const scrollToTickets = () => {
  document.querySelector('.tickets-card')?.scrollIntoView({ behavior: 'smooth' })
}

// Fetch user on mount if we have a token but no user data
onMounted(async () => {
  if (token.value && !user.value) {
    userLoading.value = true
    try {
      await fetchUser()
    } catch (error) {
      console.error('Event detail - Failed to fetch user:', error)
    } finally {
      userLoading.value = false
    }
  }
})

// Event status computations
const eventStatus = computed(() => {
  if (event.value?.tickets_available === 0) return 'sold-out'
  return event.value?.status || null
})

const statusLabel = computed(() => {
  switch (eventStatus.value) {
    case 'sold-out':
      return t('events.soldOut')
    case 'active':
      return t('events.active')
    case 'upcoming':
      return t('events.upcoming')
    default:
      return ''
  }
})

const statusColor = computed(() => {
  switch (eventStatus.value) {
    case 'sold-out':
      return 'error'
    case 'active':
      return 'success'
    case 'upcoming':
      return 'warning'
    default:
      return 'neutral'
  }
})

const priceRange = computed(() => {
  if (!event.value?.ticket_types || event.value.ticket_types.length === 0) {
    return null
  }
  const prices = event.value.ticket_types.map(t => t.price)
  const minPrice = Math.min(...prices)
  return formatPrice({ price: minPrice })
})

// Use a getter so the fetch URL is evaluated when `id` is available/reactive
const { data, pending, error: fetchError } = useFetch(() => `/api/events/${String(id)}`)

// Setup comprehensive SEO meta tags
const { setupEventSEO } = useEventSEO({
  event: computed(() => event.value),
  eventId: id,
  eventStatus,
  siteName: 'PiletiTasku'
})

setupEventSEO()

// Redirect to SEO-friendly URL if slug is missing
watchEffect(() => {
  if (event.value && route.params.slug === undefined && id) {
    const title = event.value.title || event.value.name
    if (title) {
      const slugUrl = getEventUrl(id, title)
      if (route.path !== slugUrl) {
        navigateTo(slugUrl, { redirectCode: 301 })
      }
    }
  }
})

watchEffect(() => {
  if (data.value) event.value = data.value as Event
  loading.value = !!pending.value
  if (fetchError.value) {
    const err = fetchError.value
    if (typeof err === 'string') error.value = err
    else if (err && typeof err === 'object' && 'message' in err) error.value = (err as { message?: string }).message || String(err)
    else error.value = String(err)
  }
})
</script>

<style scoped>
@reference "tailwindcss";

.event-page {
  @apply min-h-screen bg-gray-50;
}

.main-content {
  @apply py-8 -mt-4 relative z-10;
}

.content-grid {
  @apply grid lg:grid-cols-3 gap-6;
}

.description-column {
  @apply lg:col-span-2 space-y-6;
}

.tickets-column {
  @apply lg:col-span-1;
}
</style>
