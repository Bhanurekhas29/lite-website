import { faBars, faXmark } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useState } from 'react'
import { Link, NavLink } from 'react-router-dom'
import { useSite } from '../SiteContext.jsx'
import { SmartButton } from './ui.jsx'

export default function Header() {
  const { site } = useSite()
  const [open, setOpen] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const brand = site?.brand
  const items = site?.menu.header ?? []
  const close = () => setOpen(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24)
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  return (
    <header className={`site-header${scrolled ? " is-scrolled" : ""}`}>
      <div className="container header-inner">
        <Link to="/" className="logo" onClick={close} aria-label={`${brand?.name ?? 'Home'} home`}>
          {brand?.logo_header ? (
            <img src={brand.logo_header.url} alt={brand.logo_header.alt || brand.name} />
          ) : (
            <span className="logo-text">{brand?.name ?? ''}</span>
          )}
        </Link>

        <nav className="nav" aria-label="Main">
          {items.map((item) => (
            <NavLink key={item.href} to={item.href} end>
              {item.label}
            </NavLink>
          ))}
        </nav>

        <SmartButton button={brand?.header_button} className="header-cta" />

        <button
          type="button"
          className="burger"
          onClick={() => setOpen((v) => !v)}
          aria-expanded={open}
          aria-label={open ? 'Close menu' : 'Open menu'}
        >
          <FontAwesomeIcon icon={open ? faXmark : faBars} />
        </button>
      </div>

      {open && (
        <nav className="mobile-nav" aria-label="Main">
          <div className="container">
            {items.map((item) => (
              <NavLink key={item.href} to={item.href} end onClick={close}>
                {item.label}
              </NavLink>
            ))}
            <SmartButton button={brand?.header_button} className="mobile-cta" />
          </div>
        </nav>
      )}
    </header>
  )
}
