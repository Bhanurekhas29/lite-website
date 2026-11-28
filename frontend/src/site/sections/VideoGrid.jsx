import { bgProps, Eyebrow, Heading, Paragraphs } from '../components/ui.jsx'
import { VideoPlayer } from './Video.jsx'

// Two or more videos side by side (one under the other on phones), each with a title.
export default function VideoGrid({ section }) {
  const f = section.fields
  const items = section.items ?? []
  const bg = bgProps(f)
  return (
    <section className={`section section-soft${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className="container">
        <header className="section-head is-center">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} center />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
          <Paragraphs value={f.intro} className="section-intro" />
        </header>
        <div className="video-grid">
          {items.map((item) => (
            <figure className="video-card" key={item.id}>
              <VideoPlayer f={item.fields} name={item.fields.title} />
              {item.fields.title && <figcaption><h3>{item.fields.title}</h3></figcaption>}
            </figure>
          ))}
        </div>
      </div>
    </section>
  )
}
