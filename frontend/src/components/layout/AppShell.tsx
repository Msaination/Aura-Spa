import { LogIn, LogOut, UserRound } from 'lucide-react'
import { useEffect, useState } from 'react'

type CurrentUser = {
  id?: number
  name?: string
  nickname?: string
  slug?: string
  display_name?: string
}

const getUserDisplayName = (user: CurrentUser | null) => {
  if (!user) {
    return 'Account'
  }

  return user.nickname || user.name || user.display_name || user.slug || 'Account'
}

const getLogoutUrl = () => {
  const currentUrl = encodeURIComponent(window.location.href)
  const nonce = (window as Window & { auraSpaAuth?: { logoutNonce?: string } }).auraSpaAuth?.logoutNonce

  if (nonce) {
    return `${window.location.origin}/wp-login.php?action=logout&_wpnonce=${encodeURIComponent(nonce)}&redirect_to=${currentUrl}`
  }

  return `${window.location.origin}/wp-login.php?action=logout&redirect_to=${currentUrl}`
}

export function AppShell({ children }: { children: React.ReactNode }) {
  const [currentUser, setCurrentUser] = useState<CurrentUser | null>(null)
  const [isLoadingUser, setIsLoadingUser] = useState(true)

  useEffect(() => {
    let isMounted = true

    const fetchCurrentUser = async () => {
      try {
        const response = await fetch(`${window.location.origin}/wp-json/wp/v2/users/me`, {
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          },
        })

        if (!response.ok) {
          if (isMounted) {
            setCurrentUser(null)
          }
          return
        }

        const user = (await response.json()) as CurrentUser | null

        if (isMounted) {
          setCurrentUser(user && typeof user === 'object' && user.id ? user : null)
        }
      } catch (error) {
        console.warn('Unable to resolve current WordPress user for header state.', error)

        if (isMounted) {
          setCurrentUser(null)
        }
      } finally {
        if (isMounted) {
          setIsLoadingUser(false)
        }
      }
    }

    void fetchCurrentUser()

    return () => {
      isMounted = false
    }
  }, [])

  const userLabel = getUserDisplayName(currentUser)
  const loginUrl = `${window.location.origin}/wp-login.php?redirect_to=${encodeURIComponent(window.location.href)}`

  return (
    <div className="min-h-screen bg-[#f3efe9] text-[#2a241f]">
      <header className="border-b border-[#e8dcd1] bg-[#f9f5f2]/80 backdrop-blur-sm">
        <div className="mx-auto flex max-w-6xl items-center justify-between gap-3 px-6 py-4">
          <div>
            <p className="text-lg font-semibold uppercase tracking-[0.18em] text-[#2a241f]">
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

          <div className="flex items-center gap-3">
            {currentUser ? (
              <>
                <div className="hidden items-center gap-2 rounded-full border border-[#dec8ba] bg-[#f8efe9] px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#2b2624] sm:flex">
                  <UserRound className="h-3.5 w-3.5" />
                  <span className="truncate max-w-[120px]">{userLabel}</span>
                </div>
                <a
                  href={getLogoutUrl()}
                  className="inline-flex items-center gap-2 rounded-full bg-[#2b2624] px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#f8f4f1] transition-opacity hover:opacity-90"
                >
                  <LogOut className="h-3.5 w-3.5" />
                  Logout
                </a>
              </>
            ) : (
              <a
                href={loginUrl}
                className="inline-flex items-center gap-2 rounded-full bg-[#d7b8ad] px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#2a221f] transition-opacity hover:opacity-90"
              >
                <LogIn className="h-3.5 w-3.5" />
                {isLoadingUser ? 'Loading...' : 'Login'}
              </a>
            )}
          </div>
        </div>
      </header>
      <main>{children}</main>
    </div>
  )
}
