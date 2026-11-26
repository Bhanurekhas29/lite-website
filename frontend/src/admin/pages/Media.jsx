import { faCloudArrowUp, faCopy, faMagnifyingGlass, faRepeat, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useCallback, useEffect, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { api, replaceImage, uploadImage } from '../api.js'
import ConfirmDialog from '../components/ConfirmDialog.jsx'
import Modal from '../components/Modal.jsx'
import { formatBytes, formatDateTime } from '../formatDate.js'
import { useLayoutData } from '../layoutData.js'

const tabs = [
  ['all', 'All'],
  ['used', 'In use'],
  ['unused', 'Unused'],
]
const SMALL_WIDTH = 1000

// The detail panel for one image: alt text, rename, replace, where it is used, delete.
function ImageDetail({ id, onClose, onChanged }) {
  const { overview } = useLayoutData()
  const [item, setItem] = useState(null)
  const [draft, setDraft] = useState({ name: '', alt: '' })
  const [status, setStatus] = useState({ kind: '', text: '' })
  const [busy, setBusy] = useState(false)
  const [confirming, setConfirming] = useState(false)
  const fileRef = useRef(null)

  useEffect(() => {
    let cancelled = false
    api(`/admin/media/${id}`)
      .then((d) => {
        if (cancelled) return
        setItem(d.media)
        setDraft({ name: d.media.name, alt: d.media.alt })
      })
      .catch((e) => !cancelled && setStatus({ kind: 'error', text: e.message }))
    return () => {
      cancelled = true
    }
  }, [id])

  const adopt = (media) => {
    setItem(media)
    setDraft({ name: media.name, alt: media.alt })
    onChanged(media)
  }

  async function save() {
    setBusy(true)
    setStatus({ kind: '', text: '' })
    try {
      const d = await api(`/admin/media/${id}`, { method: 'PUT', body: draft })
      adopt(d.media)
      setStatus({ kind: 'ok', text: 'Saved' })
    } catch (e) {
      setStatus({ kind: 'error', text: e.message })
    } finally {
      setBusy(false)
    }
  }

  async function replace(event) {
    const file = event.target.files?.[0]
    event.target.value = ''
    if (!file) return
    setBusy(true)
    setStatus({ kind: '', text: '' })
    try {
      const d = await replaceImage(id, file)
      adopt(d.media)
      setStatus({ kind: 'ok', text: 'Image replaced. Every page that uses it now shows the new picture.' })
    } catch (e) {
      setStatus({ kind: 'error', text: e.message })
    } finally {
      setBusy(false)
    }
  }

  async function remove() {
    setBusy(true)
    try {
      await api(`/admin/media/${id}`, { method: 'DELETE', body: {} })
      onChanged(null, id)
      onClose()
    } catch (e) {
      setConfirming(false)
      setStatus({ kind: 'error', text: e.message })
      setBusy(false)
    }
  }

  async function copy() {
    try {
      await navigator.clipboard.writeText(window.location.origin + item.url)
      setStatus({ kind: 'ok', text: 'Link copied' })
    } catch {
      setStatus({ kind: 'error', text: 'Could not copy. The link is ' + item.url })
    }
  }

  const dirty = item && (draft.name !== item.name || draft.alt !== item.alt)

  return (
    <Modal title="Image details" onClose={onClose} wide>
      {!item ? (
        <p className="adm-muted">{status.text || 'Loading…'}</p>
      ) : (
        <>
          <div className="adm-media-preview"><img src={item.url} alt={item.alt} /></div>
          <p className="adm-muted adm-media-info">
            {item.width && item.height ? `${item.width} × ${item.height} px · ` : ''}
            {formatBytes(item.size_bytes)} · {item.mime_type.replace('image/', '').toUpperCase()} · Added {formatDateTime(item.created_at, overview?.timezone)}
          </p>
          {item.width && item.width < SMALL_WIDTH && (
            <p className="adm-note">This image is under {SMALL_WIDTH} px wide, so it may look soft on large screens. You can replace it with a sharper one.</p>
          )}

          <label className="adm-field">
            <span className="adm-label">Name</span>
            <input className="adm-input" value={draft.name} onChange={(e) => setDraft((d) => ({ ...d, name: e.target.value }))} maxLength={100} />
            <span className="adm-hint adm-field-help">Renaming also renames the file, which helps search engines.</span>
          </label>
          <label className="adm-field">
            <span className="adm-label">Alt text</span>
            <textarea className="adm-input" rows={2} value={draft.alt} onChange={(e) => setDraft((d) => ({ ...d, alt: e.target.value }))} maxLength={255} />
            <span className="adm-hint adm-field-help">Describe the picture for visitors who can't see it, and for search engines.</span>
          </label>

          <h3 className="adm-media-sub">Where it's used</h3>
          {item.usage.length === 0 ? (
            <p className="adm-muted">Not used anywhere yet. It's safe to delete.</p>
          ) : (
            <ul className="adm-usage">
              {item.usage.map((u) => (
                <li key={u.label + u.to}><Link to={u.to} onClick={onClose}>{u.label}</Link></li>
              ))}
            </ul>
          )}

          {status.text && <p className={status.kind === 'error' ? 'adm-error' : 'adm-status-ok'} role="status">{status.text}</p>}

          <div className="adm-actions adm-media-actions">
            <input ref={fileRef} type="file" accept="image/jpeg,image/png,image/webp,image/gif" hidden onChange={replace} />
            <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" onClick={() => fileRef.current?.click()} disabled={busy}>
              <FontAwesomeIcon icon={faRepeat} /> Replace image
            </button>
            <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" onClick={copy}>
              <FontAwesomeIcon icon={faCopy} /> Copy link
            </button>
            <button
              type="button"
              className="adm-btn adm-btn-outline adm-btn-danger adm-btn-sm"
              onClick={() => setConfirming(true)}
              disabled={busy || item.usage.length > 0}
              title={item.usage.length > 0 ? 'In use, so it cannot be deleted' : undefined}
            >
              <FontAwesomeIcon icon={faTrash} /> Delete
            </button>
            <span className="adm-grow" />
            <button type="button" className="adm-btn" onClick={save} disabled={!dirty || busy}>Save changes</button>
          </div>
        </>
      )}

      {confirming && (
        <ConfirmDialog
          title="Delete this image?"
          message="The image file will be permanently deleted. This can't be undone."
          confirmLabel="Delete"
          busy={busy}
          onConfirm={remove}
          onCancel={() => setConfirming(false)}
        />
      )}
    </Modal>
  )
}

export default function Media() {
  const [params, setParams] = useSearchParams()
  const filter = params.get('filter') || 'all'
  const page = Number(params.get('page')) || 1
  const query = params.get('q') || ''

  const [search, setSearch] = useState(query)
  const [data, setData] = useState(null)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [selected, setSelected] = useState(() => new Set())
  const [openId, setOpenId] = useState(null)
  const [uploading, setUploading] = useState('')
  const [confirmBulk, setConfirmBulk] = useState(false)
  const [busy, setBusy] = useState(false)
  const fileRef = useRef(null)

  const update = useCallback(
    (patch) => {
      const next = new URLSearchParams(params)
      Object.entries(patch).forEach(([k, v]) => (v ? next.set(k, v) : next.delete(k)))
      setParams(next, { replace: true })
    },
    [params, setParams],
  )

  useEffect(() => {
    if (search === query) return undefined
    const timer = setTimeout(() => update({ q: search.trim(), page: '' }), 350)
    return () => clearTimeout(timer)
  }, [search, query, update])

  const load = useCallback(() => {
    const qs = new URLSearchParams({ filter, page: String(page), q: query })
    return api(`/admin/media-library?${qs}`)
      .then((d) => {
        setData(d)
        setError('')
      })
      .catch((e) => setError(e.message))
  }, [filter, page, query])

  useEffect(() => {
    load()
  }, [load])

  async function upload(event) {
    const files = [...(event.target.files || [])]
    event.target.value = ''
    if (!files.length) return
    setMessage('')
    const failed = []
    for (let i = 0; i < files.length; i++) {
      setUploading(`Uploading ${i + 1} of ${files.length}…`)
      try {
        await uploadImage(files[i])
      } catch (e) {
        failed.push(`${files[i].name}: ${e.message}`)
      }
    }
    setUploading('')
    setMessage(
      failed.length
        ? `${files.length - failed.length} uploaded. ${failed.join(' ')}`
        : `${files.length} image${files.length === 1 ? '' : 's'} uploaded.`,
    )
    update({ filter: '', page: '', q: '' })
    setSearch('')
    load()
  }

  // Keep the grid in sync after the detail panel changes or deletes an image.
  function changed(media, deletedId) {
    setData((d) => {
      if (!d) return d
      if (deletedId) {
        return { ...d, items: d.items.filter((i) => i.id !== deletedId), stats: { ...d.stats, all: d.stats.all - 1 } }
      }
      return { ...d, items: d.items.map((i) => (i.id === media.id ? { ...i, ...media, uses: media.usage.length } : i)) }
    })
    if (deletedId) {
      setSelected((s) => {
        const next = new Set(s)
        next.delete(deletedId)
        return next
      })
      load()
    }
  }

  const toggle = (id) =>
    setSelected((s) => {
      const next = new Set(s)
      if (next.has(id)) next.delete(id)
      else next.add(id)
      return next
    })

  async function deleteSelected() {
    setBusy(true)
    try {
      const r = await api('/admin/media/delete', { method: 'POST', body: { ids: [...selected] } })
      setMessage(
        `Deleted ${r.deleted} image${r.deleted === 1 ? '' : 's'}.` +
          (r.skipped ? ` Skipped ${r.skipped} that ${r.skipped === 1 ? 'is' : 'are'} in use.` : ''),
      )
      setSelected(new Set())
      setConfirmBulk(false)
      load()
    } catch (e) {
      setError(e.message)
      setConfirmBulk(false)
    } finally {
      setBusy(false)
    }
  }

  const stats = data?.stats
  const pageIds = data?.items.map((i) => i.id) ?? []
  const allOnPage = pageIds.length > 0 && pageIds.every((id) => selected.has(id))

  return (
    <>
      <div className="adm-pagehead adm-pagehead-split">
        <div>
          <h1>Media</h1>
          {stats && <div className="adm-muted">{stats.all} images · {formatBytes(stats.bytes)} in total</div>}
        </div>
        <div>
          <input ref={fileRef} type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif" hidden onChange={upload} />
          <button type="button" className="adm-btn" onClick={() => fileRef.current?.click()} disabled={!!uploading}>
            <FontAwesomeIcon icon={faCloudArrowUp} /> {uploading || 'Upload images'}
          </button>
        </div>
      </div>

      <div className="adm-toolbar">
        <div className="adm-tabs" role="tablist" aria-label="Filter images">
          {tabs.map(([key, label]) => (
            <button
              key={key}
              type="button"
              role="tab"
              aria-selected={filter === key}
              className={`adm-tab${filter === key ? ' is-active' : ''}`}
              onClick={() => update({ filter: key === 'all' ? '' : key, page: '' })}
            >
              {label}
              {stats && <span>{stats[key]}</span>}
            </button>
          ))}
        </div>
        <div className="adm-search adm-search-wide">
          <FontAwesomeIcon icon={faMagnifyingGlass} />
          <input type="search" placeholder="Search by name or alt text" value={search} onChange={(e) => setSearch(e.target.value)} aria-label="Search images" />
        </div>
      </div>

      {message && <p className="adm-status-ok adm-media-message" role="status">{message}</p>}
      {error && <p className="adm-error" role="alert">{error}</p>}
      {!data && !error && <p className="adm-muted">Loading…</p>}

      {data && data.items.length === 0 && (
        <div className="adm-card">
          <p className="adm-muted">{query || filter !== 'all' ? 'No images match this filter.' : 'No images yet. Upload the first one.'}</p>
        </div>
      )}

      {data && data.items.length > 0 && (
        <>
          <label className="adm-selectall">
            <input type="checkbox" checked={allOnPage} onChange={() => setSelected(allOnPage ? new Set() : new Set(pageIds))} />
            Select all on this page
          </label>
          <div className="adm-mgrid">
            {data.items.map((m) => (
              <div className={`adm-mcard${selected.has(m.id) ? ' is-selected' : ''}`} key={m.id}>
                <input
                  type="checkbox"
                  className="adm-mcheck"
                  checked={selected.has(m.id)}
                  onChange={() => toggle(m.id)}
                  aria-label={`Select ${m.name}`}
                />
                <button type="button" className="adm-mopen" onClick={() => setOpenId(m.id)}>
                  <span className="adm-mthumb"><img src={m.url} alt={m.alt} loading="lazy" /></span>
                  <span className="adm-mname" title={m.name}>{m.name}</span>
                  <span className="adm-mmeta">
                    {m.width && m.height ? `${m.width}×${m.height} · ` : ''}{formatBytes(m.size_bytes)}
                  </span>
                  <span className="adm-mtags">
                    <span className={`adm-tag${m.uses ? '' : ' adm-tag-off'}`}>{m.uses ? `In use · ${m.uses}` : 'Unused'}</span>
                    {m.width && m.width < SMALL_WIDTH && <span className="adm-tag adm-tag-warn" title="Under 1000 px wide: may look soft on large screens">Small</span>}
                  </span>
                </button>
              </div>
            ))}
          </div>
        </>
      )}

      {data && data.pages > 1 && (
        <div className="adm-pager">
          <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" disabled={data.page <= 1} onClick={() => update({ page: String(data.page - 1) })}>Previous</button>
          <span className="adm-muted">Page {data.page} of {data.pages} · {data.total} images</span>
          <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" disabled={data.page >= data.pages} onClick={() => update({ page: String(data.page + 1) })}>Next</button>
        </div>
      )}

      {selected.size > 0 && (
        <div className="adm-bulkbar">
          <span>{selected.size} selected</span>
          <button type="button" className="adm-link-btn" onClick={() => setSelected(new Set())}>Clear</button>
          <button type="button" className="adm-btn adm-btn-sm adm-btn-danger-solid" onClick={() => setConfirmBulk(true)}>
            <FontAwesomeIcon icon={faTrash} /> Delete selected
          </button>
        </div>
      )}

      {confirmBulk && (
        <ConfirmDialog
          title={`Delete ${selected.size} image${selected.size === 1 ? '' : 's'}?`}
          message="The files will be permanently deleted. Images that are in use on a page are skipped and kept. This can't be undone."
          confirmLabel="Delete"
          busy={busy}
          onConfirm={deleteSelected}
          onCancel={() => setConfirmBulk(false)}
        />
      )}

      {openId && <ImageDetail id={openId} onClose={() => setOpenId(null)} onChanged={changed} />}
    </>
  )
}
