import { faCheckDouble, faMagnifyingGlass } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useCallback, useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { api } from '../api.js'
import { formatShort } from '../formatDate.js'
import { useLayoutData } from '../layoutData.js'

const tabs = [
  ['all', 'All'],
  ['new', 'New'],
  ['read', 'Read'],
]

export default function Enquiries() {
  const { overview, reload } = useLayoutData()
  const timeZone = overview?.timezone
  const [params, setParams] = useSearchParams()
  const status = params.get('status') || 'all'
  const page = Number(params.get('page')) || 1
  const query = params.get('q') || ''

  const [search, setSearch] = useState(query)
  const [data, setData] = useState(null)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  const update = useCallback(
    (patch) => {
      const next = new URLSearchParams(params)
      Object.entries(patch).forEach(([k, v]) => (v ? next.set(k, v) : next.delete(k)))
      setParams(next, { replace: true })
    },
    [params, setParams],
  )

  // Search as you type, after a short pause.
  useEffect(() => {
    if (search === query) return undefined
    const timer = setTimeout(() => update({ q: search.trim(), page: '' }), 350)
    return () => clearTimeout(timer)
  }, [search, query, update])

  useEffect(() => {
    let cancelled = false
    const qs = new URLSearchParams({ status, page: String(page), q: query })
    api(`/admin/enquiries?${qs}`)
      .then((d) => {
        if (!cancelled) {
          setData(d)
          setError('')
        }
      })
      .catch((e) => !cancelled && setError(e.message))
    return () => {
      cancelled = true
    }
  }, [status, page, query])

  async function markAllRead() {
    setBusy(true)
    try {
      await api('/admin/enquiries/mark-all-read', { method: 'POST', body: {} })
      reload()
      const qs = new URLSearchParams({ status, page: String(page), q: query })
      setData(await api(`/admin/enquiries?${qs}`))
    } catch (e) {
      setError(e.message)
    } finally {
      setBusy(false)
    }
  }

  const counts = data?.counts

  return (
    <>
      <div className="adm-pagehead adm-pagehead-split">
        <h1>Enquiries</h1>
        {counts?.new > 0 && (
          <button type="button" className="adm-btn adm-btn-outline" onClick={markAllRead} disabled={busy}>
            <FontAwesomeIcon icon={faCheckDouble} /> Mark all as read
          </button>
        )}
      </div>

      <div className="adm-toolbar">
        <div className="adm-tabs" role="tablist" aria-label="Filter enquiries">
          {tabs.map(([key, label]) => (
            <button
              key={key}
              type="button"
              role="tab"
              aria-selected={status === key}
              className={`adm-tab${status === key ? ' is-active' : ''}`}
              onClick={() => update({ status: key === 'all' ? '' : key, page: '' })}
            >
              {label}
              {counts && <span>{counts[key]}</span>}
            </button>
          ))}
        </div>
        <div className="adm-search adm-search-wide">
          <FontAwesomeIcon icon={faMagnifyingGlass} />
          <input
            type="search"
            placeholder="Search name, email, company or location"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Search enquiries"
          />
        </div>
      </div>

      {error && <p className="adm-error" role="alert">{error}</p>}
      {!data && !error && <p className="adm-muted">Loading…</p>}

      {data && data.items.length === 0 && (
        <div className="adm-card">
          <p className="adm-muted">
            {query || status !== 'all'
              ? 'No enquiries match this filter.'
              : 'No enquiries yet. Messages sent from the contact form will appear here.'}
          </p>
        </div>
      )}

      {data && data.items.length > 0 && (
        <div className="adm-list" role="list">
          <div className="adm-list-head" aria-hidden="true">
            <span>Name</span>
            <span>Type of enquiry</span>
            <span>Location</span>
            <span>Received</span>
          </div>
          {data.items.map((e) => (
            <Link
              key={e.id}
              role="listitem"
              to={`/admin/enquiries/${e.id}`}
              className={`adm-row-link${e.status === 'new' ? ' is-new' : ''}`}
            >
              <span className="adm-row-name">
                {e.status === 'new' && <i className="adm-new-dot" title="New" aria-label="New" />}
                <b>{e.name}</b>
                <small>{e.company || e.email}</small>
              </span>
              <span className="adm-row-cell" data-label="Type">{e.enquiry_type || '–'}</span>
              <span className="adm-row-cell" data-label="Location">{e.event_location || '–'}</span>
              <span className="adm-row-cell adm-row-date" data-label="Received">{formatShort(e.created_at, timeZone)}</span>
            </Link>
          ))}
        </div>
      )}

      {data && data.pages > 1 && (
        <div className="adm-pager">
          <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" disabled={data.page <= 1} onClick={() => update({ page: String(data.page - 1) })}>
            Previous
          </button>
          <span className="adm-muted">Page {data.page} of {data.pages} · {data.total} enquiries</span>
          <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" disabled={data.page >= data.pages} onClick={() => update({ page: String(data.page + 1) })}>
            Next
          </button>
        </div>
      )}
    </>
  )
}
