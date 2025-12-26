import { computed, type ComputedRef } from 'vue'
import formatPrice from '@utils/formatPrice'
import type { TicketType } from '@types'

/**
 * Composable for event price calculations
 * Provides consistent price range calculations across components
 */
export function useEventPrice(event: ComputedRef<{ ticket_types?: TicketType[] } | undefined>) {
  const priceRange = computed(() => {
    if (!event.value?.ticket_types || event.value.ticket_types.length === 0) {
      return null
    }

    const prices = event.value.ticket_types.map(t => t.price)
    const minPrice = Math.min(...prices)

    return formatPrice({ price: minPrice })
  })

  const totalPrice = computed(() => {
    if (!event.value?.ticket_types || event.value.ticket_types.length === 0) {
      return null
    }

    const total = event.value.ticket_types.reduce((sum, ticket) => sum + ticket.price, 0)
    return formatPrice({ price: total })
  })

  const minMaxPrice = computed(() => {
    if (!event.value?.ticket_types || event.value.ticket_types.length === 0) {
      return null
    }

    const prices = event.value.ticket_types.map(t => t.price)
    const minPrice = Math.min(...prices)
    const maxPrice = Math.max(...prices)

    if (minPrice === maxPrice) {
      return formatPrice({ price: minPrice })
    }

    return `${formatPrice({ price: minPrice })} - ${formatPrice({ price: maxPrice })}`
  })

  return {
    priceRange,
    totalPrice,
    minMaxPrice
  }
}
