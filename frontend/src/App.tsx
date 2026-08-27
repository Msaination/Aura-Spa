import { AppShell } from '@/components/layout/AppShell'
import { BookingCTA } from '@/components/home/BookingCTA'
import { FeatureGrid } from '@/components/home/FeatureGrid'
import { ServiceHighlights } from '@/components/home/ServiceHighlights'
import { Button } from '@/components/ui/button'
import { createBookingOrder, fetchBookingServices, type BookingServiceOption } from '@/lib/api'
import { useEffect, useState } from 'react'

const palette = [
  { name: 'Neutral Greige', hex: '#CDC6C3' },
  { name: 'Rose Blonde', hex: '#CFB3A9' },
  { name: 'Dusty Brown', hex: '#A09086' },
  { name: 'Cotton Cream', hex: '#E4D8CB' },
]

const offerings = [
  'Birthday',
  'Anniversary',
  'Bridal',
  'Baby Shower',
  'Thank You',
  'Corporate Appreciation',
  'Festive Season',
  'Choose Your Own Amount',
]

const seasonalOffers = [
  'Monthly Spa Specials',
  'Winter Warmth Collection',
  'Spring Renewal',
  'Mother’s Day Packages',
  'Women’s Month Experiences',
  'Valentine’s Escapes',
  'Festive Retreats',
]

const corporateWellness = [
  'Conference Wellness Breaks',
  'Team Appreciation Experiences',
  'Employee Wellness Days',
  'Corporate Gift Vouchers',
  'Private Group Bookings',
]

const eventMoments = [
  'Bridal Spa Parties',
  'Baby Showers',
  'Birthday Celebrations',
  'Private Wellness Gatherings',
]

