<template>
  <div class="tickets-card">
    <div class="tickets-header">
      <h2 class="tickets-title">
        <UIcon
          name="i-heroicons-ticket-20-solid"
          class="section-icon"
        />
        {{ t('events.ticketTypes') }}
      </h2>
      <div
        v-if="priceRange"
        class="price-range"
      >
        <ClientOnly fallback="from">
          {{ t('events.from') }}
        </ClientOnly>
        <span class="price-amount">{{ priceRange }}</span>
      </div>
    </div>

    <div v-if="ticketTypes && ticketTypes.length">
      <TicketSelector
        :tickets="ticketTypes"
        :event-id="eventId"
        :event-title="eventTitle"
        @added="$emit('added', $event)"
        @ticket-selected="$emit('ticket-selected', $event)"
      />
    </div>
    <div
      v-else
      class="no-tickets"
    >
      <UIcon
        name="i-heroicons-ticket"
        class="w-12 h-12 text-gray-300 mb-3"
      />
      <p>No ticket information available.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UIcon } from '#components'
import { useI18n } from '@composables/useI18n'
import TicketSelector from '@components/events/TicketSelector.vue'
import type { Event } from '@types'

defineProps<{
  ticketTypes?: Event['ticket_types']
  eventId: number | string
  eventTitle: string
  priceRange: string | null
}>()

defineEmits<{
  added: [item: {
    eventId: number | string
    ticketTypeId: number | string
    title: string
    ticketName: string
    price: number
    quantity: number
  }]
  'ticket-selected': [ticketId: number]
}>()

const { t } = useI18n()
</script>

<style scoped>
@reference "tailwindcss";

.tickets-card {
  @apply bg-white rounded-2xl;
  @apply p-6 shadow-xl;
  @apply border border-gray-100;
  @apply sticky top-4;
}

.tickets-header {
  @apply flex items-start justify-between mb-4;
}

.tickets-title {
  @apply flex items-center gap-2 text-xl font-bold;
  @apply text-gray-900;
}

.section-icon {
  @apply w-5 h-5;
  color: rgb(var(--color-primary-500));
}

.price-range {
  @apply text-sm text-gray-500;
}

.price-amount {
  @apply font-bold text-lg ml-1;
  color: rgb(var(--color-primary-500));
}

.no-tickets {
  @apply flex flex-col items-center justify-center py-8 text-center;
  @apply text-gray-500;
}
</style>
