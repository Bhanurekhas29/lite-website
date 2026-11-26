import { useEffect } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import { useSite } from '../SiteContext.jsx'
import BackToTop from './BackToTop.jsx'
import Footer from './Footer.jsx'
import Header from './Header.jsx'

// Header, page content and footer. Also scrolls to the top on every page change
// and applies the favicon from the Brand settings.
export default function Layout() {
  const { site, error } = useSite()
  const { pathname } = useLocation()

  useEffect(() => {
    window.scrollTo(0, 0)
  }, [pathname])

  const favicon = site?.brand.favicon?.url
  useEffect(() => {
    if (!favicon) return
    let link = document.querySelector('link[rel="icon"]')
    if (!link) {
      link = document.createElement('link')
      link.rel = 'icon'
      document.head.appendChild(link)
    }
    link.href = favicon
  }, [favicon])

  if (error) {
    return (
      <main className="container site-message">
        <h1>We'll be right back</h1>
        <p>The website could not be loaded. Please try again in a moment.</p>
      </main>
    )
  }

  return (
    <div className="site">
      <Header />
      <main className="site-main">
        <Outlet />
      </main>
      <Footer />
      <BackToTop />
    </div>
  )
}
