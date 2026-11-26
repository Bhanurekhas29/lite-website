import { faArrowDown, faArrowUp, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { icons } from '../../shared/icons.js'
import { api } from '../api.js'
import ConfirmDialog from '../components/ConfirmDialog.jsx'
import IconPicker from '../components/IconPicker.jsx'
import Toggle from '../components/Toggle.jsx'

// One editable category row: icon, name, save and delete.
function CategoryRow({ category, onChanged, onError }) {
  const [draft, setDraft] = useState({ name: category.name, icon: category.icon })
  const [busy, setBusy] = useState(false)
  const [confirming, setConfirming] = useState(false)
  const dirty = draft.name !== category.name || draft.icon !== category.icon

  async function save() {
    setBusy(true)
    try {
      const d = await api(`/admin/project-categories/${category.id}`, { method: 'PUT', body: draft })
      onChanged(d.categories)
      onError('')
    } catch (e) {
      onError(e.message)
    } finally {
      setBusy(false)
    }
  }

  async function remove() {
    setBusy(true)
    try {
      const d = await api(`/admin/project-categories/${category.id}`, { method: 'DELETE', body: {} })
      onChanged(d.categories)
      onError('')
    } catch (e) {
      onError(e.message)
      setConfirming(false)
      setBusy(false)
    }
  }

  return (
    <div className="adm-cat-row">
      <IconPicker value={draft.icon} onChange={(icon) => setDraft((d) => ({ ...d, icon }))} />
      <input
        className="adm-input"
        value={draft.name}
        onChange={(e) => setDraft((d) => ({ ...d, name: e.target.value }))}
        maxLength={100}
        aria-label="Category name"
      />
      <span className="adm-muted adm-cat-count">{category.projects} project{category.projects === 1 ? '' : 's'}</span>
      <button type="button" className="adm-btn adm-btn-sm" onClick={save} disabled={!dirty || busy}>Save</button>
      <button type="button" className="adm-icon-btn adm-danger" onClick={() => setConfirming(true)} disabled={busy} aria-label={`Delete ${category.name}`}>
        <FontAwesomeIcon icon={faTrash} />
      </button>
      {confirming && (
        <ConfirmDialog
          title="Delete this category?"
          message={`"${category.name}" will be deleted. Categories that projects still use can't be deleted.`}
          confirmLabel="Delete"
          busy={busy}
          onConfirm={remove}
          onCancel={() => setConfirming(false)}
        />
      )}
    </div>
  )
}

function AddCategory({ onChanged, onError }) {
  const [draft, setDraft] = useState({ name: '', icon: null })
  const [busy, setBusy] = useState(false)

  async function add() {
    setBusy(true)
    try {
      const d = await api('/admin/project-categories', { method: 'POST', body: draft })
      onChanged(d.categories)
      onError('')
      setDraft({ name: '', icon: null })
    } catch (e) {
      onError(e.message)
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="adm-cat-row adm-cat-add">
      <IconPicker value={draft.icon} onChange={(icon) => setDraft((d) => ({ ...d, icon }))} />
      <input
        className="adm-input"
        placeholder="New category name"
        value={draft.name}
        onChange={(e) => setDraft((d) => ({ ...d, name: e.target.value }))}
        maxLength={100}
        aria-label="New category name"
      />
      <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" onClick={add} disabled={!draft.name.trim() || busy}>
        <FontAwesomeIcon icon={faPlus} /> Add category
      </button>
    </div>
  )
}

export default function Projects() {
  const [data, setData] = useState(null)
  const [error, setError] = useState('')
  const [catError, setCatError] = useState('')

  useEffect(() => {
    api('/admin/projects')
      .then(setData)
      .catch((e) => setError(e.message))
  }, [])

  if (error) return <div className="adm-card"><p className="adm-error">{error}</p></div>
  if (!data) return <p className="adm-muted">Loading…</p>

  const { projects, categories } = data
  const patch = (id, changes) =>
    setData((d) => ({ ...d, projects: d.projects.map((p) => (p.id === id ? { ...p, ...changes } : p)) }))

  async function setFlag(project, flag, value) {
    setError('')
    patch(project.id, { [flag]: value })
    try {
      await api(`/admin/projects/${project.id}/flags`, { method: 'PUT', body: { [flag]: value } })
    } catch (e) {
      patch(project.id, { [flag]: !value })
      setError(e.message)
    }
  }

  async function move(index, step) {
    const next = [...projects]
    const target = index + step
    if (target < 0 || target >= next.length) return
    next.splice(target, 0, next.splice(index, 1)[0])
    setError('')
    setData((d) => ({ ...d, projects: next }))
    try {
      await api('/admin/projects/order', { method: 'PUT', body: { ids: next.map((p) => p.id) } })
    } catch (e) {
      setError(e.message)
      api('/admin/projects').then(setData)
    }
  }

  return (
    <>
      <div className="adm-pagehead adm-pagehead-split">
        <div>
          <h1>Projects</h1>
          <div className="adm-muted">Shown on the Our Work page. Projects marked "Show on Home" also appear on Home.</div>
        </div>
        <Link to="/admin/projects/new" className="adm-btn">
          <FontAwesomeIcon icon={faPlus} /> Add project
        </Link>
      </div>

      {error && <p className="adm-error" role="alert">{error}</p>}

      {projects.length === 0 ? (
        <div className="adm-card">
          <p className="adm-muted">No projects yet. Add the first one to show it on the Our Work page.</p>
        </div>
      ) : (
        <div className="adm-projects">
          {projects.map((p, i) => (
            <div className={`adm-project${p.is_visible ? '' : ' is-hidden'}`} key={p.id}>
              <div className="adm-move">
                <button type="button" className="adm-icon-btn" onClick={() => move(i, -1)} disabled={i === 0} aria-label="Move up">
                  <FontAwesomeIcon icon={faArrowUp} />
                </button>
                <button type="button" className="adm-icon-btn" onClick={() => move(i, 1)} disabled={i === projects.length - 1} aria-label="Move down">
                  <FontAwesomeIcon icon={faArrowDown} />
                </button>
              </div>
              <Link to={`/admin/projects/${p.id}`} className="adm-project-main">
                <span className="adm-project-thumb">
                  {p.image ? <img src={p.image.url} alt="" loading="lazy" /> : <span className="adm-muted">No photo</span>}
                </span>
                <span className="adm-project-text">
                  <b>{p.title}</b>
                  <small>
                    <span className="adm-tag">
                      {p.category_icon && icons[p.category_icon] && <FontAwesomeIcon icon={icons[p.category_icon]} />} {p.category_name}
                    </span>
                    {[p.event_year, p.location].filter(Boolean).join(' · ')}
                  </small>
                </span>
              </Link>
              <div className="adm-project-flags">
                <label>
                  <Toggle checked={p.show_on_home} onChange={(v) => setFlag(p, 'show_on_home', v)} label={`Show ${p.title} on Home`} />
                  <span>Home</span>
                </label>
                <label>
                  <Toggle checked={p.is_visible} onChange={(v) => setFlag(p, 'is_visible', v)} label={`Show ${p.title} on the website`} />
                  <span>Visible</span>
                </label>
              </div>
            </div>
          ))}
        </div>
      )}

      <h2 className="adm-subhead">Categories</h2>
      <p className="adm-hint">Each project belongs to one category. The icon shows on the project card.</p>
      {catError && <p className="adm-error" role="alert">{catError}</p>}
      <div className="adm-card">
        {categories.map((c) => (
          <CategoryRow
            key={`${c.id}-${c.name}-${c.icon}`}
            category={c}
            onChanged={(cats) => setData((d) => ({ ...d, categories: cats }))}
            onError={setCatError}
          />
        ))}
        <AddCategory onChanged={(cats) => setData((d) => ({ ...d, categories: cats }))} onError={setCatError} />
      </div>
    </>
  )
}
