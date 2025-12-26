<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'form-button',
      widthClass,
      variantClass,
      buttonClass
    ]"
    @click="$emit('click', $event)"
  >
    <span
      v-if="loading"
      class="flex items-center gap-2"
    >
      <svg
        class="animate-spin h-4 w-4"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
      </svg>
      <span>{{ loadingText }}</span>
    </span>
    <span
      v-else
      class="flex items-center gap-2"
    >
      <slot name="icon" />
      <slot>{{ label }}</slot>
    </span>
  </button>
</template>

<script setup lang="ts">
import type { FormButtonProps } from '@types'

const props = withDefaults(defineProps<FormButtonProps>(), {
  type: 'button',
  label: '',
  loading: false,
  loadingText: 'Loading...',
  disabled: false,
  variant: 'primary',
  fullWidth: false,
  buttonClass: ''
})

defineEmits<{
  click: [event: MouseEvent]
}>()

const widthClass = computed(() => {
  return props.fullWidth ? 'w-full' : ''
})

const variantClass = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'form-button-primary'
    case 'secondary':
      return 'form-button-secondary'
    case 'danger':
      return 'form-button-danger'
    case 'outline':
      return 'form-button-outline'
    default:
      return 'form-button-primary'
  }
})
</script>

<style scoped>
@reference "tailwindcss";

.form-button {
  @apply group relative flex items-center justify-center gap-2 py-2.5 px-6 border border-transparent text-sm font-semibold rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98];
}

.form-button-primary {
  @apply text-white bg-linear-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:ring-indigo-500 shadow-md hover:shadow-lg;
}

.form-button-secondary {
  @apply text-gray-700 bg-gray-200 hover:bg-gray-300:bg-gray-600 focus:ring-gray-500;
}

.form-button-danger {
  @apply text-white bg-linear-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-red-500 shadow-md hover:shadow-lg;
}

.form-button-outline {
  @apply text-indigo-600 bg-transparent border-2 border-indigo-600 hover:bg-indigo-50:bg-indigo-900/20 focus:ring-indigo-500;
}
</style>
