<template>
  <UHeader>
    <template #left>
      <NuxtLink to="/">
        <AppLogo class="w-auto h-6 shrink-0" />
      </NuxtLink>
    </template>

    <template #right>
      <div class="flex items-center gap-3">
        <!-- Shopping Cart -->
        <ShoppingCart />

        <!-- User Info (when logged in) -->
        <div
          v-if="user || token"
          class="flex items-center gap-3"
        >
          <!-- User Menu -->
          <div
            ref="userMenuRef"
            class="relative"
            @click.stop
          >
            <UButton
              color="neutral"
              variant="ghost"
              class="flex items-center gap-2"
              @click="showUserMenu = !showUserMenu"
            >
              <div class="flex items-center gap-2">
                <div class="user-avatar">
                  <span class="user-initials">
                    {{ userInitials }}
                  </span>
                </div>
                <div class="hidden sm:block text-left">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ user?.name || 'User' }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ user?.email || '' }}
                  </div>
                </div>
              </div>
              <UIcon
                name="i-heroicons-chevron-down-20-solid"
                class="w-4 h-4"
              />
            </UButton>

            <!-- Dropdown Menu -->
            <div
              v-if="showUserMenu"
              class="user-menu-dropdown"
              @click.stop
            >
              <div class="p-1">
                <div
                  v-for="(group, groupIndex) in userMenuItems"
                  :key="groupIndex"
                  class="space-y-1"
                >
                  <UButton
                    v-for="item in group"
                    :key="item.label"
                    :variant="item.label === 'Logout' ? 'ghost' : 'ghost'"
                    :color="item.label === 'Logout' ? 'error' : 'neutral'"
                    class="w-full justify-start"
                    @click="item.click(); showUserMenu = false"
                  >
                    <UIcon
                      :name="item.icon"
                      class="w-4 h-4 mr-2"
                    />
                    {{ item.label }}
                  </UButton>
                  <div
                    v-if="groupIndex < userMenuItems.length - 1"
                    class="my-1 border-t border-gray-200"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- User Roles Badges -->
          <div
            v-if="user?.roles && user.roles.length > 0"
            class="hidden md:flex items-center gap-2"
          >
            <UBadge
              v-for="role in user.roles"
              :key="role.id"
              :color="getRoleColor(role.slug)"
              variant="soft"
              size="sm"
            >
              {{ role.name }}
            </UBadge>
          </div>
        </div>

        <!-- Login/Register Buttons (when not logged in) -->
        <div
          v-else
          class="flex items-center gap-2"
        >
          <UButton
            to="/login"
            color="primary"
            variant="ghost"
            size="sm"
          >
            {{ t('nav.login') }}
          </UButton>
          <UButton
            to="/register"
            color="primary"
            size="sm"
          >
            {{ t('nav.register') }}
          </UButton>
        </div>

        <LanguageSwitcher />
      </div>
    </template>
  </UHeader>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { UHeader, UButton, UBadge, UIcon } from '#components'
import AppLogo from '@components/layout/AppLogo.vue'
import LanguageSwitcher from '@components/layout/LanguageSwitcher.vue'
import ShoppingCart from '@components/layout/ShoppingCart.vue'
import { useI18n } from '@composables/useI18n'
import { useRouter } from 'nuxt/app'
import { useAuth } from '@composables/useAuth'

const { t } = useI18n()
const { user, token, logout, fetchUser } = useAuth()
const router = useRouter()

const showUserMenu = ref(false)
const userMenuRef = ref<HTMLElement | null>(null)

// Fetch user if we have token but no user
onMounted(async () => {
  if (token.value && !user.value) {
    await fetchUser()
  }

  // Close menu when clicking outside
  const handleClickOutside = (event: MouseEvent) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
      showUserMenu.value = false
    }
  }
  document.addEventListener('click', handleClickOutside)
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
  })
})

// User initials for avatar
const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  const name = user.value.name
  const names = name.split(' ').filter(n => n.length > 0)
  if (names.length >= 2) {
    const first = names[0]
    const last = names[names.length - 1]
    if (first && last && first.length > 0 && last.length > 0) {
      return (first[0]! + last[0]!).toUpperCase()
    }
  }
  if (name.length >= 2) {
    return name.substring(0, 2).toUpperCase()
  }
  return name.substring(0, 1).toUpperCase()
})

// Get color for role badge
const getRoleColor = (roleSlug: string): 'error' | 'warning' | 'primary' | 'neutral' => {
  const colorMap: Record<string, 'error' | 'warning' | 'primary' | 'neutral'> = {
    admin: 'error',
    organizer: 'warning',
    customer: 'primary'
  }
  return colorMap[roleSlug] || 'neutral'
}

// User menu items
const userMenuItems = computed(() => {
  const items = [
    [
      {
        label: t('nav.profile'),
        icon: 'i-heroicons-user-circle-20-solid',
        click: () => {
          // TODO: Navigate to profile page when created
        }
      },
      {
        label: t('nav.myEvents'),
        icon: 'i-heroicons-calendar-20-solid',
        click: () => {
          // TODO: Navigate to my events page when created
        }
      }
    ]
  ]

  // Add logout
  items.push([
    {
      label: t('nav.logout'),
      icon: 'i-heroicons-arrow-right-on-rectangle-20-solid',
      click: async () => {
        await logout()
        router.push('/')
      }
    }
  ])

  return items
})
</script>

<style scoped>
.user-avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-primary-100);
}

.user-initials {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-primary-600);
}

.user-menu-dropdown {
  position: absolute;
  right: 0;
  margin-top: 0.5rem;
  width: 14rem;
  border-radius: 0.5rem;
  border: 1px solid rgb(229 231 235);
  background-color: white;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  z-index: 50;
}
</style>
