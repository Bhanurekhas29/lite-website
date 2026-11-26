import {
  faArrowUpRightFromSquare,
  faCloudArrowUp,
  faDiagramProject,
  faEnvelope,
  faEnvelopeOpenText,
  faFileExcel,
  faImages,
  faInbox,
  faTriangleExclamation,
  faUserShield,
  faWindowRestore,
} from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../api.js'
import { useAuth } from '../AuthContext.jsx'
import { formatShort } from '../formatDate.js'
import { useLayoutData } from '../layoutData.js'

function greeting(name) {
  const hour = new Date().getHours()
  const part = hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening'
  return `${part}, ${name?.split(' ')[0] || 'there'}`
}

function StatCard({ icon, label, value, note, to, tone }) {
  const body = (
    <>
      <span className={`adm-stat-icon${tone ? ` is-${tone}` : ''}`}><FontAwesomeIcon icon={icon} aria-hidden="true" /></span>
      <span className="adm-stat-text">
        <b>{value ?? '–'}</b>
        <span>{label}</span>
        {note && <small>{note}</small>}
      </span>
    </>
  )
  return to ? <Link to={to} className="adm-stat2">{body}</Link> : <div className="adm-stat2">{body}</div>
}

// A bar for each of the last 14 days.
function DailyChart({ days }) {
  const max = Math.max(1, ...days.map((d) => d.count))
  const total = days.reduce((n, d) => n + d.count, 0)
  return (
    <div className="adm-chart" role="img" aria-label={`Enquiries per day over the last 14 days: ${total} in total`}>
      <div className="adm-chart-bars">
        {days.map((d, i) => (
          <div className="adm-chart-col" key={d.date} title={`${d.label}: ${d.count} enquir${d.count === 1 ? 'y' : 'ies'}`}>
            <span className="adm-chart-num">{d.count > 0 ? d.count : ''}</span>
            <span className="adm-chart-bar" style={{ height: `${(d.count / max) * 100}%`, '--i': i }} />
            <span className="adm-chart-label">{i % 2 === 0 || days.length < 8 ? d.label : ''}</span>
          </div>
        ))}
      </div>
      {total === 0 && <p className="adm-chart-empty adm-muted">No enquiries in the last 14 days.</p>}
    </div>
  )
}

