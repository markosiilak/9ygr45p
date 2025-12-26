<template>
  <div class="shopping-cart-wrapper">
    <CartButton
      :item-count="totalItems"
      @toggle="toggleCart"
    />

    <CartPanel
      :is-open="showCart"
      :items="items"
      :item-count="totalItems"
      :total="cartTotal"
      @close="showCart = false"
      @remove="removeItem"
      @clear="clearAllItems"
      @checkout="goToCheckout"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import CartButton from '@components/cart/CartButton.vue'
import CartPanel from '@components/cart/CartPanel.vue'
import { useCart } from '@composables/useCart'
import type { CartItem } from '@types'

const { items, removeFromCart, clearCart, total } = useCart()
const showCart = ref(false)

const totalItems = computed(() => {
  return items.value.reduce((sum, item) => sum + item.quantity, 0)
})

const cartTotal = computed(() => total())

const toggleCart = () => {
  showCart.value = !showCart.value
}

const removeItem = (item: CartItem) => {
  removeFromCart(item.ticketTypeId, item.eventId)
}

const clearAllItems = () => {
  if (confirm('Are you sure you want to clear your cart?')) {
    clearCart()
    showCart.value = false
  }
}

const goToCheckout = () => {
  showCart.value = false
  navigateTo('/checkout')
}

// Close cart when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (showCart.value && !target.closest('.shopping-cart-wrapper')) {
    showCart.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
@reference "tailwindcss";

.shopping-cart-wrapper {
  @apply relative;
}
</style>
