import { faCheckDouble, faEye, faFileExcel, faMagnifyingGlass, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useCallback, useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { api, downloadFile } from '../api.js'
import ConfirmDialog from '../components/ConfirmDialog.jsx'
import Pager from '../components/Pager.jsx'
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
  const perPage = Number(params.get('per_page')) || 20

  const [search, setSearch] = useState(query)
  const [data, setData] = useState(null)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [busy, setBusy] = useState(false)
  const [selected, setSelected] = useState(() => new Set())
  const [toDelete, setToDelete] = useState(null) // an array of ids, or null

  const update = useCallback(
    (patch) => {
      const next = new URLSearchParams(params)
      Object.entries(patch).forEach(([k, v]) => (v ? next.set(k, v) : next.delete(k)))
      setParams(next, { replace: true })
    },
    [params, setParams],
  )

  const qs = useCallback(
    () => new URLSearchParams({ status, page: String(page), q: query, per_page: String(perPage) }),
    [status, page, query, perPage],
  )

  // Search as you type, after a short pause.
  useEffect(() => {
    if (search === query) return undefined
    const timer = setTimeout(() => update({ q: search.trim(), page: '' }), 350)
    return () => clearTimeout(timer)
  }, [search, query, update])

  const load = useCallback(async () => {
    try {
      const d = await api(`/admin/enquiries?${qs()}`)
      setData(d)
      setError('')
      // Page is past the end (for example after deleting the last rows): go back one.
      if (d.items.length === 0 && d.page > 1) update({ page: String(d.page - 1) })
    } catch (e) {
      setError(e.message)
    }
  }, [qs, update])

  useEffect(() => {
    load()
    setSelected(new Set())
  }, [load])

  async function markAllRead() {
    setBusy(true)
    try {
      await api('/admin/enquiries/mark-all-read', { method: 'POST', body: {} })
      reload()
      await load()
    } catch (e) {
      setError(e.message)
    } finally {
      setBusy(false)
    }
  }

  async function exportExcel() {
    setBusy(true)
    setMessage('')
    try {
      const filter = new URLSearchParams({ status, q: query })
      await downloadFile(`/admin/enquiries/export?${filter}`, `enquiries-${new Date().toISOString().slice(0, 10)}.xlsx`)
    } catch (e) {
      setError(e.message)
    } finally {
      setBusy(false)
    }
  }

  async function confirmDelete() {
    setBusy(true)
    try {
      const r = await api('/admin/enquiries/delete', { method: 'POST', body: { ids: toDelete } })
      setMessage(`Deleted ${r.deleted} enquir${r.deleted === 1 ? 'y' : 'ies'}.`)
      setToDelete(null)
      setSelected(new Set())
      reload()
      await load()
    } catch (e) {
      setError(e.message)
      setToDelete(null)
    } finally {
      setBusy(false)
    }
  }

  const toggle = (id) =>
    setSelected((s) => {
      const next = new Set(s)
      if (next.has(id)) next.delete(id)
      else next.add(id)
      return next
    })

  const counts = data?.counts
  const ids = data?.items.map((e) => e.id) ?? []
  const allOnPage = ids.length > 0 && ids.every((id) => selected.has(id))

  return (
    <>
      <div className="adm-pagehead adm-pagehead-split">
        <h1>Enquiries</h1>
        <div className="adm-actions">
          {counts?.new > 0 && (
            <button type="button" className="adm-btn adm-btn-outline" onClick={markAllRead} disabled={busy}>
              <FontAwesomeIcon icon={faCheckDouble} /> Mark all as read
            </button>
          )}
          <button type="button" className="adm-btn" onClick={exportExcel} disabled={busy || !data?.total}>
            <FontAwesomeIcon icon={faFileExcel} /> Export to Excel
          </button>
        </div>
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

      {message && <p className="adm-status-ok adm-media-message" role="status">{message}</p>}
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
        <>
          <div className="adm-erows-top">
            <label className="adm-selectall">
              <input type="checkbox" checked={allOnPage} onChange={() => setSelected(allOnPage ? new Set() : new Set(ids))} />
              Select all on this page
            </label>
            <label className="adm-perpage">
              Show
              <select value={perPage} onChange={(e) => update({ per_page: e.target.value === '20' ? '' : e.target.value, page: '' })}>
                {[10, 20, 50, 100].map((n) => <option key={n} value={n}>{n}</option>)}
              </select>
              per page
            </label>
          </div>

          <div className="adm-list" role="list">
            <div className="adm-list-head adm-erow-head" aria-hidden="true">
              <span />
              <span>Name</span>
              <span>Type of enquiry</span>
              <span>Location</span>
              <span>Received</span>
              <span>Actions</span>
            </div>
            {data.items.map((e) => (
              <div className={`adm-erow${e.status === 'new' ? ' is-new' : ''}${selected.has(e.id) ? ' is-selected' : ''}`} role="listitem" key={e.id}>
                <input
                  type="checkbox"
                  className="adm-echeck"
                  checked={selected.has(e.id)}
                  onChange={() => toggle(e.id)}
                  aria-label={`Select the enquiry from ${e.name}`}
                />
                <Link to={`/admin/enquiries/${e.id}`} className="adm-erow-main">
                  <span className="adm-row-name">
                    {e.status === 'new' && <i className="adm-new-dot" title="New" aria-label="New" />}
                    <b>{e.name}</b>
                    <small>{e.company || e.email}</small>
                  </span>
                  <span className="adm-row-cell" data-label="Type">{e.enquiry_type || '–'}</span>
                  <span className="adm-row-cell" data-label="Location">{e.event_location || '–'}</span>
                  <span className="adm-row-cell adm-row-date" data-label="Received">{formatShort(e.created_at, timeZone)}</span>
                </Link>
                <span className="adm-erow-actions">
                  <Link className="adm-icon-btn" to={`/admin/enquiries/${e.id}`} aria-label={`View the enquiry from ${e.name}`} title="View">
                    <FontAwesomeIcon icon={faEye} />
                  </Link>
                  <button type="button" className="adm-icon-btn adm-icon-danger" onClick={() => setToDelete([e.id])} aria-label={`Delete the enquiry from ${e.name}`} title="Delete">
                    <FontAwesomeIcon icon={faTrash} />
                  </button>
                </span>
              </div>
            ))}
          </div>
        </>
      )}

      {data && <Pager page={data.page} pages={data.pages} total={data.total} noun="enquiries" onPage={(n) => update({ page: String(n) })} />}

      {selected.size > 0 && (
        <div className="adm-bulkbar">
          <span>{selected.size} selected</span>
          <button type="button" className="adm-link-btn" onClick={() => setSelected(new Set())}>Clear</button>
          <button type="button" className="adm-btn adm-btn-sm adm-btn-danger-solid" onClick={() => setToDelete([...selected])}>
            <FontAwesomeIcon icon={faTrash} /> Delete selected
          </button>
        </div>
      )}

      {toDelete && (
        <ConfirmDialog
          title={`Delete ${toDelete.length} enquir${toDelete.length === 1 ? 'y' : 'ies'}?`}
          message="They will be permanently deleted. This can't be undone. Export to Excel first if you want to keep a copy."
          confirmLabel="Delete"
          busy={busy}
          onConfirm={confirmDelete}
          onCancel={() => setToDelete(null)}
        />
      )}
    </>
  )
}
