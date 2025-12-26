<script setup lang="ts">
import AppHeader from '@components/layout/AppHeader.vue'

useHead({
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1' }
  ],
  link: [
    { rel: 'icon', href: '/favicon.ico' }
  ],
  htmlAttrs: {
    lang: 'en'
  }
})

const title = 'Ticket Discovery Portal'
const description = 'Discover amazing concerts, festivals, and live performances. Find and book tickets for the best events near you.'

useSeoMeta({
  title,
  description,
  ogTitle: title,
  ogDescription: description,
  twitterCard: 'summary_large_image'
})

// Fetch user data on app initialization if token exists
const { token, fetchUser } = useAuth()
if (import.meta.client && token.value) {
  fetchUser()
}
</script>

<template>
  <UApp class="full-page-app">
    <AppHeader />

    <main class="main-content">
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>
    </main>
  </UApp>
</template>

<style>
/* Global full-page styles */
html, body, #__nuxt {
  height: 100%;
  margin: 0;
  padding: 0;
}

.full-page-app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}
</style>
