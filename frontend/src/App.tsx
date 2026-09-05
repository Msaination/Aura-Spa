import { useEffect, useState } from 'react'

const launchDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)

function getTimeLeft(target: Date) {
  const difference = target.getTime() - Date.now()

  if (difference <= 0) {
    return { days: 0, hours: 0, minutes: 0, seconds: 0 }
  }

  return {
    days: Math.floor(difference / (1000 * 60 * 60 * 24)),
    hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
    minutes: Math.floor((difference / (1000 * 60)) % 60),
    seconds: Math.floor((difference / 1000) % 60),
  }
}

function App() {
  const [timeLeft, setTimeLeft] = useState(() => getTimeLeft(launchDate))

  useEffect(() => {
    const timer = window.setInterval(() => {
      setTimeLeft(getTimeLeft(launchDate))
    }, 1000)

    return () => window.clearInterval(timer)
  }, [])

  const countdownItems = [
    { label: 'Days', value: timeLeft.days },
    { label: 'Hours', value: timeLeft.hours },
    { label: 'Minutes', value: timeLeft.minutes },
    { label: 'Seconds', value: timeLeft.seconds },
  ]

  return (
    <main className="coming-soon-page">
      <div className="glow glow-one" />
      <div className="glow glow-two" />

      <section className="launch-panel">
        <p className="eyebrow">Aura Spa</p>
        <h1>Coming soon</h1>
        <p className="subtitle">
          We are preparing a refined wellness experience for you. Our new spa launch opens in 7 days.
        </p>

        <div className="countdown-grid" aria-label="Countdown to launch">
          {countdownItems.map((item) => (
            <div key={item.label} className="time-box">
              <span className="time-value">{String(item.value).padStart(2, '0')}</span>
              <span className="time-label">{item.label}</span>
            </div>
          ))}
        </div>

        <div className="info-row">
          <div>
            <span className="info-label">Launch date</span>
            <strong>{launchDate.toLocaleDateString('en-ZA', { dateStyle: 'medium' })}</strong>
          </div>
          <div>
            <span className="info-label">Wellness promise</span>
            <strong>Restorative rituals &amp; mindful luxury</strong>
          </div>
        </div>
      </section>
    </main>
  )
}

export default App
