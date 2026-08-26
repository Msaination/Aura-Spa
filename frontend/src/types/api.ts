export type ApiResponse<T> = {
  status: 'success' | 'error'
  message?: string
  data: T
  errors?: Record<string, string[]>
}

export type Business = {
  id: number
  slug: string
  name: string
  description?: string
  featured_image?: string
  category?: string
  city?: string
  rating?: number
}

export type Service = {
  id: number
  business_id: number
  name: string
  description?: string
  price?: number
  duration?: string
  slug?: string
}

export type Booking = {
  id: number
  booking_id?: string
  status: 'pending' | 'confirmed' | 'cancelled'
  date: string
  time: string
  total?: number
}
