import { useId, useState } from 'react'
import { bgProps, Eyebrow, Heading, Paragraphs } from '../components/ui.jsx'

// Questions and answers as an accordion. The same questions are also added as FAQ markup
// (schema.org) so search engines can read them.
export default function Faq({ section }) {
  const f = section.fields
  const items = (section.items ?? []).filter((i) => i.fields.question && i.fields.answer)
  const [open, setOpen] = useState(null)
  const base = useId()
  const bg = bgProps(f)
  if (items.length === 0) return null

  const markup = {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: items.map((i) => ({
      '@type': 'Question',
      name: i.fields.question,
      acceptedAnswer: { '@type': 'Answer', text: i.fields.answer },
    })),
  }

  return (
    <section className={`section${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className="container">
        <header className="section-head is-center">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} center />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
          <Paragraphs value={f.intro} className="section-intro" />
        </header>
        <div className="faq">
          {items.map((item, i) => {
            const isOpen = open === i
            return (
              <div className={`faq-item${isOpen ? ' is-open' : ''}`} key={item.id}>
                <h3 className="faq-q">
                  <button type="button" aria-expanded={isOpen} aria-controls={`${base}-${i}`} id={`${base}-q${i}`} onClick={() => setOpen(isOpen ? null : i)}>
                    <span>{item.fields.question}</span>
                    <span className="faq-icon" aria-hidden="true" />
                  </button>
                </h3>
                <div className="faq-a" id={`${base}-${i}`} role="region" aria-labelledby={`${base}-q${i}`}>
                  <div className="faq-a-inner">
                    <Paragraphs value={item.fields.answer} />
                  </div>
                </div>
              </div>
            )
          })}
        </div>
      </div>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(markup).replace(/</g, '\u003c') }} />
    </section>
  )
}
