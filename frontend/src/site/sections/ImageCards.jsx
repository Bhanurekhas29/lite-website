import { bgProps, Eyebrow, Heading, Icon, Paragraphs, SmartButton } from '../components/ui.jsx'

export default function ImageCards({ section }) {
  const f = section.fields
  const stacked = f.layout === 'stacked'
  const bg = bgProps(f)
  return (
    <section className={`section section-warm${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className="container">
        <header className="section-head">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
        </header>

        <div className={`hcards${stacked ? ' hcards-stacked' : ''}`}>
          {section.items?.map((item) => (
            <article className="hcard" key={item.id}>
              <div className="hcard-top">
                <div className="hcard-media">
                  {item.fields.image && <img src={item.fields.image.url} alt={item.fields.image.alt} loading="lazy" />}
                </div>
                {stacked && item.fields.icon && (
                  <span className="card-icon"><Icon name={item.fields.icon} aria-hidden="true" /></span>
                )}
              </div>
              <div className="hcard-body">
                {!stacked && (item.fields.tag || item.fields.icon) && (
                  <p className="hcard-tag">
                    <Icon name={item.fields.icon} aria-hidden="true" /> {item.fields.tag}
                  </p>
                )}
                <h3>{item.fields.title}</h3>
                <Paragraphs value={item.fields.text} />
                <SmartButton button={item.fields.button} />
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}
