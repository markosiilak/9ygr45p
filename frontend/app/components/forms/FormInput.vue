<template>
  <div class="form-input-wrapper group">
    <label
      v-if="label && !srOnly"
      :for="id"
      :class="[
        'block text-sm font-semibold mb-2 transition-colors',
        labelClass || 'text-gray-700',
        error ? 'text-red-600' : ''
      ]"
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red-500 ml-1"
      >*</span>
    </label>
    <div class="relative">
      <div
        v-if="icon"
        class="input-icon-wrapper"
      >
        <UIcon
          :name="icon"
          :class="[
            'input-icon',
            error ? 'input-icon-error' : 'input-icon-default'
          ]"
        />
      </div>
      <input
        :id="id"
        :name="name"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :autocomplete="autocomplete"
        :disabled="disabled"
        :class="[
          'form-input',
          roundedClass,
          icon ? 'form-input-with-icon' : 'form-input-no-icon',
          error ? 'form-input-error' : 'form-input-normal',
          disabled ? 'form-input-disabled' : 'form-input-enabled',
          inputClass
        ]"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      >
      <div
        v-if="error"
        class="input-error-icon-wrapper"
      >
        <UIcon
          
          class="h-5 w-5 text-red-500"
        />
      </div>
    </div>
    <Transition
      enter-active-class="transition-opacity duration-200 ease"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200 ease"
      leave-to-class="opacity-0"
      mode="out-in"
    >
      <p
        v-if="error"
        class="input-error-text"
      >
        <UIcon
          
          class="w-4 h-4 shrink-0"
        />
        {{ error }}
      </p>
      <p
        v-else-if="hint"
        class="input-hint-text"
      >
        <UIcon
          
          class="w-4 h-4 shrink-0"
        />
        {{ hint }}
      </p>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { UIcon } from '#components'
import type { FormInputProps } from '@types'

const props = withDefaults(defineProps<FormInputProps>(), {
  type: 'text',
  name: undefined,
  placeholder: '',
  label: '',
  labelClass: '',
  srOnly: false,
  required: false,
  autocomplete: undefined,
  disabled: false,
  error: '',
  hint: '',
  rounded: 'all',
  inputClass: '',
  icon: undefined
})

defineEmits<{
  'update:modelValue': [value: string | number]
  'blur': [event: FocusEvent]
  'focus': [event: FocusEvent]
}>()

const roundedClass = computed(() => {
  switch (props.rounded) {
    case 'none':
      return 'rounded-none'
    case 'top':
      return 'rounded-t-lg'
    case 'bottom':
      return 'rounded-b-lg'
    case 'all':
    default:
      return 'rounded-lg'
  }
})
</script>

<style scoped>
@reference "tailwindcss";

.input-icon-wrapper {
  @apply absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10;
}

.input-icon {
  @apply h-5 w-5 transition-colors duration-200;
}

.input-icon-default {
  @apply text-gray-500 group-focus-within:text-indigo-600:text-indigo-400;
}

.input-icon-error {
  @apply text-red-500;
}

.form-input {
  @apply appearance-none relative block w-full border placeholder-gray-400 text-gray-900 bg-white focus:outline-none focus:ring-2 transition-all duration-200;
}

.form-input-with-icon {
  @apply pl-10 pr-3 py-2.5;
}

.form-input-no-icon {
  @apply px-3 py-2.5;
}

.form-input-normal {
  @apply border-gray-300 focus:border-indigo-500 focus:ring-indigo-500;
}

.form-input-error {
  @apply border-red-300 focus:border-red-500 focus:ring-red-500;
}

.form-input-enabled {
  @apply hover:border-gray-400:border-gray-500;
}

.form-input-disabled {
  @apply bg-gray-100 cursor-not-allowed opacity-60;
}

.input-error-icon-wrapper {
  @apply absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none;
}

.input-error-text {
  @apply mt-1.5 text-sm text-red-600 flex items-center gap-1;
}

.input-hint-text {
  @apply mt-1.5 text-xs text-gray-500 flex items-center gap-1;
}
</style>