function App() {
  const [services, setServices] = useState<BookingServiceOption[]>([])
  const [loadingServices, setLoadingServices] = useState(false)
  const [bookingError, setBookingError] = useState('')
  const [form, setForm] = useState({
    service_id: 0,
    date: '2026-09-05',
    time: '10:30',
    amount: 0,
    first_name: 'Jane',
    last_name: 'Doe',
    email: 'jane@example.com',
  })
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    const loadServices = async () => {
      setLoadingServices(true)

      try {
        const result = await fetchBookingServices()
        setServices(result)

        if (result.length > 0) {
          const firstService = result[0]
          setForm((current) => ({
            ...current,
            service_id: firstService.id,
            amount: firstService.price,
          }))
        }
      } catch (error) {
        console.error('Failed to load BookPro services', error)
      } finally {
        setLoadingServices(false)
      }
    }

    void loadServices()
  }, [])

  const handleChange = (field: keyof typeof form, value: string | number) => {
    setForm((current) => ({ ...current, [field]: value }))
  }

  const handleServiceChange = (serviceId: number) => {
    const selected = services.find((service) => service.id === serviceId)
    if (!selected) {
      return
    }

    setForm((current) => ({ ...current, service_id: selected.id, amount: selected.price }))
  }

  const handleBookTreatment = async (event?: React.SyntheticEvent<HTMLElement>) => {
    event?.preventDefault()
    setSubmitting(true)
    setBookingError('')

    try {
      const customerName = `${form.first_name} ${form.last_name}`.trim()
      const result = await createBookingOrder({
        serviceId: String(form.service_id),
        appointmentDate: form.date,
        appointmentTime: form.time,
        amount: Number(form.amount),
        customerName,
        customerEmail: form.email,
        phone: '',
        notes: 'Spa booking from Aura Spa frontend',
      })

      if (result.checkoutUrl) {
        window.location.href = result.checkoutUrl
      }
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Unable to create your booking right now.'
      setBookingError(message)
      console.error('Failed to create booking order', error)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <AppShell>
      <section className="mx-auto max-w-6xl px-6 py-10 md:py-14">
        <div className="relative overflow-hidden rounded-[2rem] bg-[#f4efe9] p-6 shadow-[0_20px_40px_rgba(120,96,85,0.08)] md:p-10">
          <div className="grid gap-5 md:grid-cols-2">
            {palette.map((color) => (
              <div
                key={color.name}
                className="flex h-[260px] items-end justify-center rounded-[2rem] border border-white/20 shadow-inner"
                style={{ backgroundColor: color.hex }}
              >
                <div className="mb-8 text-center text-[#2f2926]">
                  <div className="text-[0.76rem] font-medium uppercase tracking-[0.14em] md:text-[0.95rem]">
                    {color.name.split(' ')[0]}
                  </div>
                  <div className="mt-1 text-[0.76rem] font-medium uppercase tracking-[0.14em] md:text-[0.95rem]">
                    {color.name.split(' ').slice(1).join(' ')}
                  </div>
                  <div className="mt-2 text-[0.8rem] font-medium tracking-[0.12em] text-[#251f1d] md:text-[0.95rem]">
                    {color.hex}
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-10 grid gap-6 md:grid-cols-[1.4fr_0.6fr] md:items-center">
            <div>
              <p className="text-xs font-medium uppercase tracking-[0.26em] text-[#8c7267]">
                Welcome to Aura Spa
              </p>
              <h1 className="mt-3 max-w-xl text-4xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-5xl">
                Escape the demands of everyday life and step into a sanctuary of calm.
              </h1>
              <p className="mt-4 max-w-2xl text-base leading-7 text-[#5d4f49]">
                Designed with timeless elegance and inspired by the calming essence of nature, Aura Spa offers thoughtfully curated wellness experiences that restore balance, nurture confidence and awaken the senses.
              </p>
            </div>

            <div className="flex flex-col justify-start gap-3 md:items-end">
              <Button
                className="rounded-full bg-[#d7b8ad] text-[#2a221f] hover:bg-[#caa79a]"
                onClick={handleBookTreatment}
              >
                Book a Treatment
              </Button>
              <Button variant="secondary" className="rounded-full border-[#dbc6b8] bg-[#f6efe9] text-[#2b2624]">
                Buy a Gift Voucher
              </Button>
            </div>
          </div>
        </div>
      </section>

      <section id="about" className="mx-auto max-w-6xl px-6 py-12 md:py-20">
        <div className="grid gap-10 md:grid-cols-2 md:items-center">
          <div>
            <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#8c7267]">
              The Aura Experience
            </p>
            <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-4xl">
              Sensory Wellness Therapy
            </h2>
            <p className="mt-4 text-base leading-7 text-[#5d4f49]">
              Every wellness journey at Aura Spa begins with the senses. Before your treatment begins, you’ll be invited to choose your preferred aroma from our signature collection of therapeutic oils. As calming fragrances fill the room, gentle touch, mindful rituals and a serene atmosphere work together to release tension, quiet the mind and restore balance.
            </p>
            <p className="mt-4 text-base leading-7 text-[#5d4f49]">
              More than just a treatment, this is a complete sensory experience designed to nurture both body and soul—leaving you feeling renewed long after your visit.
            </p>
          </div>

          <div className="rounded-[2rem] bg-[#efe5de] p-8">
            <div className="space-y-4">
              <div className="rounded-2xl bg-[#f7f1eb] p-4">
                <p className="text-xs font-medium uppercase tracking-[0.2em] text-[#7a625a]">Atmosphere</p>
                <p className="mt-2 text-lg font-medium text-[#2b2624]">A tranquil environment designed to quiet the senses.</p>
              </div>
              <div className="rounded-2xl bg-[#f7f1eb] p-4">
                <p className="text-xs font-medium uppercase tracking-[0.2em] text-[#7a625a]">Care</p>
                <p className="mt-2 text-lg font-medium text-[#2b2624]">Personalised touch, mindful rituals and exceptional service.</p>
              </div>
              <div className="rounded-2xl bg-[#f7f1eb] p-4">
                <p className="text-xs font-medium uppercase tracking-[0.2em] text-[#7a625a]">Outcome</p>
                <p className="mt-2 text-lg font-medium text-[#2b2624]">Refreshed, uplifted and already planning your next visit.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <FeatureGrid />
      <ServiceHighlights />

      <section id="gift-vouchers" className="mx-auto max-w-6xl px-6 py-12 md:py-20">
        <div className="rounded-[2rem] bg-[#f7efe9] p-8 md:p-10">
          <div className="mb-8">
            <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#8c7267]">Gift Vouchers</p>
            <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-4xl">
              Give the gift of relaxation.
            </h2>
          </div>

          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {offerings.map((item) => (
              <div key={item} className="rounded-2xl border border-[#eadfd5] bg-white/70 p-4 text-sm font-medium text-[#4d403b]">
                {item}
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="seasonal" className="mx-auto max-w-6xl px-6 py-12 md:py-20">
        <div className="mb-8 max-w-2xl">
          <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#8c7267]">Seasonal Indulgence</p>
          <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-4xl">
            Discover exclusive monthly experiences and limited-time wellness offers.
          </h2>
        </div>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {seasonalOffers.map((item) => (
            <div key={item} className="rounded-2xl border border-[#eadfd5] bg-[#f9f4f1] p-5 text-base font-medium text-[#3a2f2c]">
              {item}
            </div>
          ))}
        </div>
      </section>

      <section id="corporate" className="mx-auto max-w-6xl px-6 py-12 md:py-20">
        <div className="grid gap-8 md:grid-cols-[1fr_1.2fr] md:items-center">
          <div>
            <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#8c7267]">Corporate Wellness</p>
            <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-4xl">
              Invest in your team’s wellbeing.
            </h2>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            {corporateWellness.map((item) => (
              <div key={item} className="rounded-2xl border border-[#eadfd5] bg-[#f9f4f1] p-4 text-sm font-medium text-[#3a2f2c]">
                {item}
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="events" className="mx-auto max-w-6xl px-6 py-12 md:py-20">
        <div className="rounded-[2rem] bg-[#efe5de] p-8 md:p-10">
          <div className="mb-6">
            <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#8c7267]">Events</p>
            <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-4xl">
              Celebrate life’s special moments in an atmosphere of luxury and relaxation.
            </h2>
          </div>
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {eventMoments.map((item) => (
              <div key={item} className="rounded-2xl border border-[#dcc9c0] bg-white/70 p-4 text-sm font-medium text-[#3a2f2c]">
                {item}
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="contact" className="mx-auto max-w-6xl px-6 pb-16 pt-6 md:pb-24">
        <div className="rounded-[2rem] border border-[#e6d8cf] bg-[#f9f5f1] p-8 md:p-10">
          <div className="mb-6 max-w-2xl">
            <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#8c7267]">Your next visit</p>
            <h3 className="mt-2 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624]">
              Book your Aura Spa treatment.
            </h3>
          </div>

          <form onSubmit={handleBookTreatment} className="grid gap-4 md:grid-cols-2">
            {bookingError ? (
              <div className="md:col-span-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {bookingError}
              </div>
            ) : null}

            <label className="flex flex-col gap-2 text-sm font-medium text-[#2b2624]">
              Service
              <select
                value={form.service_id}
                onChange={(event) => handleServiceChange(Number(event.target.value))}
                disabled={loadingServices || services.length === 0}
                className="rounded-xl border border-[#dcc9c0] bg-white px-3 py-2 outline-none ring-0"
              >
                {services.length === 0 ? (
                  <option value={0}>{loadingServices ? 'Loading services...' : 'No services available'}</option>
                ) : (
                  services.map((service) => (
                    <option key={service.id} value={service.id}>
                      {service.name} — R {service.price.toFixed(2)}
                    </option>
                  ))
                )}
              </select>
            </label>

            <label className="flex flex-col gap-2 text-sm font-medium text-[#2b2624]">
              Amount (ZAR)
              <input
                type="number"
                min="0"
                value={form.amount}
                onChange={(event) => handleChange('amount', Number(event.target.value))}
                className="rounded-xl border border-[#dcc9c0] bg-white px-3 py-2 outline-none ring-0"
              />
            </label>

            <label className="flex flex-col gap-2 text-sm font-medium text-[#2b2624]">
              Booking date
              <input
                type="date"
                value={form.date}
                onChange={(event) => handleChange('date', event.target.value)}
                className="rounded-xl border border-[#dcc9c0] bg-white px-3 py-2 outline-none ring-0"
              />
            </label>

            <label className="flex flex-col gap-2 text-sm font-medium text-[#2b2624]">
              Booking time
              <input
                type="time"
                value={form.time}
                onChange={(event) => handleChange('time', event.target.value)}
                className="rounded-xl border border-[#dcc9c0] bg-white px-3 py-2 outline-none ring-0"
              />
            </label>

            <label className="flex flex-col gap-2 text-sm font-medium text-[#2b2624]">
              First name
              <input
                type="text"
                value={form.first_name}
                onChange={(event) => handleChange('first_name', event.target.value)}
                className="rounded-xl border border-[#dcc9c0] bg-white px-3 py-2 outline-none ring-0"
              />
            </label>

            <label className="flex flex-col gap-2 text-sm font-medium text-[#2b2624]">
              Last name
              <input
                type="text"
                value={form.last_name}
                onChange={(event) => handleChange('last_name', event.target.value)}
                className="rounded-xl border border-[#dcc9c0] bg-white px-3 py-2 outline-none ring-0"
              />
            </label>

            <label className="flex flex-col gap-2 text-sm font-medium text-[#2b2624] md:col-span-2">
              Email
              <input
                type="email"
                value={form.email}
                onChange={(event) => handleChange('email', event.target.value)}
                className="rounded-xl border border-[#dcc9c0] bg-white px-3 py-2 outline-none ring-0"
              />
            </label>

            <div className="md:col-span-2 flex justify-end">
              <Button type="submit" className="rounded-full bg-[#2b2624] text-white hover:bg-[#1d1a18]" disabled={submitting}>
                {submitting ? 'Preparing your booking...' : 'Continue to PayFast'}
              </Button>
            </div>
          </form>
        </div>
      </section>

      <BookingCTA />
    </AppShell>
  )
}

export default App
