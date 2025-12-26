<template>
  <div class="ticket-selector-container">
    <div
      v-if="showLabel"
      class="selector-header"
    >
      <UIcon
        name="i-heroicons-ticket-20-solid"
        class="header-icon"
      />
      <label class="header-label">Select ticket type</label>
    </div>
    <div class="tickets-grid">
      <UButton
        v-for="ticket in tickets"
        :key="ticket.id"
        variant="ghost"
        color="neutral"
        :aria-pressed="selectedTicketId === ticket.id"
        :class="[
          'ticket-option',
          selectedTicketId === ticket.id ? 'ticket-option-selected' : ''
        ]"
        @click="handleTicketSelect(ticket.id)"
      >
        <div class="ticket-content">
          <div class="ticket-info">
            <div
              :class="[
                'ticket-icon',
                selectedTicketId === ticket.id ? 'ticket-icon-selected' : 'ticket-icon-default'
              ]"
            >
              <UIcon
                name="i-heroicons-ticket-20-solid"
                class="ticket-icon-svg"
              />
            </div>
            <div class="ticket-details">
              <div
                :class="[
                  'ticket-name',
                  selectedTicketId === ticket.id ? 'ticket-name-selected' : 'ticket-name-default'
                ]"
              >
                {{ ticket.name }}
              </div>
              <div
                v-if="ticket.description"
                class="ticket-description"
              >
                {{ ticket.description }}
              </div>
            </div>
          </div>
          <div class="ticket-price-section">
            <div
              :class="[
                'ticket-price',
                selectedTicketId === ticket.id ? 'ticket-price-selected' : 'ticket-price-default'
              ]"
            >
              {{ formatPrice(ticket) }}
            </div>
          </div>
        </div>
      </UButton>
    </div>

    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="transform -translate-y-2.5 opacity-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-to-class="transform -translate-y-2.5 opacity-0"
      mode="out-in"
    >
      <div
        v-if="selectedTicketId"
        class="selected-ticket-section"
      >
        <div class="quantity-header">
          <label class="quantity-label">
            <UIcon
              name="i-heroicons-hashtag-20-solid"
              class="quantity-label-icon"
            />
            Quantity
          </label>
          <span class="quantity-max">Max: {{ maxQuantity || '∞' }}</span>
        </div>
        <div class="quantity-controls">
          <UButton
            variant="ghost"
            color="neutral"
            :disabled="localQuantity <= 1"
            class="quantity-btn"
            @click="decreaseQuantity"
          >
            <UIcon
              name="i-heroicons-minus-20-solid"
              class="quantity-btn-icon"
            />
          </UButton>
          <input
            v-model.number="localQuantity"
            type="number"
            min="1"
            :max="maxQuantity"
            class="quantity-input"
            @input="handleQuantityChange"
          >
          <UButton
            variant="ghost"
            color="neutral"
            :disabled="maxQuantity ? localQuantity >= maxQuantity : false"
            class="quantity-btn"
            @click="increaseQuantity"
          >
            <UIcon
              name="i-heroicons-plus-20-solid"
              class="quantity-btn-icon"
            />
          </UButton>
        </div>
        <div class="total-section">
          <span class="total-label">Total:</span>
          <span class="total-amount">{{ formatTotalPrice }}</span>
        </div>
        <UButton
          type="button"
          :loading="adding"
          block
          size="lg"
          class="ticket-add-button"

          @click="handleAddToCart"
        >
          {{ adding ? 'Adding to cart...' : 'Add to cart' }}
        </UButton>
        <Transition
          enter-active-class="transition-opacity duration-200"
          enter-from-class="opacity-0"
          leave-active-class="transition-opacity duration-200"
          leave-to-class="opacity-0"
          mode="out-in"
        >
          <UAlert
            v-if="message"
            :color="messageType === 'success' ? 'success' : 'error'"
            variant="soft"
            :icon="messageType === 'success' ? '' : ''"
            :title="message"
          />
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import formatPrice from '@utils/formatPrice'
import type { TicketSelectorProps } from '@types'

