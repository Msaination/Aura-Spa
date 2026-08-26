const WP_API_URL = import.meta.env.VITE_WP_API_URL ?? 'http://localhost:8080/wp-json'
const WP_NAMESPACE = import.meta.env.VITE_WP_REST_NAMESPACE ?? 'auraspa/v1'

export async function wpApi<T>(path: string, init?: RequestInit): Promise<T> {
  const url = `${WP_API_URL}/${WP_NAMESPACE}${path.startsWith('/') ? path : `/${path}`}`

  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      ...(init?.headers ?? {}),
    },
    ...init,
  })

  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`)
  }

  return response.json() as Promise<T>
}

export type CreateBookingOrderPayload = {
  service_id: number
  date: string
  time: string
  amount: number
  first_name: string
  last_name: string
  email: string
}

export type CreateBookingOrderResponse = {
  order_id: number
  checkout_url: string
  message: string
}

export type BookingServiceOption = {
  id: number
  title: string
  price: number
  duration: string
  description?: string
}

export async function fetchBookingServices(): Promise<BookingServiceOption[]> {
  return wpApi<BookingServiceOption[]>('/services')
}

export async function createBookingOrder(payload: CreateBookingOrderPayload): Promise<CreateBookingOrderResponse> {
  return wpApi<CreateBookingOrderResponse>('/create-booking-order', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}
