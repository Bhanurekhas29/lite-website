import { useEffect, useRef, useState } from 'react'

// Shows a number like "15+" counting up from 1 when it scrolls into view.
export default function CountUp({ value, duration = 1800 }) {
  const text = String(value ?? '')
  const match = /^(\D*)(\d[\d,]*)(.*)$/.exec(text)
  const ref = useRef(null)
  const target = match ? Number(match[2].replace(/,/g, '')) : 0
  const [current, setCurrent] = useState(() =>
    target > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 1 : target,
  )

  useEffect(() => {
    if (target <= 1 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return undefined
    let frame
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return
        observer.disconnect()
        const start = performance.now()
        const tick = (now) => {
          const t = Math.min((now - start) / duration, 1)
          const eased = 1 - Math.pow(1 - t, 3)
          setCurrent(Math.max(1, Math.round(target * eased)))
          if (t < 1) frame = requestAnimationFrame(tick)
        }
        setCurrent(1)
        frame = requestAnimationFrame(tick)
      },
      { threshold: 0.6 },
    )
    observer.observe(ref.current)
    return () => {
      observer.disconnect()
      cancelAnimationFrame(frame)
    }
  }, [text, target, duration])

  if (!match) return <>{text}</>
  const shown = match[2].includes(',') ? current.toLocaleString('en-GB') : String(current)
  return <span ref={ref}>{match[1]}{shown}{match[3]}</span>
}
