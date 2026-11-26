import {
  faBars,
  faMagnifyingGlass,
  faRightFromBracket,
  faUserGear,
} from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../AuthContext.jsx'
import { useLayoutData } from '../layoutData.js'
import { accessItems, libraryItems, settingsItems } from '../navConfig.js'
import ConfirmDialog from './ConfirmDialog.jsx'

function initials(name) {
  return (name || '?').split(/\s+/).map((w) => w[0]).join('').slice(0, 2).toUpperCase()
}

export default function TopBar({ onMenu }) {
  const { user, logout } = useAuth()
  const { pages } = useLayoutData()
  const navigate = useNavigate()
  const [query, setQuery] = useState('')
  const [menuOpen, setMenuOpen] = useState(false)
  const [confirming, setConfirming] = useState(false)
  const [busy, setBusy] = useState(false)
  const menuRef = useRef(null)

  // Light search: matches page names and admin screens only.
  const results = useMemo(() => {
    const q = query.trim().toLowerCase()
    if (!q) return []
    const items = [
      ...pages.map((p) => ({ label: p.menu_label, to: `/admin/pages/${p.slug}`, group: 'Page' })),
      ...libraryItems.map((i) => ({ label: i.label, to: i.to, group: 'Screen' })),
      ...settingsItems.map((i) => ({ label: i.label, to: i.to, group: 'Settings' })),
      { label: 'Enquiries', to: '/admin/enquiries', group: 'Screen' },
      ...(user?.is_super ? accessItems.map((i) => ({ label: i.label, to: i.to, group: 'Access control' })) : []),
    ]
    return items.filter((i) => i.label.toLowerCase().includes(q)).slice(0, 6)
  }, [query, pages, user])

  useEffect(() => {
    if (!menuOpen) return
    const close = (e) => !menuRef.current?.contains(e.target) && setMenuOpen(false)
    const esc = (e) => e.key === 'Escape' && setMenuOpen(false)
    document.addEventListener('mousedown', close)
    document.addEventListener('keydown', esc)
    return () => {
      document.removeEventListener('mousedown', close)
      document.removeEventListener('keydown', esc)
    }
  }, [menuOpen])

  function go(to) {
    setQuery('')
    navigate(to)
  }

  async function confirmLogout() {
    setBusy(true)
    await logout()
    navigate('/admin/login', { replace: true })
  }

  return (
    <header className="adm-top">
      <button type="button" className="adm-icon-btn adm-burger" onClick={onMenu} aria-label="Open menu">
        <FontAwesomeIcon icon={faBars} />
      </button>

      <div className="adm-search">
        <FontAwesomeIcon icon={faMagnifyingGlass} />
        <input
          type="search"
          placeholder="Search pages and screens"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' && results[0]) go(results[0].to)
            if (e.key === 'Escape') setQuery('')
          }}
          aria-label="Search"
        />
        {query.trim() && (
          <ul className="adm-search-results">
            {results.length ? (
              results.map((r) => (
                <li key={r.to}>
                  <button type="button" onClick={() => go(r.to)}>
                    {r.label} <span>{r.group}</span>
                  </button>
                </li>
              ))
            ) : (
              <li className="adm-search-empty">No matches</li>
            )}
          </ul>
        )}
      </div>

      <div className="adm-user" ref={menuRef}>
        <button
          type="button"
          className="adm-avatar"
          onClick={() => setMenuOpen((v) => !v)}
          aria-haspopup="menu"
          aria-expanded={menuOpen}
          aria-label="Account menu"
        >
          {initials(user?.display_name)}
        </button>
        {menuOpen && (
          <div className="adm-dropdown" role="menu">
            <div className="adm-dropdown-who">
              <b>{user?.display_name}</b>
              <span>{user?.email || user?.username}</span>
            </div>
            <Link to="/admin/profile" role="menuitem" onClick={() => setMenuOpen(false)}>
              <FontAwesomeIcon icon={faUserGear} /> Profile settings
            </Link>
            <button
              type="button"
              role="menuitem"
              className="adm-dropdown-danger"
              onClick={() => { setMenuOpen(false); setConfirming(true) }}
            >
              <FontAwesomeIcon icon={faRightFromBracket} /> Log out
            </button>
          </div>
        )}
      </div>

      {confirming && (
        <ConfirmDialog
          title="Log out?"
          message="You'll need to sign in again to use the admin panel."
          confirmLabel="Log out"
          busy={busy}
          onConfirm={confirmLogout}
          onCancel={() => setConfirming(false)}
        />
      )}
    </header>
  )
}
