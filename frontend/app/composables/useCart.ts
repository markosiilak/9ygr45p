import { ref } from 'vue'
import type { CartItem } from '@types'

const STORAGE_KEY = 'ticket_cart'

export const useCart = () => {
  const stored = import.meta.client ? localStorage.getItem(STORAGE_KEY) : null
  const initial = stored ? JSON.parse(stored) : []
  const items = ref<CartItem[]>(initial)

  const persist = () => {
    if (import.meta.client) localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
  }

  const addToCart = (item: CartItem) => {
    const existing = items.value.find(i => i.eventId === item.eventId && i.ticketTypeId === item.ticketTypeId)
    if (existing) {
      existing.quantity += item.quantity
    } else {
      items.value.push({ ...item })
    }
    persist()
  }

  const removeFromCart = (ticketTypeId: number | string, eventId: number | string) => {
    items.value = items.value.filter(i => !(i.ticketTypeId === ticketTypeId && i.eventId === eventId))
    persist()
  }

  const clearCart = () => {
    items.value = []
    persist()
  }

  const total = () => items.value.reduce((s, i) => s + i.price * i.quantity, 0)

  return {
    items,
    addToCart,
    removeFromCart,
    clearCart,
    total
  }
}

export default useCart
