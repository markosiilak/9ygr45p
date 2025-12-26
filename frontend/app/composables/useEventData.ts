import { computed, type ComputedRef } from 'vue'
import type { Event, EventStatus } from '@types'

/**
 * Composable for common event data transformations
 * Handles event array extraction and sorting logic
 */
export function useEventData(eventsData: ComputedRef<Event[] | { data: Event[] } | undefined>) {
  // Extract events array (handle both direct array and wrapped response)
  const allEvents = computed(() => {
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

  // Sort events by tickets available (most popular first)
  const eventsByPopularity = computed(() => {
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

    return events
  })

  // Get top N events by popularity
  const topPopularEvents = (count: number) => computed(() => {
    return eventsByPopularity.value.slice(0, count)
  })

  // Filter events by status
  const eventsByStatus = (status: EventStatus) => computed(() => {
    return allEvents.value.filter(event => event.status === status)
  })

  // Get events with available tickets
  const eventsWithTickets = computed(() => {
    return allEvents.value.filter(event =>
      event.tickets_available !== undefined &&
      event.tickets_available !== null &&
      event.tickets_available > 0
    )
  })

  return {
    allEvents,
    eventsByPopularity,
    topPopularEvents,
    eventsByStatus,
    eventsWithTickets
  }
}
