<template>
  <div class="register-page-container">
    <Transition
      name="form-entrance"
      appear
    >
      <div class="max-w-md w-full">
        <div class="bg-white rounded-3xl shadow-2xl border border-black/5 ring-1 ring-white/20 auth-card">
          <div class="p-6 sm:p-8 space-y-6">
            <div class="text-center space-y-3">
              <Transition
                name="icon-bounce"
                appear
              >
                <div class="flex justify-center">
                  <div class="register-icon-container">
                    <UIcon
                      name="i-heroicons-user-plus-20-solid"
                      class="w-8 h-8 text-white"
                    />
                  </div>
                </div>
              </Transition>
              <Transition
                name="fade-slide"
                appear
              >
                <div>
                  <h2 class="text-3xl font-bold text-gray-900">
                    {{ t('auth.createYourAccount') }}
                  </h2>
                  <p class="text-sm text-gray-600">
                    {{ t('auth.joinUs') }}
                  </p>
                </div>
              </Transition>
            </div>

            <form
              class="space-y-4"
              @submit.prevent="handleRegister"
            >
              <Transition
                name="fade-slide"
                appear
              >
                <div
                  class="space-y-1"
                  style="animation-delay: 0.1s"
                >
                  <label
                    for="name"
                    class="text-sm font-medium text-gray-700"
                  >{{ t('auth.fullName') }}</label>
                  <input
                    id="name"
                    v-model="name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    :placeholder="t('auth.fullNamePlaceholder')"
                    required
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                </div>
              </Transition>

              <Transition
                name="fade-slide"
                appear
              >
                <div
                  class="space-y-1"
                  style="animation-delay: 0.2s"
                >
                  <label
                    for="email"
                    class="text-sm font-medium text-gray-700"
                  >{{ t('auth.email') }}</label>
                  <input
                    id="email"
                    v-model="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    :placeholder="t('auth.emailPlaceholder')"
                    required
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                </div>
              </Transition>

              <Transition
                name="fade-slide"
                appear
              >
                <div
                  class="space-y-1"
                  style="animation-delay: 0.3s"
                >
                  <label
                    for="password"
                    class="text-sm font-medium text-gray-700"
                  >
                    {{ t('auth.password') }}
                    <span class="text-xs text-gray-500">({{ t('auth.minCharacters', { count: 8 }) }})</span>
                  </label>
                  <input
                    id="password"
                    v-model="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="t('auth.createPasswordPlaceholder')"
                    required
                    minlength="8"
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                </div>
              </Transition>

              <Transition
                name="fade-slide"
                appear
              >
                <div
                  class="space-y-1"
                  style="animation-delay: 0.4s"
                >
                  <label
                    for="password_confirmation"
                    class="text-sm font-medium text-gray-700"
                  >{{ t('auth.confirmPassword') }}</label>
                  <input
                    id="password_confirmation"
                    v-model="passwordConfirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="t('auth.confirmPasswordPlaceholder')"
                    required
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                </div>
              </Transition>

              <Transition
                name="fade"
                mode="out-in"
              >
                <div
                  v-if="error"
                  class="rounded-xl border border-red-300 bg-red-50/70 p-3 text-sm text-red-700 flex items-start gap-2"
                >
                  <UIcon
                    name="i-heroicons-exclamation-circle-20-solid"
                    class="w-5 h-5 text-red-500"
                  />
                  <span>{{ error }}</span>
                </div>
              </Transition>

              <Transition
                name="fade-slide"
                appear
              >
                <UButton
                  type="submit"
                  :loading="loading"
                  block
                  size="xl"
                  color="primary"
                  class="rounded-xl"
                  style="animation-delay: 0.5s"
                >
                  {{ loading ? t('auth.signingUp') : t('auth.createAccount') }}
                </UButton>
              </Transition>
            </form>

            <Transition
              name="fade-slide"
              appear
            >
              <div class="mt-3 text-center space-y-3">
                <p class="text-sm text-gray-500">
                  {{ t('auth.alreadyHaveAccount') }}
                </p>
                <NuxtLink
                  to="/login"
                  class="block w-full rounded-xl border border-gray-200 text-center py-3 text-lg font-semibold text-gray-700 hover:border-primary-500 hover:text-primary-600 transition"
                >
                  {{ t('auth.signInInstead') }}
                </NuxtLink>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { UButton } from '#components'
import { definePageMeta, useRouter, useAuth } from '#imports'
import { useI18n } from '@composables/useI18n'

const { t } = useI18n()

definePageMeta({
  layout: false,
  ssr: true
})

const { register } = useAuth()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref('')
const loading = ref(false)

const handleRegister = async () => {
  error.value = ''

  if (password.value !== passwordConfirmation.value) {
    error.value = 'Passwords do not match'
    return
  }

  if (password.value.length < 8) {
    error.value = 'Password must be at least 8 characters'
    return
  }

  loading.value = true

  try {
    const result = await register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    if (result.success) {
      await router.push('/')
    } else {
      error.value = result.error || 'Registration failed'
    }
  } catch (err: unknown) {
    error.value = err instanceof Error ? err.message : 'An error occurred during registration'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@reference "tailwindcss";
@import "@/assets/styles/form-animations.css";

.icon-bounce-enter-active {
  animation: icon-bounce 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes icon-bounce {
  0% {
    opacity: 0;
    transform: scale(0) rotate(-180deg);
  }
  50% {
    transform: scale(1.1) rotate(10deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
}

.fade-slide-enter-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.register-page-container {
  @apply flex-1 flex items-center justify-center bg-linear-to-br from-indigo-50 via-white to-purple-50 py-6 px-4 sm:px-6 lg:px-8;
  min-height: calc(100vh - 64px);
}

.register-icon-container {
  @apply w-16 h-16 rounded-full bg-linear-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg;
}

.auth-card {
  animation: fade-in 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