const props = withDefaults(defineProps<TicketSelectorProps>(), {
  showLabel: true,
  maxQuantity: undefined
})

const emit = defineEmits<{
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

const selectedTicketId = ref<number | null>(null)
const localQuantity = ref<number>(1)
const adding = ref(false)
const message = ref('')
const messageType = ref<'success' | 'error'>('success')

const handleTicketSelect = (ticketId: number) => {
  selectedTicketId.value = ticketId
  localQuantity.value = 1
  message.value = ''
  emit('ticket-selected', ticketId)
}

const selectedTicket = computed(() => {
  return props.tickets.find(t => t.id === selectedTicketId.value)
})

const formatTotalPrice = computed(() => {
  if (!selectedTicket.value) return '$0.00'
  const total = selectedTicket.value.price * localQuantity.value
  return formatPrice({ price: total })
})

const decreaseQuantity = () => {
  if (localQuantity.value > 1) {
    localQuantity.value--
    message.value = ''
  }
}

const increaseQuantity = () => {
  if (!props.maxQuantity || localQuantity.value < props.maxQuantity) {
    localQuantity.value++
    message.value = ''
  }
}

const handleQuantityChange = () => {
  if (localQuantity.value < 1) {
    localQuantity.value = 1
  }
  if (props.maxQuantity && localQuantity.value > props.maxQuantity) {
    localQuantity.value = props.maxQuantity
  }
  message.value = ''
}

const handleAddToCart = () => {
  if (!selectedTicketId.value) {
    message.value = 'Please select a ticket type.'
    messageType.value = 'error'
    setTimeout(() => {
      message.value = ''
    }, 3000)
    return
  }

  const ticket = props.tickets.find(t => t.id === selectedTicketId.value)
  if (!ticket) {
    message.value = 'Selected ticket type not found.'
    messageType.value = 'error'
    setTimeout(() => {
      message.value = ''
    }, 3000)
    return
  }

  const quantity = Math.max(1, Number(localQuantity.value) || 1)
  const item = {
    eventId: props.eventId,
    ticketTypeId: ticket.id,
    title: props.eventTitle,
    ticketName: ticket.name,
    price: ticket.price,
    quantity
  }

  adding.value = true
  message.value = ''

  // Simulate async operation
  setTimeout(() => {
    emit('added', item)
    message.value = `Added ${quantity} × ${ticket.name} to cart.`
    messageType.value = 'success'
    adding.value = false

    // Reset quantity but keep selection
    localQuantity.value = 1

    setTimeout(() => {
      message.value = ''
    }, 3000)
  }, 300)
}

// Watch for external changes to selectedTicketId
watch(() => props.tickets, () => {
  if (selectedTicketId.value) {
    const ticketExists = props.tickets.some(t => t.id === selectedTicketId.value)
    if (!ticketExists) {
      selectedTicketId.value = null
    }
  }
}, { deep: true })
</script>

<style scoped>
@reference "tailwindcss";

.ticket-selector-container {
  @apply space-y-6;
}

.selector-header {
  @apply flex items-center gap-3 mb-6 pb-3 border-b-2;
  border-image: linear-gradient(to right, rgba(var(--color-primary-500), 0.3), transparent) 1;
}

.header-icon {
  @apply w-6 h-6 transition-all duration-300;
  color: rgba(var(--color-primary-500), 1);
  filter: drop-shadow(0 0 8px rgba(var(--color-primary-500), 0.3));
  animation: pulse-glow 2s ease-in-out infinite;
}

.header-label {
  @apply text-lg font-bold text-gray-900;
  background: linear-gradient(to right, rgb(var(--color-primary-600)), rgb(var(--color-purple-600)));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

@keyframes pulse-glow {
  0%, 100% {
    filter: drop-shadow(0 0 8px rgba(var(--color-primary-500), 0.3));
  }
  50% {
    filter: drop-shadow(0 0 12px rgba(var(--color-primary-500), 0.6));
  }
}

.tickets-grid {
  @apply grid gap-3;
}

.ticket-option {
  @apply relative p-5 rounded-2xl border-2 text-left transition-all duration-300;
  @apply border-gray-200;
  @apply hover:shadow-2xl hover:-translate-y-1 cursor-pointer overflow-hidden;
  background-color: white;
  box-shadow: 0 0 0 0 transparent;
  transform-origin: center;
}

.ticket-option::before {
  content: '';
  @apply absolute inset-0 rounded-2xl opacity-0 transition-opacity duration-300;
  background: linear-gradient(135deg, rgba(var(--color-primary-500), 0.05), rgba(var(--color-purple-500), 0.05));
}

.ticket-option:hover::before {
  @apply opacity-100;
}

.ticket-option:hover {
  border-color: rgb(129 140 248);
  box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.2), 0 0 0 1px rgba(99, 102, 241, 0.1);
  transform: translateY(-4px) scale(1.01);
}

.ticket-option-selected {
  @apply shadow-2xl;
  border-color: rgb(79 70 229);
  background: linear-gradient(135deg, rgba(238, 242, 255, 0.8), rgba(238, 242, 255, 0.4));
  box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.3), 0 0 0 3px rgba(79, 70, 229, 0.1);
  transform: translateY(-2px) scale(1.02);
  animation: selected-pulse 2s ease-in-out infinite;
}

