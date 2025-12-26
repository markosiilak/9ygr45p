/**
 * Shared TypeScript interfaces and types for the application
 */

// ============================================================================
// Event Types
// ============================================================================

export interface TicketType {
  id: number
  name: string
  description?: string
  price: number
  currency?: string
  event_id?: number
  created_at?: string
  updated_at?: string
}

export type EventStatus = 'active' | 'inactive' | 'upcoming' | 'sold-out'

export interface Event {
  id: number
  user_id?: number
  title?: string
  name?: string
  subtitle?: string
  description?: string
  image_url?: string
  location?: string
  start_time?: string
  date?: string
  capacity?: number
  organizer?: string
  categories?: string[]
  tickets_available?: number
  status?: EventStatus
  ticket_types?: TicketType[]
  created_at?: string
  updated_at?: string
}

export interface HeroEvent {
  id?: number
  title?: string
  name?: string
  description?: string
  summary?: string
  url?: string
  image_url?: string
  image?: string
}

// ============================================================================
// Component Props
// ============================================================================

export interface EventCardProps {
  event: Event
}

export interface TicketSelectorProps {
  tickets: TicketType[]
  eventId: number | string
  eventTitle: string
  showLabel?: boolean
  maxQuantity?: number
}

export interface EventsListProps {
  events?: Event[]
}

export interface FeaturedEventsSidebarProps {
  events: Event[]
}

export interface FormInputProps {
  id: string
  name?: string
  type?: string
  modelValue: string | number
  placeholder?: string
  label?: string
  labelClass?: string
  srOnly?: boolean
  required?: boolean
  autocomplete?: string
  disabled?: boolean
  error?: string
  hint?: string
  rounded?: 'none' | 'top' | 'bottom' | 'all'
  inputClass?: string
  icon?: string
}

export interface FormButtonProps {
  type?: 'button' | 'submit' | 'reset'
  label?: string
  loading?: boolean
  loadingText?: string
  disabled?: boolean
  variant?: 'primary' | 'secondary' | 'danger' | 'outline'
  fullWidth?: boolean
  buttonClass?: string
}

export interface StatusBadgeProps {
  status: 'active' | 'inactive' | 'pending' | 'completed' | 'cancelled' | 'upcoming' | 'sold-out'
  label?: string
  showIcon?: boolean
  variant?: 'solid' | 'soft' | 'outline'
  size?: 'xs' | 'sm' | 'md' | 'lg'
}

// ============================================================================
// Auth Types
// ============================================================================

export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string | null
  roles?: Array<{
    id: number
    name: string
    slug: string
    description?: string
  }>
  created_at?: string
  updated_at?: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

// ============================================================================
// Cart Types
// ============================================================================

export interface CartItem {
  eventId: number | string
  ticketTypeId: number | string
  title: string
  ticketName: string
  price: number
  quantity: number
}

// ============================================================================
// i18n Types
// ============================================================================

export interface Translations {
  [key: string]: string | Translations
}
