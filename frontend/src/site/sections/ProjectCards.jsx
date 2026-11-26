import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { siteIcons } from '../../shared/icons.js'
import { Eyebrow, Heading, Icon, Paragraphs, SmartButton } from '../components/ui.jsx'

function Project({ p, detailed }) {
  return (
    <article className={`card project${p.hover_image ? " has-hover" : ""}`}>
      <div className="card-top">
        <div className="project-media">
          {p.image && <img className="project-main" src={p.image.url} alt={p.image.alt} loading="lazy" />}
          {p.hover_image && <img className="project-hover" src={p.hover_image.url} alt="" aria-hidden="true" loading="lazy" />}
        </div>
        {detailed && p.category_icon && (
          <span className="card-icon"><Icon name={p.category_icon} aria-hidden="true" /></span>
        )}
      </div>
      <div className="card-body">
        {!detailed && (
          <p className="project-category"><Icon name={p.category_icon} aria-hidden="true" /> {p.category}</p>
        )}
        <h3>{p.title}</h3>

        {detailed ? (
          <>
            <ul className="project-facts">
              {p.year && <li><FontAwesomeIcon icon={siteIcons.calendar} aria-hidden="true" /> <b>Date:</b> {p.year}</li>}
              {p.location && <li><FontAwesomeIcon icon={siteIcons.location} aria-hidden="true" /> <b>Location:</b> {p.location}</li>}
              {p.role && <li><FontAwesomeIcon icon={siteIcons.user} aria-hidden="true" /> <b>Our Role:</b> {p.role}</li>}
            </ul>
            {p.services.length > 0 && (
              <div className="project-services">
                <b>Services Provided:</b>
                <ul>
                  {p.services.map((s) => (
                    <li key={s}><FontAwesomeIcon icon={siteIcons.check} aria-hidden="true" /> {s}</li>
                  ))}
                </ul>
              </div>
            )}
          </>
        ) : (
          <dl className="project-meta">
            {p.role && <div><dt>E for Events Role:</dt> <dd>{p.role}</dd></div>}
            {p.services.length > 0 && <div><dt>Services:</dt> <dd>{p.services.join(', ')}</dd></div>}
          </dl>
        )}
      </div>
    </article>
  )
}

export default function ProjectCards({ section }) {
  const f = section.fields
  const projects = section.projects ?? []
  const detailed = f.style === 'detailed'
  return (
    <section className="section section-tint" id={`section-${section.id}`}>
      <div className="container">
        <header className="section-head section-head-row">
          <div>
            <Eyebrow text={f.eyebrow} style={f.eyebrow_style} />
            <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
            <Paragraphs value={f.intro} className="section-intro" />
          </div>
          <SmartButton button={f.button} className="head-action" />
        </header>

        {projects.length > 0 && (
          <div className="cards">
            {projects.map((p) => <Project key={p.id} p={p} detailed={detailed} />)}
          </div>
        )}
      </div>
    </section>
  )
}
