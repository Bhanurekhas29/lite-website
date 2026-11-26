import { Link } from 'react-router-dom'
import { useSite } from '../SiteContext.jsx'

function instagramUrl(handle) {
  return `https://www.instagram.com/${handle.replace(/^@/, '')}`
}

export default function Footer() {
  const { site } = useSite()
  if (!site) return <footer className="site-footer" />
  const { brand, contact, social, menu } = site
  const website = contact.website
  const websiteUrl = /^https?:/i.test(website) ? website : `https://${website}`

  return (
    <footer className="site-footer">
      <div className="container footer-grid">
        <div className="footer-brand">
          <Link to="/" className="footer-logo">
            {brand.logo_footer ? (
              <img src={brand.logo_footer.url} alt={brand.logo_footer.alt || brand.name} />
            ) : null}
            <span>{brand.name}</span>
          </Link>
          <p className="footer-tagline">{brand.footer_tagline}</p>
          <p className="footer-desc">{brand.footer_description}</p>
        </div>

        <div>
          <h3 className="footer-title">Explore</h3>
          <ul className="footer-links">
            {menu.footer.map((item) => (
              <li key={item.href}><Link to={item.href}>{item.label}</Link></li>
            ))}
          </ul>
        </div>

        <div>
          <h3 className="footer-title">Contact</h3>
          <ul className="footer-contact">
            {contact.email && <li>Email: <a href={`mailto:${contact.email}`}>{contact.email}</a></li>}
            {website && <li>Website: <a href={websiteUrl} target="_blank" rel="noopener noreferrer">{website}</a></li>}
            {contact.phone && <li>Phone: <a href={`tel:${contact.phone.replace(/\s+/g, '')}`}>{contact.phone}</a></li>}
            {social.instagram && (
              <li>Instagram: <a href={instagramUrl(social.instagram)} target="_blank" rel="noopener noreferrer">{social.instagram}</a></li>
            )}
            {contact.address && <li>{contact.address}</li>}
          </ul>
        </div>
      </div>

      <div className="container footer-bottom">
        <p>{brand.footer_copyright}</p>
        <p>{brand.footer_right_text}</p>
      </div>

      {brand.credits.enabled && brand.credits.text && (
        <div className="container footer-credits">
          {brand.credits.url ? (
            <a href={brand.credits.url} target="_blank" rel="noopener noreferrer">{brand.credits.text}</a>
          ) : (
            <span>{brand.credits.text}</span>
          )}
        </div>
      )}
    </footer>
  )
}
