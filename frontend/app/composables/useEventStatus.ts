import { computed, type ComputedRef } from 'vue'
import type { EventStatus } from '@types'

/**
 * Composable for event status logic
 * Provides consistent event status calculation and labeling across components
 */
export function useEventStatus(event: ComputedRef<{ tickets_available?: number; status?: EventStatus } | undefined>) {
  const eventStatus = computed((): EventStatus | null => {
    if (event.value?.tickets_available === 0) return 'sold-out'
    return event.value?.status || null
  })

  const statusLabel = computed((): string => {
    switch (eventStatus.value) {
      case 'sold-out':
        return 'Sold Out'
      case 'active':
        return 'Active'
      case 'upcoming':
        return 'Upcoming'
      default:
        return ''
    }
  })

  const statusColor = computed((): string => {
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

  return {
    eventStatus,
    statusLabel,
    statusColor
  }
}
