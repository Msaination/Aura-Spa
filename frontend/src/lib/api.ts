const WP_GRAPHQL_URL = import.meta.env.VITE_WP_GRAPHQL_URL ?? 'http://localhost:8080/graphql'

async function graphqlRequest<T>(query: string, variables?: Record<string, unknown>): Promise<T> {
  const response = await fetch(WP_GRAPHQL_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ query, variables }),
  })

  if (!response.ok) {
    throw new Error(`GraphQL request failed: ${response.status}`)
  }

  const payload = (await response.json()) as {
    data?: T
    errors?: Array<{ message: string }>
  }

  if (payload.errors && payload.errors.length > 0) {
    throw new Error(payload.errors[0]?.message ?? 'GraphQL request failed')
  }

  if (!payload.data) {
    throw new Error('GraphQL response was empty')
  }

  return payload.data
}

export type CreateBookingOrderPayload = {
  serviceId: string
  appointmentDate: string
  appointmentTime: string
  amount: number
  customerName: string
  customerEmail: string
  phone?: string
  notes?: string
}

export type CreateBookingOrderResponse = {
  createBooking: {
    orderId: number
    checkoutUrl: string
    status: string
    serviceId: string
    amount: number
    customerName: string
    customerEmail: string
    appointmentDate: string
    appointmentTime: string
  }
}

export type BookingServiceOption = {
  id: number
  name: string
  price: number
  duration: string
  description?: string
}

export async function fetchBookingServices(): Promise<BookingServiceOption[]> {
  const query = `
    query AuraSpaServices($limit: Int) {
      auraSpaServices(limit: $limit) {
        id
        name
        description
        price
        duration
      }
    }
  `

  const result = await graphqlRequest<{ auraSpaServices: Array<BookingServiceOption> }>(query, { limit: 20 })

  return result.auraSpaServices.map((service) => ({
    id: Number(service.id),
    name: service.name,
    price: Number(service.price),
    duration: service.duration,
    description: service.description,
  }))
}

export async function createBookingOrder(payload: CreateBookingOrderPayload): Promise<CreateBookingOrderResponse['createBooking']> {
  const mutation = `
    mutation CreateBooking(
      $customerName: String!
      $customerEmail: String!
      $phone: String
      $serviceId: String!
      $appointmentDate: String!
      $appointmentTime: String!
      $notes: String
      $amount: Float!
    ) {
      createBooking(
        input: {
          customerName: $customerName
          customerEmail: $customerEmail
          phone: $phone
          serviceId: $serviceId
          appointmentDate: $appointmentDate
          appointmentTime: $appointmentTime
          notes: $notes
          amount: $amount
        }
      ) {
        id
        orderId
        status
        serviceId
        amount
        customerName
        customerEmail
        appointmentDate
        appointmentTime
        checkoutUrl
      }
    }
  `

  const result = await graphqlRequest<CreateBookingOrderResponse>(mutation, payload)

  return result.createBooking
}
