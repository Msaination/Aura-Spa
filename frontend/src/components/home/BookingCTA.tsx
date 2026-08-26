import { Button } from '@/components/ui/button'

export function BookingCTA() {
  return (
    <section className="mx-auto max-w-6xl px-6 py-12 md:py-20">
      <div className="rounded-[2rem] bg-[#efe5de] px-8 py-10 md:px-12">
        <div className="grid gap-6 md:grid-cols-[1.2fr_0.8fr] md:items-center">
          <div>
            <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#7d625b]">
              Your wellness ritual
            </p>
            <h3 className="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-4xl">
              Book your next reset in minutes.
            </h3>
          </div>

          <div className="flex justify-start md:justify-end">
            <Button className="rounded-full bg-[#2b2624] text-white hover:bg-[#1d1a18]">
              Reserve a treatment
            </Button>
          </div>
        </div>
      </div>
    </section>
  )
}
