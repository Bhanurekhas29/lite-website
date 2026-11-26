import { bgProps, Eyebrow, Heading, Icon, Paragraphs } from '../components/ui.jsx'

// Numbered steps with a connecting line. Icons are optional.
export default function Steps({ section }) {
  const f = section.fields
  const items = section.items ?? []
  const bg = bgProps(f)
  return (
    <section className={`section${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className="container">
        <header className="section-head is-center">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} center />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
          <Paragraphs value={f.intro} className="section-intro" />
        </header>
        <ol className="steps" style={{ '--steps': items.length }}>
          {items.map((item, i) => (
            <li className={`step${item.fields.icon ? ' has-icon' : ''}`} key={item.id}>
              <div className="step-mark">
                <span className="step-number">{i + 1}</span>
              </div>
              {item.fields.icon && (
                <div className="step-line" aria-hidden="true">
                  <Icon name={item.fields.icon} className="step-line-icon" />
                </div>
              )}
              <h3>{item.fields.title}</h3>
              <Paragraphs value={item.fields.text} />
            </li>
          ))}
        </ol>
      </div>
    </section>
  )
}
