<template>
  <UDropdownMenu :items="menuItems">
    <UButton
      variant="ghost"
      size="sm"
      class="language-btn"
    >
      <span class="flag">{{ currentFlag }}</span>
      <span class="code">{{ locale.toUpperCase() }}</span>
      <UIcon
        name="i-heroicons-chevron-down-20-solid"
        class="w-4 h-4 ml-1"
      />
    </UButton>
  </UDropdownMenu>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n, type Locale } from '@composables/useI18n'

const { locale, availableLocales, setLocale } = useI18n()

const currentFlag = computed(() => {
  const current = availableLocales.find(l => l.code === locale.value)
  return current?.flag || '🌐'
})

const menuItems = computed(() => [
  availableLocales.map(lang => ({
    label: `${lang.flag} ${lang.name}`,
    click: async () => {
      await setLocale(lang.code as Locale)
    },
    active: locale.value === lang.code
  }))
])
</script>

<style scoped>
@reference "tailwindcss";

.language-btn {
  @apply flex items-center gap-1.5;
}

.flag {
  @apply text-base;
}

.code {
  @apply text-xs font-medium text-gray-600;
}
</style>
