import { faFacebookF, faInstagram, faLinkedinIn, faWhatsapp } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useSite } from '../SiteContext.jsx'

const clean = (value) => (typeof value === 'string' ? value.trim() : '')
const asUrl = (value) => (/^https?:\/\//i.test(value) ? value : `https://${value}`)

// The social links the client has filled in under Settings > Contact and social, as round icon buttons.
// A network that is left empty is not shown at all.
export function socialItems(social = {}) {
  const instagram = clean(social.instagram)
  const facebook = clean(social.facebook)
  const linkedin = clean(social.linkedin)
  const whatsapp = clean(social.whatsapp).replace(/\D/g, '')
  return [
    instagram && {
      key: 'instagram',
      label: 'Instagram',
      icon: faInstagram,
      href: /^https?:\/\//i.test(instagram) || instagram.includes('instagram.com') ? asUrl(instagram) : `https://www.instagram.com/${instagram.replace(/^@/, '')}`,
    },
    facebook && { key: 'facebook', label: 'Facebook', icon: faFacebookF, href: asUrl(facebook) },
    linkedin && { key: 'linkedin', label: 'LinkedIn', icon: faLinkedinIn, href: asUrl(linkedin) },
    whatsapp && { key: 'whatsapp', label: 'WhatsApp', icon: faWhatsapp, href: `https://wa.me/${whatsapp}` },
  ].filter(Boolean)
}

export default function SocialLinks({ className = '' }) {
  const { site } = useSite()
  const items = socialItems(site?.social)
  if (items.length === 0) return null
  return (
    <ul className={`social-links ${className}`} aria-label="Follow us">
      {items.map((s) => (
        <li key={s.key}>
          <a href={s.href} target="_blank" rel="noopener noreferrer" aria-label={`${site?.brand?.name || 'E for Events'} on ${s.label}`} title={s.label}>
            <FontAwesomeIcon icon={s.icon} aria-hidden="true" />
          </a>
        </li>
      ))}
    </ul>
  )
}