export default function Dashboard() {
  const { user } = useAuth()
  const { overview, pages } = useLayoutData()
  const [data, setData] = useState(null)
  const [error, setError] = useState('')

  useEffect(() => {
    let cancelled = false
    api('/admin/dashboard')
      .then((d) => !cancelled && setData(d))
      .catch((e) => !cancelled && setError(e.message))
    return () => {
      cancelled = true
    }
  }, [])

  const c = data?.cards
  const timeZone = overview?.timezone
  const topMax = Math.max(1, ...(data?.types ?? []).map((t) => t.total))
  const today = new Intl.DateTimeFormat('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone }).format(new Date())

  return (
    <>
      <div className="adm-pagehead adm-welcome">
        <div>
          <h1>{greeting(user?.display_name)}</h1>
          <div className="adm-muted">{today}. Here is how the website is doing.</div>
        </div>
        <a className="adm-btn adm-btn-outline" href="/" target="_blank" rel="noopener noreferrer">
          <FontAwesomeIcon icon={faArrowUpRightFromSquare} /> View website
        </a>
      </div>

      {error && <p className="adm-error" role="alert">{error}</p>}

      {data && !data.email_ready && (
        <div className="adm-reminder">
          <FontAwesomeIcon icon={faTriangleExclamation} aria-hidden="true" />
          <span>
            Email is not set up yet, so new enquiries and password reset links are not emailed.{' '}
            <Link to="/admin/settings/email">Set up email</Link>
          </span>
        </div>
      )}

      <div className="adm-stats2">
        <StatCard icon={faInbox} tone="pink" label="New enquiries" value={c?.new_enquiries} note="Waiting to be read" to="/admin/enquiries?status=new" />
        <StatCard icon={faEnvelopeOpenText} label="Enquiries this month" value={c?.enquiries_month} note={c ? `${c.enquiries_total} in total` : ''} to="/admin/enquiries" />
        <StatCard icon={faWindowRestore} label="Pages live" value={c ? `${c.pages_visible} of ${c.pages_total}` : null} note="Shown on the website" />
        <StatCard icon={faDiagramProject} label="Projects shown" value={c?.projects} note="On Our Work" to="/admin/projects" />
        <StatCard icon={faImages} label="Images" value={c?.media} note="In the media library" to="/admin/media" />
        <StatCard icon={faUserShield} label="Active admins" value={c?.admins} note={user?.is_super ? 'Manage in Users' : ''} to={user?.is_super ? '/admin/users' : undefined} />
      </div>

      <div className="adm-dash-grid">
        <section className="adm-card adm-dash-wide">
          <h2 className="adm-card-title">Enquiries, last 14 days</h2>
          {data ? <DailyChart days={data.daily} /> : <p className="adm-muted">Loading…</p>}
        </section>

        <section className="adm-card">
          <h2 className="adm-card-title">What people ask about</h2>
          {data && data.types.length === 0 && <p className="adm-muted">Once enquiries arrive, the most common types show here.</p>}
          <ul className="adm-bars">
            {data?.types.map((t) => (
              <li key={t.name}>
                <span className="adm-bars-label">{t.name}</span>
                <span className="adm-bars-track"><span className="adm-bars-fill" style={{ width: `${(t.total / topMax) * 100}%` }} /></span>
                <b>{t.total}</b>
              </li>
            ))}
          </ul>
        </section>

        <section className="adm-card">
          <h2 className="adm-card-title">Latest enquiries</h2>
          {data && data.recent.length === 0 && <p className="adm-muted">No enquiries yet. Messages from the contact form will appear here.</p>}
          <ul className="adm-recent">
            {data?.recent.map((e) => (
              <li key={e.id}>
                <Link to={`/admin/enquiries/${e.id}`}>
                  <span className="adm-recent-main">
                    {e.status === 'new' && <i className="adm-new-dot" aria-label="New" />}
                    <b>{e.name}</b>
                    <small>{e.enquiry_type || e.email}</small>
                  </span>
                  <span className="adm-muted adm-recent-date">{formatShort(e.created_at, timeZone)}</span>
                </Link>
              </li>
            ))}
          </ul>
          {data?.recent.length > 0 && <Link className="adm-more" to="/admin/enquiries">See all enquiries</Link>}
        </section>

        <section className="adm-card">
          <h2 className="adm-card-title">Quick actions</h2>
          <div className="adm-quick">
            <Link className="adm-quick-item" to="/admin/enquiries"><FontAwesomeIcon icon={faFileExcel} /> Export enquiries</Link>
            <Link className="adm-quick-item" to="/admin/projects/new"><FontAwesomeIcon icon={faDiagramProject} /> Add a project</Link>
            <Link className="adm-quick-item" to="/admin/media"><FontAwesomeIcon icon={faCloudArrowUp} /> Upload images</Link>
            <Link className="adm-quick-item" to="/admin/enquiries?status=new"><FontAwesomeIcon icon={faEnvelope} /> Read new enquiries</Link>
          </div>
        </section>

        <section className="adm-card">
          <h2 className="adm-card-title">Edit a page</h2>
          <div className="adm-chips adm-pagechips">
            {pages.map((p) => (
              <Link key={p.id} to={`/admin/pages/${p.slug}`} className={`adm-chip-link${p.is_visible ? '' : ' is-hidden'}`} title={p.is_visible ? 'Shown on the website' : 'Hidden'}>
                {p.menu_label}
              </Link>
            ))}
          </div>
        </section>
      </div>
    </>
  )
}
