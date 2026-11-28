import { bgProps, Eyebrow, Heading, Icon, Paragraphs, SmartButton } from '../components/ui.jsx'

// One card. The layout decides what the card shows.
function Card({ layout, item, index }) {
  const f = item.fields
  const number = String(index + 1).padStart(2, '0')

  if (layout === 'icon_row') {
    return (
      <li className="icon-tile">
        <span className="feature-icon feature-icon-lg"><Icon name={f.icon} aria-hidden="true" /></span>
        <h3>{f.title}</h3>
      </li>
    )
  }

  if (layout === 'icon_cards' || layout === 'numbered_cards' || layout === 'thumb_cards') {
    return (
      <article className={`card card-plain card-${layout}`}>
        <div className="card-plain-head">
          <span className="feature-icon feature-icon-sq"><Icon name={f.icon} aria-hidden="true" /></span>
          {layout === 'thumb_cards' && f.image && (
            <img className="card-thumb" src={f.image.url} alt={f.image.alt} loading="lazy" />
          )}
        </div>
        {layout === 'numbered_cards' && <span className="card-number">{number}</span>}
        <h3>{f.title}</h3>
        <Paragraphs value={f.text} />
      </article>
    )
  }

  return (
    <article className="card">
      <div className="card-top">
        <div className="card-media">
          {f.image && <img src={f.image.url} alt={f.image.alt} loading="lazy" />}
        </div>
        {f.icon && <span className="card-icon"><Icon name={f.icon} aria-hidden="true" /></span>}
      </div>
      <div className="card-body">
        <h3>{f.title}</h3>
        <Paragraphs value={f.text} />
      </div>
    </article>
  )
}

export default function CardGrid({ section }) {
  const f = section.fields
  const layout = f.layout
  const items = section.items ?? []
  const center = f.align === 'center' && layout !== 'intro_side'
  const cols = { '--cols': f.columns }
  const bg = bgProps(f)

  const head = (
    <header className={`section-head${center ? ' is-center' : ''}`}>
      <Eyebrow text={f.eyebrow} style={f.eyebrow_style} center={center} />
      <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
      <Paragraphs value={f.intro} className="section-intro" />
    </header>
  )

  const list =
    layout === 'icon_row' ? (
      <ul className="icon-row" style={cols}>
        {items.map((item, i) => <Card key={item.id} layout={layout} item={item} index={i} />)}
      </ul>
    ) : (
      <div className={`cards cards-${layout}`} style={cols}>
        {items.map((item, i) => <Card key={item.id} layout={layout} item={item} index={i} />)}
      </div>
    )

  return (
    <section
      className={`section ${layout === 'image_cards' || layout === 'intro_side' ? 'section-tint' : ''}${bg.className}`}
      style={bg.style}
      id={`section-${section.id}`}
    >
      <div className="container">
        {layout === 'intro_side' ? (
          <div className="intro-side">
            <div className="card intro-card">
              {head}
              {f.image && <img className="intro-image" src={f.image.url} alt={f.image.alt} loading="lazy" />}
            </div>
            {list}
          </div>
        ) : (
          <>
            {head}
            {list}
          </>
        )}

        {f.footnote && <p className="footnote">{f.footnote}</p>}
        {f.button && (
          <div className="section-action">
            <SmartButton button={f.button} />
          </div>
        )}
      </div>
    </section>
  )
}
