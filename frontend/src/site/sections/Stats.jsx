import CountUp from '../components/CountUp.jsx'
import { bgProps } from '../components/ui.jsx'

export default function Stats({ section }) {
  if (!section.items?.length) return null
  const bg = bgProps(section.fields)
  return (
    <section className={`stats${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className="container">
        <div className="stats-card">
          {section.items.map((item) => (
            <div className="stat" key={item.id}>
              <div className="stat-number"><CountUp value={item.fields.number} /></div>
              <div className="stat-label">{item.fields.label}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
