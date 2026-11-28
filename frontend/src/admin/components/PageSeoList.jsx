import { useEffect, useState } from 'react'
import { api } from '../api.js'

// One page's meta title and description, edited and saved on its own.
function PageSeoRow({ initial, onSaved }) {
  const [draft, setDraft] = useState(initial)
  const [status, setStatus] = useState({ kind: '', text: '' })
  const [busy, setBusy] = useState(false)
  const keys = ['seo_title', 'seo_description', 'seo_keywords_primary', 'seo_keywords_secondary']
  const dirty = keys.some((k) => draft[k] !== initial[k])

  async function save() {
    setBusy(true)
    setStatus({ kind: '', text: '' })
    try {
      const data = await api(`/admin/pages/${initial.slug}`, { method: 'PUT', body: draft })
      onSaved(data.page)
      setStatus({ kind: 'ok', text: 'Saved' })
    } catch (e) {
      setStatus({ kind: 'error', text: e.message })
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="adm-card">
      <h3 className="adm-card-title">{initial.menu_label} <small className="adm-muted">{initial.slug === 'home' ? '(home page)' : `/${initial.slug}`}</small></h3>
      <label className="adm-field">
        <span className="adm-label">Meta title <span className={`adm-count${draft.seo_title.length > 60 ? ' is-over' : ''}`}>{draft.seo_title.length} characters (about 60 is ideal)</span></span>
        <input className="adm-input" value={draft.seo_title} maxLength={255} onChange={(e) => setDraft({ ...draft, seo_title: e.target.value })} />
      </label>
      <label className="adm-field">
        <span className="adm-label">Meta description <span className={`adm-count${draft.seo_description.length > 160 ? ' is-over' : ''}`}>{draft.seo_description.length} characters (about 155 is ideal)</span></span>
        <textarea className="adm-input" rows={3} value={draft.seo_description} maxLength={500} onChange={(e) => setDraft({ ...draft, seo_description: e.target.value })} />
      </label>
      <div className="adm-grid-2">
        <label className="adm-field">
          <span className="adm-label">Primary keywords (optional)</span>
          <input className="adm-input" value={draft.seo_keywords_primary} maxLength={500} onChange={(e) => setDraft({ ...draft, seo_keywords_primary: e.target.value })} />
        </label>
        <label className="adm-field">
          <span className="adm-label">Secondary keywords (optional)</span>
          <input className="adm-input" value={draft.seo_keywords_secondary} maxLength={500} onChange={(e) => setDraft({ ...draft, seo_keywords_secondary: e.target.value })} />
        </label>
      </div>
      <div className="adm-actions adm-actions-end">
        {status.text && <span className={status.kind === 'error' ? 'adm-status-error' : 'adm-status-ok'} role="status">{status.text}</span>}
        <button type="button" className="adm-btn adm-btn-outline" onClick={() => setDraft(initial)} disabled={!dirty || busy}>Discard</button>
        <button type="button" className="adm-btn" onClick={save} disabled={!dirty || busy}>{busy ? 'Saving…' : 'Save'}</button>
      </div>
    </div>
  )
}

// Page-wise SEO: the meta title and description of every page in one place.
export default function PageSeoList() {
  const [pages, setPages] = useState(null)
  const [error, setError] = useState('')

  useEffect(() => {
    let cancelled = false
    api('/admin/pages')
      .then((list) => Promise.all(list.pages.map((p) => api(`/admin/pages/${p.slug}`))))
      .then((all) => !cancelled && setPages(all.map((d) => d.page)))
      .catch((e) => !cancelled && setError(e.message))
    return () => {
      cancelled = true
    }
  }, [])

  const replace = (page) => setPages((list) => list.map((p) => (p.id === page.id ? page : p)))

  return (
    <section className="adm-page-seo">
      <div className="adm-pagehead">
        <h2>Page-wise SEO</h2>
        <div className="adm-muted">Each page's own title and description for search engines. These replace the site-wide defaults above.</div>
      </div>
      {error && <p className="adm-error" role="alert">{error}</p>}
      {!pages && !error && <p className="adm-muted">Loading…</p>}
      {pages?.map((p) => <PageSeoRow key={`${p.id}-${p.seo_title}-${p.seo_description}-${p.seo_keywords_primary}-${p.seo_keywords_secondary}`} initial={p} onSaved={replace} />)}
    </section>
  )
}
