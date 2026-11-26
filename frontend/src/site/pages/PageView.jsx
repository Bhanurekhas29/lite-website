import { useEffect, useState } from 'react'
import { useReveal } from '../useReveal.js'
import { Link } from 'react-router-dom'
import { getJson } from '../api.js'
import { renderSection } from '../sections/index.jsx'
import { useSite } from '../SiteContext.jsx'

// Pages whose scroll animations are switched on so far.
const ANIMATED_PAGES = ['home', 'about', 'concert-partnerships', 'hindu-tamil-weddings', 'event-solutions', 'sports-events-hospitality', 'our-work', 'contact']

function setMeta(name, content) {
  let tag = document.querySelector(`meta[name="${name}"]`)
  if (!tag) {
    tag = document.createElement('meta')
    tag.name = name
    document.head.appendChild(tag)
  }
  tag.content = content
}

// Shows one page: loads its visible sections and draws them in order.
export default function PageView({ slug }) {
  const { site } = useSite()
  const [state, setState] = useState({ status: 'loading', data: null })

  useEffect(() => {
    let cancelled = false
    getJson(`/pages/${slug}`)
      .then((data) => !cancelled && setState({ status: 'ok', data }))
      .catch((e) => !cancelled && setState({ status: e.status === 404 ? 'missing' : 'error', data: null }))
    return () => {
      cancelled = true
    }
  }, [slug])

  const page = state.data?.page
  useEffect(() => {
    if (!page) return
    document.title = page.seo_title
    setMeta('description', page.seo_description || site?.seo.description || '')
  }, [page, site])

  useReveal(state.status === 'ok' && ANIMATED_PAGES.includes(slug))

  // lets the stylesheet give one page its own touches
  useEffect(() => {
    document.body.dataset.page = slug
    return () => {
      delete document.body.dataset.page
    }
  }, [slug])

  if (state.status === 'loading') return <div className="page-loading" aria-busy="true" />

  if (state.status !== 'ok') {
    return (
      <section className="container site-message">
        <h1>{state.status === 'missing' ? 'Page not found' : 'Something went wrong'}</h1>
        <p>
          {state.status === 'missing'
            ? "We couldn't find the page you were looking for."
            : 'The page could not be loaded. Please try again in a moment.'}
        </p>
        <Link className="btn btn-primary" to="/">Back to home</Link>
      </section>
    )
  }

  return <>{state.data.sections.map((section) => renderSection(section))}</>
}
