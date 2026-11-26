import { Eyebrow, Heading, Paragraphs, SmartButton } from '../components/ui.jsx'

export default function Cta({ section }) {
  const f = section.fields
  const dark = f.style === 'pink' || f.style === 'plum'
  return (
    <section className={`section section-cta${f.style === 'plum' ? ' section-cta-bleed' : ''}`} id={`section-${section.id}`}>
      <div className="container">
        <div
          className={`cta cta-${f.style} cta-${f.align}`}
          style={f.background ? { backgroundImage: `url(${f.background.url})` } : undefined}
        >
          <div className="cta-inner">
            <Eyebrow text={f.eyebrow} style={f.eyebrow_style} center={f.align === 'center'} light={dark} />
            <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
            <Paragraphs value={f.text1} className="cta-text" />
            <Paragraphs value={f.text2} className="cta-text" />
            <div className="cta-actions">
              <SmartButton button={f.button1} variant={f.style === 'pink' ? 'light' : 'primary'} />
              <SmartButton button={f.button2} variant={dark ? 'outline' : 'outline-dark'} />
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
