<template>
  <div>
    <!-- Backdrop Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="cart-backdrop"
        @click="$emit('close')"
      />
    </Transition>

    <!-- Cart Sidebar Panel -->
    <Transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-x-full"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="isOpen"
        class="cart-panel"
        @click.stop
      >
        <!-- Header -->
        <div class="cart-header">
          <h3 class="cart-title">
            <UIcon
              name="i-heroicons-shopping-cart-20-solid"
              class="w-5 h-5"
            />
            {{ t('cart.title') }}
            <span
              v-if="itemCount > 0"
              class="cart-count"
            >({{ itemCount }})</span>
          </h3>
          <UButton
            variant="ghost"
            color="neutral"
            class="close-button"
            @click="$emit('close')"
          >
            <UIcon
              name="i-heroicons-x-mark-20-solid"
              class="w-5 h-5"
            />
          </UButton>
        </div>

        <!-- Content -->
        <div
          v-if="itemCount > 0"
          class="cart-content"
        >
          <div class="cart-items">
            <CartItem
              v-for="item in items"
              :key="`${item.eventId}-${item.ticketTypeId}`"
              :item="item"
              @remove="$emit('remove', $event)"
            />
          </div>

          <!-- Footer -->
          <div class="cart-footer">
            <div class="cart-total">
              <span class="total-label">{{ t('cart.total') }}:</span>
              <span class="total-amount">{{ formatPrice({ price: total }) }}</span>
            </div>
            <div class="cart-actions">
              <UButton
                color="neutral"
                variant="ghost"
                size="sm"
                @click="$emit('clear')"
              >
                {{ t('cart.clearCart') }}
              </UButton>
              <UButton
                color="primary"
                size="sm"
                @click="$emit('checkout')"
              >
                {{ t('cart.checkout') }}
              </UButton>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="cart-content"
        >
          <CartEmpty @close="$emit('close')" />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { UButton, UIcon } from '#components'
import { useI18n } from '@composables/useI18n'
import CartItem from './CartItem.vue'
import CartEmpty from './CartEmpty.vue'
import formatPrice from '@utils/formatPrice'
import type { CartItem as CartItemType } from '@types'

const { t } = useI18n()

defineProps<{
  isOpen: boolean
  items: CartItemType[]
  itemCount: number
  total: number
}>()

defineEmits<{
  close: []
  remove: [item: CartItemType]
  clear: []
  checkout: []
}>()
</script>

<style scoped>
@reference "tailwindcss";

.cart-backdrop {
  @apply fixed inset-0 bg-black/50;
  @apply z-[9998];
  backdrop-filter: blur(2px);
}

.cart-panel {
  @apply fixed right-0 top-0 bottom-0;
  @apply w-full sm:w-[450px];
  @apply bg-white shadow-2xl;
  @apply z-[9999];
  @apply flex flex-col;
  @apply h-screen;
  @apply max-h-screen;
}

.cart-header {
  @apply flex items-center justify-between;
  @apply px-6 py-4 border-b border-gray-200;
  @apply bg-white;
  @apply shrink-0;
}

.cart-title {
  @apply text-lg font-bold text-gray-900;
  @apply flex items-center gap-2;
}

.cart-count {
  @apply text-sm font-medium text-gray-500;
}

.close-button {
  @apply p-2 rounded-lg;
  @apply text-gray-400 hover:text-gray-600;
  @apply hover:bg-gray-100;
  @apply transition-colors;
}

.cart-content {
  @apply flex-1 overflow-hidden;
  @apply flex flex-col;
  @apply min-h-0;
}

.cart-items {
  @apply flex-1 overflow-y-auto;
  @apply divide-y divide-gray-100;
  @apply px-4;
}

.cart-footer {
  @apply px-6 py-4 border-t border-gray-200;
  @apply bg-gray-50;
  @apply space-y-4;
  @apply shrink-0;
}

.cart-total {
  @apply flex items-center justify-between;
}

.total-label {
  @apply text-sm font-medium text-gray-600;
}

.total-amount {
  @apply text-xl font-bold text-gray-900;
}

.cart-actions {
  @apply flex items-center gap-2;
}
</style>
