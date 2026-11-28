import { useState } from 'react'
import { api } from '../api.js'
import Toggle from './Toggle.jsx'

// Page-level settings: visibility, address, menu labels and SEO.
export default function PageSettings({ page, onSaved }) {
  const [draft, setDraft] = useState(page)
  const [status, setStatus] = useState({ kind: '', text: '' })
  const [busy, setBusy] = useState(false)
  const set = (patch) => setDraft((d) => ({ ...d, ...patch }))
  const dirty = JSON.stringify(draft) !== JSON.stringify(page)

  async function save() {
    setBusy(true)
    setStatus({ kind: '', text: '' })
    try {
      const data = await api(`/admin/pages/${page.slug}`, { method: 'PUT', body: draft })
      const hidNow = page.is_visible && !data.page.is_visible
      const note = hidNow && data.linked_buttons > 0
        ? `Saved. ${data.linked_buttons} button${data.linked_buttons === 1 ? '' : 's'} link to this page and will be hidden on the website.`
        : 'Saved'
      setStatus({ kind: 'ok', text: note })
      onSaved(data.page)
    } catch (e) {
      setStatus({ kind: 'error', text: e.message })
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="adm-card">
      <div className="adm-row adm-row-center">
        <Toggle
          checked={draft.is_visible}
          onChange={(v) => set({ is_visible: v })}
          label="Page visible on the website"
          disabled={page.is_locked}
        />
        <div className="adm-grow">
          <b>Page visible on the website</b>
          <div className="adm-hint">
            {page.is_locked
              ? 'The Home page cannot be hidden.'
              : draft.is_visible
                ? 'Shown in the menu and open to visitors.'
                : 'Hidden: removed from the menu. Buttons that point here are hidden too.'}
          </div>
        </div>
        <span className={`adm-tag${draft.is_visible ? '' : ' adm-tag-off'}`}>{draft.is_visible ? 'Visible' : 'Hidden'}</span>
      </div>

      <div className="adm-grid-2">
        <label className="adm-field">
          <span className="adm-label">Page address</span>
          <input className="adm-input" value={draft.slug} onChange={(e) => set({ slug: e.target.value })} disabled={page.is_locked} />
        </label>
        <label className="adm-field">
          <span className="adm-label">Menu label</span>
          <input className="adm-input" value={draft.menu_label} onChange={(e) => set({ menu_label: e.target.value })} maxLength={100} />
        </label>
        <label className="adm-field">
          <span className="adm-label">Footer label (if different)</span>
          <input className="adm-input" value={draft.footer_label ?? ''} onChange={(e) => set({ footer_label: e.target.value })} maxLength={100} />
        </label>
        <div className="adm-field">
          <span className="adm-label">Show in menus</span>
          <div className="adm-checks">
            <label><Toggle checked={draft.show_in_header} onChange={(v) => set({ show_in_header: v })} label="Show in header" /> Header</label>
            <label><Toggle checked={draft.show_in_footer} onChange={(v) => set({ show_in_footer: v })} label="Show in footer" /> Footer</label>
          </div>
        </div>
      </div>

      <div className="adm-seo">
        <h2 className="adm-card-title">SEO for this page (shown in Google and browser tabs)</h2>
        <label className="adm-field">
          <span className="adm-label">Meta title <span className={`adm-count${draft.seo_title.length > 60 ? ' is-over' : ''}`}>{draft.seo_title.length} characters (about 60 is ideal)</span></span>
          <input className="adm-input" value={draft.seo_title} onChange={(e) => set({ seo_title: e.target.value })} maxLength={255} />
        </label>
        <label className="adm-field">
          <span className="adm-label">Meta description <span className={`adm-count${draft.seo_description.length > 160 ? ' is-over' : ''}`}>{draft.seo_description.length} characters (about 155 is ideal)</span></span>
          <textarea className="adm-input" rows={3} value={draft.seo_description} onChange={(e) => set({ seo_description: e.target.value })} maxLength={500} />
        </label>
        <div className="adm-grid-2">
          <label className="adm-field">
            <span className="adm-label">Primary keywords (optional)</span>
            <input className="adm-input" value={draft.seo_keywords_primary} onChange={(e) => set({ seo_keywords_primary: e.target.value })} maxLength={500} />
          </label>
          <label className="adm-field">
            <span className="adm-label">Secondary keywords (optional)</span>
            <input className="adm-input" value={draft.seo_keywords_secondary} onChange={(e) => set({ seo_keywords_secondary: e.target.value })} maxLength={500} />
          </label>
        </div>
      </div>

      <div className="adm-actions adm-actions-end adm-savebar">
        {status.text && <span className={status.kind === 'error' ? 'adm-status-error' : 'adm-status-ok'} role="status">{status.text}</span>}
        <button type="button" className="adm-btn adm-btn-outline" onClick={() => setDraft(page)} disabled={!dirty || busy}>Discard</button>
        <button type="button" className="adm-btn" onClick={save} disabled={!dirty || busy}>{busy ? 'Saving…' : 'Save page settings'}</button>
      </div>
    </div>
  )
}
