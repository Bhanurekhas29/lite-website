import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { siteIcons } from '../../shared/icons.js'
import { bgProps, Eyebrow, Heading } from '../components/ui.jsx'
import { useSite } from '../SiteContext.jsx'

// Contact rows (from Settings > Contact and social) next to an image.
export default function ContactInfo({ section }) {
  const f = section.fields
  const { site } = useSite()
  const contact = site?.contact ?? {}
  const social = site?.social ?? {}

  const bg = bgProps(f)
  const website = contact.website
  const rows = [
    contact.email && { icon: siteIcons.envelope, label: 'Email', text: contact.email, href: `mailto:${contact.email}` },
    website && { icon: siteIcons.globe, label: 'Website', text: website, href: /^https?:/i.test(website) ? website : `https://${website}` },
    social.instagram && { icon: siteIcons.instagram, label: 'Instagram', text: social.instagram, href: `https://www.instagram.com/${social.instagram.replace(/^@/, '')}` },
    contact.phone && { icon: siteIcons.phone, label: 'Phone', text: contact.phone, href: `tel:${contact.phone.replace(/\s+/g, '')}`, note: contact.phone_note },
  ].filter(Boolean)

  return (
    <section className={`section${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className="container split">
        <div className="split-text">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
          <ul className="contact-rows">
            {rows.map((row) => (
              <li key={row.label}>
                <span className="contact-icon"><FontAwesomeIcon icon={row.icon} aria-hidden="true" /></span>
                <div>
                  <span className="contact-label">{row.label}</span>
                  <a href={row.href} {...(row.href.startsWith('http') ? { target: '_blank', rel: 'noopener noreferrer' } : {})}>{row.text}</a>
                  {row.note && <span className="contact-note">({row.note})</span>}
                </div>
              </li>
            ))}
            {contact.address && (
              <li>
                <span className="contact-icon"><FontAwesomeIcon icon={siteIcons.location} aria-hidden="true" /></span>
                <div>
                  <span className="contact-label">Address</span>
                  <span>{contact.address}</span>
                </div>
              </li>
            )}
          </ul>
        </div>
        {f.image && (
          <figure className="split-media has-frame">
            <img src={f.image.url} alt={f.image.alt} loading="lazy" />
          </figure>
        )}
      </div>
    </section>
  )
}