.ticket-option-selected::before {
  @apply opacity-100;
}

.ticket-option-selected::after {
  content: '';
  @apply absolute inset-0 rounded-2xl;
  background: linear-gradient(135deg, transparent, rgba(var(--color-primary-500), 0.1), transparent);
  animation: shimmer 3s ease-in-out infinite;
}

@keyframes icon-bounce {
  0% {
    transform: scale(0.8) rotate(-10deg);
  }
  50% {
    transform: scale(1.1) rotate(5deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
  }
}

@keyframes selected-pulse {
  0%, 100% {
    box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.3), 0 0 0 3px rgba(79, 70, 229, 0.1);
  }
  50% {
    box-shadow: 0 25px 30px -5px rgba(99, 102, 241, 0.4), 0 0 0 4px rgba(79, 70, 229, 0.2);
  }
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%) skewX(-15deg);
  }
  100% {
    transform: translateX(200%) skewX(-15deg);
  }
}

.ticket-content {
  @apply flex items-start justify-between gap-4;
}

.ticket-info {
  @apply flex items-start gap-3 flex-1;
}

.ticket-icon {
  @apply shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300;
}

.ticket-icon-selected {
  @apply shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300;
  background: linear-gradient(135deg, rgb(243 244 246), rgb(229 231 235));
  color: rgb(75 85 99);
}

