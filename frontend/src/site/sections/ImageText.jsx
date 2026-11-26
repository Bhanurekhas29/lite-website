import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { siteIcons } from '../../shared/icons.js'
import { bgProps, Eyebrow, Heading, Icon, Paragraphs, SmartButton } from '../components/ui.jsx'

function List({ style, items }) {
  if (!items?.length) return null
  const label = (i) => i.fields.title || i.fields.text

  if (style === 'checklist') {
    return (
      <ul className="checklist">
        {items.map((i) => (
          <li key={i.id}>
            <span className="check"><FontAwesomeIcon icon={siteIcons.check} aria-hidden="true" /></span>
            {label(i)}
          </li>
        ))}
      </ul>
    )
  }
  if (style === 'tiles') {
    return (
      <ul className="tiles">
        {items.map((i) => (
          <li key={i.id}>
            <span className="feature-icon"><Icon name={i.fields.icon} aria-hidden="true" /></span>
            <span>{label(i)}</span>
          </li>
        ))}
      </ul>
    )
  }
  if (style === 'iconlist' || style === 'pills') {
    return (
      <ul className={style === 'pills' ? 'pills' : 'iconlist'}>
        {items.map((i) => (
          <li key={i.id}>
            <span className="feature-icon"><Icon name={i.fields.icon} aria-hidden="true" /></span>
            <span>{label(i)}</span>
          </li>
        ))}
      </ul>
    )
  }
  return (
    <ul className="features">
      {items.map((i) => (
        <li className="feature" key={i.id}>
          <span className="feature-icon"><Icon name={i.fields.icon} aria-hidden="true" /></span>
          <div>
            <h3>{i.fields.title}</h3>
            <p>{i.fields.text}</p>
          </div>
        </li>
      ))}
    </ul>
  )
}

export default function ImageText({ section }) {
  const f = section.fields
  const left = f.image_position === 'left'
  const script = f.badge_style === 'script'
  const bg = bgProps(f)

  const media = f.image && (
    <figure className={`split-media${f.frame ? ' has-frame' : ''}${f.image2 ? ' has-second' : ''}`}>
      <img src={f.image.url} alt={f.image.alt} loading="lazy" />
      {f.image2 && <img className="second" src={f.image2.url} alt={f.image2.alt} loading="lazy" />}
      {f.badge_image && <img className="script-badge-img" src={f.badge_image.url} alt={f.badge || f.badge_image.alt} />}
      {!f.badge_image && f.badge && script && <span className="script-badge">{f.badge}</span>}
      {((f.badge && !script && !f.badge_image) || f.caption) && (
        <figcaption>
          {f.badge && !script && <span className="badge">{f.badge}</span>}
          {f.caption && <span className="caption">{f.caption}</span>}
        </figcaption>
      )}
    </figure>
  )

  return (
    <section className={`section${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className={`container split${left ? ' split-reverse' : ''}${f.image_shape === 'wide' ? ' split-wide' : ''}`}>
        <div className="split-text">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
          <Paragraphs value={f.text1} />
          <Paragraphs value={f.text2} />
          <Paragraphs value={f.text3} />
          <Paragraphs value={f.text4} />
          <List style={f.items_style} items={section.items} />
          {f.note && <p className="list-note">{f.note}</p>}
          <SmartButton button={f.button} />
        </div>
        {media}
      </div>
    </section>
  )
}
