import { faArrowDown, faArrowUp, faChevronDown, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useMemo, useState } from 'react'
import { api } from '../api.js'
import Field, { defaultValue } from './Field.jsx'
import Toggle from './Toggle.jsx'

let keySeed = 0
const newKey = () => `new-${++keySeed}`

// Turn a section from the API into editable form state.
function toDraft(section) {
  return {
    fields: { ...section.fields },
    items: (section.items || []).map((i) => ({ key: `item-${i.id}`, id: i.id, is_visible: i.is_visible, fields: { ...i.fields } })),
  }
}

export default function SectionCard({ section, type, index, count, onSaved, onVisibility, onMove }) {
  const [open, setOpen] = useState(false)
  const [draft, setDraft] = useState(() => toDraft(section))
  const [status, setStatus] = useState({ kind: '', text: '' })
  const [busy, setBusy] = useState(false)

  const base = useMemo(() => JSON.stringify(toDraft(section), (k, v) => (k === 'key' ? undefined : v)), [section])
  const dirty = JSON.stringify(draft, (k, v) => (k === 'key' ? undefined : v)) !== base

  const setField = (key, value) => setDraft((d) => ({ ...d, fields: { ...d.fields, [key]: value } }))
  const setItemField = (itemKey, key, value) =>
    setDraft((d) => ({
      ...d,
      items: d.items.map((i) => (i.key === itemKey ? { ...i, fields: { ...i.fields, [key]: value } } : i)),
    }))
  const patchItem = (itemKey, patch) =>
    setDraft((d) => ({ ...d, items: d.items.map((i) => (i.key === itemKey ? { ...i, ...patch } : i)) }))
  const moveItem = (from, to) =>
    setDraft((d) => {
      const items = [...d.items]
      items.splice(to, 0, items.splice(from, 1)[0])
      return { ...d, items }
    })
  const removeItem = (itemKey) => setDraft((d) => ({ ...d, items: d.items.filter((i) => i.key !== itemKey) }))
  const addItem = () =>
    setDraft((d) => ({
      ...d,
      items: [
        ...d.items,
        {
          key: newKey(),
          id: null,
          is_visible: true,
          fields: Object.fromEntries(type.items.fields.map((f) => [f.key, defaultValue(f)])),
        },
      ],
    }))

  async function save() {
    setBusy(true)
    setStatus({ kind: '', text: '' })
    try {
      const body = {
        fields: draft.fields,
        items: draft.items.map((i) => ({ id: i.id, is_visible: i.is_visible, fields: i.fields })),
      }
      const data = await api(`/admin/sections/${section.id}`, { method: 'PUT', body })
      setDraft(toDraft(data.section))
      onSaved(data.section)
      setStatus({ kind: 'ok', text: 'Saved' })
    } catch (e) {
      setStatus({ kind: 'error', text: e.message })
    } finally {
      setBusy(false)
    }
  }

  function discard() {
    setDraft(toDraft(section))
    setStatus({ kind: '', text: '' })
  }

  const itemSpec = type.items

  return (
    <section className={`adm-section${open ? ' is-open' : ''}${section.is_visible ? '' : ' is-hidden'}`}>
      <div className="adm-section-head">
        <div className="adm-move">
          <button type="button" className="adm-icon-btn" onClick={() => onMove(-1)} disabled={index === 0} aria-label="Move section up">
            <FontAwesomeIcon icon={faArrowUp} />
          </button>
          <button type="button" className="adm-icon-btn" onClick={() => onMove(1)} disabled={index === count - 1} aria-label="Move section down">
            <FontAwesomeIcon icon={faArrowDown} />
          </button>
        </div>
        <button type="button" className="adm-section-title" onClick={() => setOpen((v) => !v)} aria-expanded={open}>
          <b>{section.label}</b>
          <span>
            {type.label} · {section.is_visible ? 'Visible' : 'Hidden'}
            {dirty && ' · Unsaved changes'}
          </span>
        </button>
        <Toggle checked={section.is_visible} onChange={onVisibility} label={`Show ${section.label} on the website`} />
        <button type="button" className="adm-icon-btn adm-chevron" onClick={() => setOpen((v) => !v)} aria-label={open ? 'Collapse' : 'Expand'}>
          <FontAwesomeIcon icon={faChevronDown} />
        </button>
      </div>

      {open && (
        <div className="adm-section-body">
          {type.note && <p className="adm-note">{type.note}</p>}

          {type.fields.map((spec) => (
            <Field key={spec.key} spec={spec} value={draft.fields[spec.key]} onChange={(v) => setField(spec.key, v)} />
          ))}

          {itemSpec && (
            <div className="adm-items">
              <div className="adm-items-head">
                <b>{itemSpec.label}s</b>
                <span className="adm-muted">{draft.items.length} of {itemSpec.max} used</span>
              </div>
              {draft.items.map((item, i) => (
                <div className={`adm-item${item.is_visible ? '' : ' is-hidden'}`} key={item.key}>
                  <div className="adm-item-head">
                    <b>{itemSpec.label} {i + 1}</b>
                    <div className="adm-item-tools">
                      <Toggle checked={item.is_visible} onChange={(v) => patchItem(item.key, { is_visible: v })} label={`Show ${itemSpec.label} ${i + 1}`} />
                      <button type="button" className="adm-icon-btn" onClick={() => moveItem(i, i - 1)} disabled={i === 0} aria-label="Move up">
                        <FontAwesomeIcon icon={faArrowUp} />
                      </button>
                      <button type="button" className="adm-icon-btn" onClick={() => moveItem(i, i + 1)} disabled={i === draft.items.length - 1} aria-label="Move down">
                        <FontAwesomeIcon icon={faArrowDown} />
                      </button>
                      <button type="button" className="adm-icon-btn adm-danger" onClick={() => removeItem(item.key)} aria-label={`Remove ${itemSpec.label} ${i + 1}`}>
                        <FontAwesomeIcon icon={faTrash} />
                      </button>
                    </div>
                  </div>
                  {itemSpec.fields.map((spec) => (
                    <Field key={spec.key} spec={spec} value={item.fields[spec.key]} onChange={(v) => setItemField(item.key, spec.key, v)} />
                  ))}
                </div>
              ))}
              <button
                type="button"
                className="adm-btn adm-btn-outline adm-btn-sm"
                onClick={addItem}
                disabled={draft.items.length >= itemSpec.max}
              >
                <FontAwesomeIcon icon={faPlus} /> Add {itemSpec.label.toLowerCase()}
              </button>
            </div>
          )}

          <div className="adm-actions adm-actions-end adm-savebar">
            {status.text && <span className={status.kind === 'error' ? 'adm-status-error' : 'adm-status-ok'} role="status">{status.text}</span>}
            <button type="button" className="adm-btn adm-btn-outline" onClick={discard} disabled={!dirty || busy}>
              Discard
            </button>
            <button type="button" className="adm-btn" onClick={save} disabled={!dirty || busy}>
              {busy ? 'Saving…' : 'Save section'}
            </button>
          </div>
        </div>
      )}
    </section>
  )
}
