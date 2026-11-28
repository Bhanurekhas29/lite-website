import { bgProps, Eyebrow, Heading, Paragraphs, SmartButton } from '../components/ui.jsx'

// Hero banner in three styles: dark (white text on a dark image), light (dark text on a
// faded image) and split (text with a framed image on the right).
export default function Hero({ section }) {
  const f = section.fields
  const center = f.align === 'center'
  const light = f.style === 'light'
  const split = f.style === 'split'
  const dark = f.style === 'dark'

  const text = (
    <>
      <Eyebrow text={f.eyebrow} style={f.eyebrow_style} light={dark} center={center} />
      <Heading as="h1" text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} className={f.tagline ? 'hero-h1' : ''} />
      {f.tagline && (
        <p className="heading hero-tagline">
          {f.tagline}
          {f.tagline_accent && <><br /><em>{f.tagline_accent}</em></>}
        </p>
      )}
      {f.subheading && <p className="hero-sub">{f.subheading}</p>}
      <div className="hero-text">
        <Paragraphs value={f.text1} className="hero-lead" />
        <Paragraphs value={f.text2} />
        <Paragraphs value={f.text3} />
      </div>
      {(f.button1 || f.button2) && (
        <div className="hero-actions">
          <SmartButton button={f.button1} variant="primary" />
          <SmartButton button={f.button2} variant={dark ? 'outline' : 'outline-dark'} />
        </div>
      )}
    </>
  )

  if (split) {
    const bg = bgProps(f)
    return (
      <section className={`hero hero-split${bg.className}`} style={bg.style} id={`section-${section.id}`}>
        <div className="container hero-split-inner">
          <div className="hero-copy">{text}</div>
          {f.image && (
            <figure className="framed hero-framed">
              <img src={f.image.url} alt={f.image.alt} />
            </figure>
          )}
        </div>
      </section>
    )
  }

  return (
    <section
      className={`hero hero-${f.style}${center ? ' hero-center' : ''}`}
      id={`section-${section.id}`}
      style={f.background ? { backgroundImage: `url(${f.background.url})` } : undefined}
    >
      <div className="hero-overlay" />
      <div className="container hero-inner">{text}</div>
    </section>
  )
}
