<template>
  <UBadge
    :color="badgeColor"
    :variant="variant"
    :size="size"
  >
    <div class="flex items-center gap-1.5">
      <UIcon
        v-if="showIcon"
        :name="iconName"
        :class="iconSize"
      />
      <slot>{{ label }}</slot>
    </div>
  </UBadge>
</template>

<script setup lang="ts">
import type { StatusBadgeProps } from '@types'

const props = withDefaults(defineProps<StatusBadgeProps>(), {
  label: '',
  showIcon: true,
  variant: 'soft',
  size: 'sm'
})

const badgeColor = computed(() => {
  switch (props.status) {
    case 'active':
    case 'completed':
      return 'success'
    case 'upcoming':
      return 'primary'
    case 'pending':
      return 'warning'
    case 'cancelled':
    case 'inactive':
      return 'error'
    case 'sold-out':
      return 'neutral'
    default:
      return 'neutral'
  }
})

const iconName = computed(() => {
  switch (props.status) {
    case 'active':
      return ''
    case 'completed':
      return ''
    case 'upcoming':
      return ''
    case 'pending':
      return ''
    case 'cancelled':
      return ''
    case 'inactive':
      return ''
    case 'sold-out':
      return ''
    default:
      return ''
  }
})

const iconSize = computed(() => {
  switch (props.size) {
    case 'xs':
      return 'w-3 h-3'
    case 'sm':
      return 'w-3.5 h-3.5'
    case 'md':
      return 'w-4 h-4'
    case 'lg':
      return 'w-5 h-5'
    default:
      return 'w-3.5 h-3.5'
  }
})
</script>
