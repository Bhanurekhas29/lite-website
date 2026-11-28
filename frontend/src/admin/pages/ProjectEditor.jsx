import { faArrowDown, faArrowLeft, faArrowUp, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { api } from '../api.js'
import ConfirmDialog from '../components/ConfirmDialog.jsx'
import Field from '../components/Field.jsx'
import Toggle from '../components/Toggle.jsx'

const MAX_SERVICES = 12
const photoSpec = { key: 'image', label: 'Photo', type: 'image' }
const hoverSpec = { key: 'hover_image', label: 'Hover photo (optional)', type: 'image', noAlt: true, help: 'Shown on the Home page card when a visitor hovers over it. Leave empty for no change.' }

// Turn the project from the API into editable form state (each service gets a stable key).
let keySeed = 0
const newKey = () => `s${++keySeed}`
function toForm(p) {
  return {
    title: p.title,
    category_id: p.category_id,
    image: p.image ? { ...p.image, alt_custom: p.image_alt ?? '' } : null,
    hover_image: p.hover_image,
    event_year: p.event_year,
    location: p.location,
    our_role: p.our_role,
    summary: p.summary ?? '',
    show_on_home: p.show_on_home,
    is_visible: p.is_visible,
    services: p.services.map((text) => ({ key: newKey(), text })),
  }
}

export default function ProjectEditor() {
  const { id } = useParams()
  const isNew = id === undefined
  const navigate = useNavigate()
  const [categories, setCategories] = useState(null)
  const [form, setForm] = useState(null)
  const [base, setBase] = useState('')
  const [error, setError] = useState('')
  const [status, setStatus] = useState('')
  const [busy, setBusy] = useState(false)
  const [confirming, setConfirming] = useState(false)

  useEffect(() => {
    let cancelled = false
    Promise.all([api('/admin/projects'), isNew ? null : api(`/admin/projects/${id}`)])
      .then(([list, one]) => {
        if (cancelled) return
        setCategories(list.categories)
        const initial = isNew
          ? toForm({
              title: '', category_id: list.categories[0]?.id ?? '', image: null, hover_image: null, event_year: '', location: '',
              our_role: '', summary: '', show_on_home: false, is_visible: true, services: [''],
            })
          : toForm(one.project)
        setForm(initial)
        setBase(JSON.stringify(initial, (k, v) => (k === 'key' ? undefined : v)))
      })
      .catch((e) => !cancelled && setError(e.message))
    return () => {
      cancelled = true
    }
  }, [id, isNew])

  const back = (
    <Link to="/admin/projects" className="adm-back">
      <FontAwesomeIcon icon={faArrowLeft} /> All projects
    </Link>
  )

  if (error && !form) {
    return (
      <>
        {back}
        <div className="adm-card"><p className="adm-error">{error}</p></div>
      </>
    )
  }
  if (!form || !categories) return <p className="adm-muted">Loading…</p>

  const dirty = JSON.stringify(form, (k, v) => (k === 'key' ? undefined : v)) !== base
  const set = (patch) => {
    setForm((f) => ({ ...f, ...patch }))
    setStatus('')
  }
  const setService = (key, text) => set({ services: form.services.map((s) => (s.key === key ? { ...s, text } : s)) })
  const moveService = (from, to) => {
    const services = [...form.services]
    services.splice(to, 0, services.splice(from, 1)[0])
    set({ services })
  }

  async function save() {
    setBusy(true)
    setError('')
    setStatus('')
    try {
      const body = { ...form, image_alt: form.image?.alt_custom ?? '', services: form.services.map((s) => s.text) }
      const data = await api(isNew ? '/admin/projects' : `/admin/projects/${id}`, { method: isNew ? 'POST' : 'PUT', body })
      if (isNew) {
        navigate('/admin/projects', { replace: true })
        return
      }
      const fresh = toForm(data.project)
      setForm(fresh)
      setBase(JSON.stringify(fresh, (k, v) => (k === 'key' ? undefined : v)))
      setStatus('Saved')
    } catch (e) {
      setError(e.message)
    } finally {
      setBusy(false)
    }
  }

  async function remove() {
    setBusy(true)
    try {
      await api(`/admin/projects/${id}`, { method: 'DELETE', body: {} })
      navigate('/admin/projects', { replace: true })
    } catch (e) {
      setConfirming(false)
      setError(e.message)
      setBusy(false)
    }
  }

  return (
    <>
      {back}
      <div className="adm-pagehead">
        <h1>{isNew ? 'Add project' : form.title || 'Edit project'}</h1>
      </div>

      {categories.length === 0 && (
        <p className="adm-error">Add a category first. You can do this at the bottom of the <Link to="/admin/projects">Projects</Link> screen.</p>
      )}
      {error && <p className="adm-error" role="alert">{error}</p>}

      <div className="adm-card">
        <label className="adm-field">
          <span className="adm-label">Title</span>
          <input className="adm-input" value={form.title} onChange={(e) => set({ title: e.target.value })} maxLength={200} />
        </label>

        <div className="adm-grid-2">
          <label className="adm-field">
            <span className="adm-label">Category</span>
            <select className="adm-input" value={form.category_id} onChange={(e) => set({ category_id: Number(e.target.value) })}>
              {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
            </select>
          </label>
          <label className="adm-field">
            <span className="adm-label">Year or date</span>
            <input className="adm-input" value={form.event_year} onChange={(e) => set({ event_year: e.target.value })} maxLength={20} placeholder="2025" />
          </label>
          <label className="adm-field">
            <span className="adm-label">Location</span>
            <input className="adm-input" value={form.location} onChange={(e) => set({ location: e.target.value })} maxLength={150} placeholder="London, UK" />
          </label>
          <label className="adm-field">
            <span className="adm-label">Our role</span>
            <input className="adm-input" value={form.our_role} onChange={(e) => set({ our_role: e.target.value })} maxLength={255} placeholder="Event coordination and supplier liaison" />
          </label>
        </div>
        <label className="adm-field">
          <span className="adm-label">Short description (shown on the card)</span>
          <textarea className="adm-input" rows={3} value={form.summary} onChange={(e) => set({ summary: e.target.value })} maxLength={500} placeholder="One or two sentences about the event and your part in it" />
        </label>

        <Field spec={photoSpec} value={form.image} onChange={(image) => set({ image })} />
        <Field spec={hoverSpec} value={form.hover_image} onChange={(hover_image) => set({ hover_image })} />
      </div>

      <div className="adm-card">
        <h2 className="adm-card-title">Services provided</h2>
        <p className="adm-hint">Shown as a tick list on Our Work, and as a line on Home.</p>
        {form.services.map((s, i) => (
          <div className="adm-service-row" key={s.key}>
            <input
              className="adm-input"
              value={s.text}
              onChange={(e) => setService(s.key, e.target.value)}
              maxLength={200}
              placeholder={`Service ${i + 1}`}
              aria-label={`Service ${i + 1}`}
            />
            <button type="button" className="adm-icon-btn" onClick={() => moveService(i, i - 1)} disabled={i === 0} aria-label="Move up">
              <FontAwesomeIcon icon={faArrowUp} />
            </button>
            <button type="button" className="adm-icon-btn" onClick={() => moveService(i, i + 1)} disabled={i === form.services.length - 1} aria-label="Move down">
              <FontAwesomeIcon icon={faArrowDown} />
            </button>
            <button type="button" className="adm-icon-btn adm-danger" onClick={() => set({ services: form.services.filter((x) => x.key !== s.key) })} aria-label={`Remove service ${i + 1}`}>
              <FontAwesomeIcon icon={faTrash} />
            </button>
          </div>
        ))}
        <button
          type="button"
          className="adm-btn adm-btn-outline adm-btn-sm"
          onClick={() => set({ services: [...form.services, { key: newKey(), text: '' }] })}
          disabled={form.services.length >= MAX_SERVICES}
        >
          <FontAwesomeIcon icon={faPlus} /> Add service
        </button>
      </div>

      <div className="adm-card">
        <div className="adm-row adm-row-center">
          <Toggle checked={form.is_visible} onChange={(v) => set({ is_visible: v })} label="Visible on the website" />
          <div className="adm-grow">
            <b>Visible on the website</b>
            <div className="adm-hint">Hidden projects stay here but don't appear on any page.</div>
          </div>
        </div>
        <div className="adm-row adm-row-center">
          <Toggle checked={form.show_on_home} onChange={(v) => set({ show_on_home: v })} label="Show on Home" />
          <div className="adm-grow">
            <b>Show on Home</b>
            <div className="adm-hint">Adds this project to the Our Work cards on the Home page.</div>
          </div>
        </div>
      </div>

      <div className="adm-actions adm-actions-end adm-savebar adm-sticky-save">
        {status && <span className="adm-status-ok" role="status">{status}</span>}
        {!isNew && (
          <button type="button" className="adm-btn adm-btn-outline adm-btn-danger adm-push-left" onClick={() => setConfirming(true)} disabled={busy}>
            <FontAwesomeIcon icon={faTrash} /> Delete
          </button>
        )}
        <Link to="/admin/projects" className="adm-btn adm-btn-outline">{dirty ? 'Cancel' : 'Close'}</Link>
        <button type="button" className="adm-btn" onClick={save} disabled={busy || (!isNew && !dirty) || categories.length === 0}>
          {busy ? 'Saving…' : isNew ? 'Add project' : 'Save changes'}
        </button>
      </div>

      {confirming && (
        <ConfirmDialog
          title="Delete this project?"
          message={`"${form.title}" will be permanently deleted from Our Work and Home. This can't be undone.`}
          confirmLabel="Delete"
          busy={busy}
          onConfirm={remove}
          onCancel={() => setConfirming(false)}
        />
      )}
    </>
  )
}
