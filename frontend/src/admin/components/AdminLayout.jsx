import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHouse } from '@fortawesome/free-solid-svg-icons'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { api } from '../api.js'
import { useAuth } from '../AuthContext.jsx'
import { LayoutDataContext } from '../layoutData.js'
import { accessItems, dashboardItems, libraryItems, pageIcons, settingsItems } from '../navConfig.js'
import TopBar from './TopBar.jsx'

function NavItem({ item, badge, onNavigate }) {
  const content = (
    <>
      <FontAwesomeIcon icon={item.icon} fixedWidth />
      <span className="adm-nav-label">{item.label}</span>
      {item.dot !== undefined && <span className={`adm-dot${item.dot ? '' : ' adm-dot-off'}`} title={item.dot ? 'Visible' : 'Hidden'} />}
      {badge > 0 && <span className="adm-badge">{badge}</span>}
    </>
  )
  if (item.href) {
    return (
      <a className="adm-nav-item" href={item.href} target="_blank" rel="noopener noreferrer">
        {content}
      </a>
    )
  }
  return (
    <NavLink to={item.to} end={item.end} className="adm-nav-item" onClick={onNavigate}>
      {content}
    </NavLink>
  )
}

export default function AdminLayout() {
  const { user } = useAuth()
  const [pages, setPages] = useState([])
  const [overview, setOverview] = useState(null)
  const [drawer, setDrawer] = useState(false)

  const reload = useCallback(() => {
    api('/admin/pages').then((d) => setPages(d.pages)).catch(() => {})
    api('/admin/overview').then(setOverview).catch(() => {})
  }, [])

  useEffect(reload, [reload])

  const value = useMemo(() => ({ pages, overview, reload }), [pages, overview, reload])
  const close = () => setDrawer(false)

  const pageItems = pages.map((p) => ({
    label: p.menu_label,
    to: `/admin/pages/${p.slug}`,
    icon: pageIcons[p.slug] || faHouse,
    dot: p.is_visible,
  }))

  const groups = [
    ['Dashboard', dashboardItems],
    ['Website pages', pageItems],
    ['Library', libraryItems],
    ['Settings', settingsItems],
    ...(user?.is_super ? [['Access control', accessItems]] : []),
  ]

  return (
    <LayoutDataContext.Provider value={value}>
      <div className="adm-shell">
        {drawer && <div className="adm-scrim" onClick={close} />}
        <aside className={`adm-sidebar${drawer ? ' is-open' : ''}`} aria-label="Admin menu">
          <div className="adm-logo">
            E for Events
            <small>Admin</small>
          </div>
          <nav>
            {groups.map(([title, items]) => (
              <div key={title}>
                <div className="adm-group">{title}</div>
                {items.map((item) => (
                  <NavItem
                    key={item.label}
                    item={item}
                    badge={item.badge ? overview?.[item.badge] : 0}
                    onNavigate={close}
                  />
                ))}
              </div>
            ))}
          </nav>
        </aside>

        <div className="adm-main">
          <TopBar onMenu={() => setDrawer(true)} />
          <main className="adm-content">
            <Outlet />
          </main>
        </div>
      </div>
    </LayoutDataContext.Provider>
  )
}
