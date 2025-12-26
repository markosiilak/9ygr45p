<template>
  <div class="cart-item">
    <div class="item-info">
      <div class="item-title">
        {{ item.title }}
      </div>
      <div class="item-ticket">
        {{ item.ticketName }}
      </div>
      <div class="item-quantity">
        {{ t('cart.quantity') }}: {{ item.quantity }}
      </div>
    </div>
    <div class="item-actions">
      <div class="item-price">
        {{ formatPrice({ price: item.price * item.quantity }) }}
      </div>
      <UButton
        variant="ghost"
        color="neutral"
        class="remove-button"
        @click="$emit('remove', item)"
      >
        <UIcon
          name="i-heroicons-trash-20-solid"
          class="w-4 h-4"
        />
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UButton, UIcon } from '#components'
import { useI18n } from '@composables/useI18n'
import formatPrice from '@utils/formatPrice'
import type { CartItem } from '@types'

const { t } = useI18n()

defineProps<{
  item: CartItem
}>()

defineEmits<{
  remove: [item: CartItem]
}>()
</script>

<style scoped>
@reference "tailwindcss";

.cart-item {
  @apply flex items-start justify-between gap-3;
  @apply p-4;
  @apply hover:bg-gray-50;
  @apply transition-colors;
}

.item-info {
  @apply flex-1 min-w-0;
}

.item-title {
  @apply text-sm font-semibold text-gray-900;
  @apply truncate;
}

.item-ticket {
  @apply text-xs text-gray-600;
  @apply mt-0.5;
}

.item-quantity {
  @apply text-xs text-gray-500;
  @apply mt-1;
}

.item-actions {
  @apply flex flex-col items-end gap-2;
}

.item-price {
  @apply text-sm font-bold;
  color: rgb(var(--color-primary-600));
}

.remove-button {
  @apply p-1 rounded-lg;
  @apply text-gray-400 hover:text-red-500;
  @apply hover:bg-red-50;
  @apply transition-colors;
}
</style>