@keyframes icon-bounce {
  0% {
    transform: scale(0.8) rotate(-10deg);
  }
  50% {
    transform: scale(1.1) rotate(5deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
  }
}

.ticket-icon-default {
  @apply shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300;
  background: linear-gradient(135deg, rgb(243 244 246), rgb(229 231 235));
  color: rgb(75 85 99);
}

.ticket-option:hover .ticket-icon-default {
  background: linear-gradient(135deg, rgba(var(--color-primary-500), 0.15), rgba(var(--color-purple-500), 0.15));
  transform: scale(1.05) rotate(5deg);
  box-shadow: 0 4px 8px rgba(var(--color-primary-500), 0.2);
}

.ticket-option:hover .ticket-icon-svg {
  transform: scale(1.1);
}

.ticket-option:hover .ticket-icon-default {
  background-color: rgba(var(--color-primary-500), 0.1);
}

.ticket-icon-svg {
  @apply w-5 h-5;
}

.ticket-details {
  @apply flex-1 min-w-0;
}

.ticket-name-selected {
  @apply font-bold text-lg mb-1 transition-all duration-300 text-gray-900;
}

.ticket-name-default {
  @apply font-semibold text-base mb-1 transition-colors text-gray-900;
}

.ticket-description {
  @apply text-sm text-gray-600 line-clamp-2;
}

.ticket-price-selected {
  @apply text-2xl font-black transition-all duration-300 text-gray-900;
}

@keyframes slide-in-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.ticket-price {
  @apply text-lg font-bold transition-colors;
}

.ticket-price-selected {
  @apply text-lg font-bold transition-colors;
  color: rgba(var(--color-primary-600), 1);
}

.ticket-price-default {
  @apply text-lg font-bold transition-colors text-gray-900;
}

.ticket-checkmark {
  @apply shrink-0 w-7 h-7 rounded-full text-white flex items-center justify-center animate-[checkmark-pop_0.5s_ease-out];
  background: linear-gradient(135deg, rgba(var(--color-primary-500), 1), rgba(var(--color-purple-600), 1));
  box-shadow: 0 4px 12px rgba(var(--color-primary-500), 0.4), 0 0 20px rgba(var(--color-primary-500), 0.2);
}

.checkmark-icon {
  @apply w-4 h-4;
}

.selected-ticket-section {
  @apply space-y-5 pt-5 mt-5 border-t-2;
  border-image: linear-gradient(to right, rgba(var(--color-primary-500), 0.3), transparent) 1;
  animation: slide-in-up 0.4s ease-out;
}

@keyframes slide-in-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.quantity-header {
  @apply flex items-center justify-between mb-2;
}

.quantity-label {
  @apply text-sm font-semibold text-gray-900 flex items-center gap-2;
}

.quantity-label-icon {
  @apply w-4 h-4;
  color: rgba(var(--color-primary-500), 1);
}

.quantity-max {
  @apply text-xs text-gray-500;
}

.quantity-controls {
  @apply flex items-center gap-3;
}

.quantity-btn {
  @apply shrink-0 w-12 h-12 rounded-xl border-2 border-gray-300;
  @apply text-gray-700;
  @apply disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300;
  @apply flex items-center justify-center hover:scale-110 active:scale-95;
  @apply disabled:hover:scale-100 disabled:active:scale-100;
  background: linear-gradient(135deg, white, rgb(249 250 251));
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.quantity-btn:hover:not(:disabled) {
  border-color: rgba(var(--color-primary-500), 1);
  background: linear-gradient(135deg, rgba(var(--color-primary-500), 0.1), rgba(var(--color-purple-500), 0.1));
  box-shadow: 0 4px 12px rgba(var(--color-primary-500), 0.2);
}

.ticket-add-button::before {
  content: '';
  @apply absolute inset-0;
  background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transform: translateX(0) rotate(-45deg);
  opacity: 0;
  transition: transform 0.4s cubic-bezier(.4,0,.2,1), opacity 0.4s;
}

.ticket-add-button:hover::before {
  transform: translateX(200%) skewX(-15deg);
}

.ticket-add-button:active {
  transform: scale(0.98);
}

.quantity-btn-icon {
  @apply w-4 h-4;
}

.quantity-input {
  @apply flex-1 px-4 py-2.5 text-center text-lg font-semibold border-2 border-gray-300 rounded-lg;
  @apply bg-white text-gray-900;
  @apply focus:outline-none transition-all;
  @apply [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none;
}

.quantity-input:focus {
  border-color: rgba(var(--color-primary-500), 1);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-500), 0.1);
}

.total-section {
  @apply flex items-center justify-between p-3 bg-gray-50 rounded-lg;
}

.total-label {
  @apply text-sm font-medium text-gray-700;
}

.total-amount {
  @apply text-lg font-bold;
  color: rgba(var(--color-primary-600), 1);
}

.ticket-add-button {
  @apply transition-all;
  box-shadow: 0 10px 15px -3px rgba(var(--color-primary-500), 0.2);
}

.ticket-add-button:hover {
  box-shadow: 0 20px 25px -5px rgba(var(--color-primary-500), 0.3);
}

@keyframes checkmark-pop {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
