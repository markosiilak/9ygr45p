import type { User, LoginCredentials, RegisterData } from '@types'

export const useAuth = () => {
  const config = useRuntimeConfig()
  const apiBaseUrl = config.public.apiBaseUrl

  const user = useState<User | null>('auth.user', () => null)
  const token = useState<string | null>('auth.token', () => null)

  // Initialize token from localStorage on client side
  if (import.meta.client) {
    const storedToken = localStorage.getItem('auth_token')
    if (storedToken) {
      token.value = storedToken
    }
  }

  const isAuthenticated = computed(() => !!user.value && !!token.value)

  const hasRole = (roleSlug: string): boolean => {
    return user.value?.roles?.some(role => role.slug === roleSlug) ?? false
  }

  const hasAnyRole = (roleSlugs: string[]): boolean => {
    return user.value?.roles?.some(role => roleSlugs.includes(role.slug)) ?? false
  }

  const hasPermission = (permissionSlug: string): boolean => {
    if (!user.value?.roles) return false
    // Check if any of the user's roles have the permission
    // This is a simplified check - in a real app, you'd check the permissions table
    // For now, we'll check based on role slugs
    const roleSlugs = user.value.roles.map(r => r.slug)
    if (roleSlugs.includes('admin')) return true // Admin has all permissions
    if (permissionSlug === 'create-events' && roleSlugs.includes('organizer')) return true
    if (permissionSlug === 'edit-events' && roleSlugs.includes('organizer')) return true
    return false
  }

  const login = async (credentials: LoginCredentials) => {
    try {
      // Ensure credentials are properly formatted
      const body = {
        email: credentials.email.trim(),
        password: credentials.password
      }

      const response = await $fetch<{ user: User, token: string }>(`${apiBaseUrl}/api/login`, {
        method: 'POST',
        body,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      user.value = response.user
      token.value = response.token

      if (import.meta.client) {
        localStorage.setItem('auth_token', response.token)
      }

      return { success: true, user: response.user }
    } catch (error: unknown) {
      // Handle Laravel validation errors (422)
      // $fetch throws FetchError with data in error.data or error.response._data
      const err = error as { status?: number, statusCode?: number, data?: unknown, response?: { _data?: unknown, data?: unknown }, message?: string }

      console.error('Login error:', error)
      console.error('Error status:', err.status, err.statusCode)
      console.error('Error data:', err.data)
      console.error('Error response:', err.response)
      console.error('Full error object:', JSON.stringify(error, null, 2))
      if (err.status === 422 || err.statusCode === 422) {
        // Try different error response structures that $fetch might use
        const errorData = (err.data || (err.response as { _data?: unknown, data?: unknown })?.data || {}) as { errors?: Record<string, string[]>, message?: string }
        const errors = errorData.errors || {}

        // Extract error message from various possible locations
        let errorMessage = errors.email?.[0] || errors.password?.[0]

        if (!errorMessage) {
          errorMessage = errorData.message || 'Validation failed. Please check your email and password.'
        }

        console.error('Validation errors:', errors)
        console.error('Error message:', errorMessage)

        return {
          success: false,
          error: errorMessage
        }
      }

      // Handle other errors
      const errorData = (err.data || (err.response as { _data?: unknown, data?: unknown })?.data || {}) as { message?: string }
      const errorMessage = errorData.message || err.message || 'Login failed. Please try again.'
      return {
        success: false,
        error: errorMessage
      }
    }
  }

  const register = async (data: RegisterData) => {
    try {
      const response = await $fetch<{ user: User, token: string }>(`${apiBaseUrl}/api/register`, {
        method: 'POST',
        body: data,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      user.value = response.user
      token.value = response.token

      if (import.meta.client) {
        localStorage.setItem('auth_token', response.token)
      }

      return { success: true, user: response.user }
    } catch (error: unknown) {
      console.error('Register error:', error)

      // Handle Laravel validation errors (422)
      const err = error as { status?: number, statusCode?: number, data?: { errors?: Record<string, string[]>, message?: string }, message?: string }
      if (err.status === 422 || err.statusCode === 422) {
        const errors = err.data?.errors || {}
        const errorMessage = errors.email?.[0] || errors.password?.[0] || errors.name?.[0] || err.data?.message || 'Validation failed'
        return {
          success: false,
          error: errorMessage
        }
      }

      // Handle other errors
      const errorMessage = err.data?.message || err.message || 'Registration failed'
      return {
        success: false,
        error: errorMessage
      }
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await $fetch(`${apiBaseUrl}/api/logout`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token.value}`
          }
        })
      }
    } catch (error) {
      // Continue with logout even if API call fails
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null

      if (import.meta.client) {
        localStorage.removeItem('auth_token')
      }
    }
  }

  const fetchUser = async () => {
    if (!token.value) {
      return
    }

    try {
      const response = await $fetch<User>(`${apiBaseUrl}/api/user`, {
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: 'application/json'
        }
      })

      user.value = response
    } catch (error: unknown) {
      console.error('fetchUser: Error fetching user:', error)
      // Token might be invalid, clear auth state
      user.value = null
      token.value = null

      if (import.meta.client) {
        localStorage.removeItem('auth_token')
      }
      throw error
    }
  }

  // Fetch user on initialization if token exists
  if (import.meta.client && token.value) {
    fetchUser()
  }

  return {
    user: readonly(user),
    token: readonly(token),
    isAuthenticated,
    hasRole,
    hasAnyRole,
    hasPermission,
    login,
    register,
    logout,
    fetchUser
  }
}
