import type { ReactNode } from 'react'

export function AppShell({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-[#f3efe9] text-[#2a241f]">
      <header className="border-b border-[#e8dcd1] bg-[#f9f5f2]/80 backdrop-blur-sm">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
          <div>
            <p className="text-lg font-semibold tracking-[0.18em] text-[#2a241f] uppercase">
              Aura Spa
            </p>
          </div>
          <nav className="hidden items-center gap-5 text-xs font-medium uppercase tracking-[0.18em] text-[#5d4f49] md:flex">
            <a href="#" className="transition-opacity hover:opacity-80">Home</a>
            <a href="#about" className="transition-opacity hover:opacity-80">About Us</a>
            <a href="#treatments" className="transition-opacity hover:opacity-80">Treatments</a>
            <a href="#gift-vouchers" className="transition-opacity hover:opacity-80">Gift Vouchers</a>
            <a href="#seasonal" className="transition-opacity hover:opacity-80">Seasonal Indulgence</a>
            <a href="#corporate" className="transition-opacity hover:opacity-80">Corporate Wellness</a>
            <a href="#events" className="transition-opacity hover:opacity-80">Events</a>
            <a href="#contact" className="transition-opacity hover:opacity-80">Contact</a>
          </nav>
        </div>
      </header>
      <main>{children}</main>
    </div>
  )
}
