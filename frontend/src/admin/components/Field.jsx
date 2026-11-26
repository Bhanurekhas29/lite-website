import { faArrowDown, faArrowUp, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useRef, useState } from 'react'
import { useLayoutData } from '../layoutData.js'
import IconPicker from './IconPicker.jsx'
import MediaPicker from './MediaPicker.jsx'
import Toggle from './Toggle.jsx'

// The starting value for a field, matching what the API returns for empty fields.
export function defaultValue(spec) {
  switch (spec.type) {
    case 'image':
    case 'icon':
      return null
    case 'button':
    case 'link':
    case 'action':
      return { label: '', icon: null, link_type: 'none', link_page_id: null, link_url: '' }
    case 'toggle':
      return spec.default ?? '0'
    case 'select':
      return spec.default ?? Object.keys(spec.options)[0]
    default:
      return ''
  }
}

function ImageField({ value, onChange }) {
  const [open, setOpen] = useState(false)
  return (
    <div className="adm-image-field">
      <div className="adm-thumb">
        {value ? <img src={value.url} alt="" /> : <span className="adm-muted">No image</span>}
      </div>
      <div className="adm-image-actions">
        <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" onClick={() => setOpen(true)}>
          {value ? 'Change image' : 'Choose image'}
        </button>
        {value && (
          <button type="button" className="adm-link-btn" onClick={() => onChange(null)}>
            Remove
          </button>
        )}
      </div>
      {open && (
        <MediaPicker
          onClose={() => setOpen(false)}
          onSelect={(m) => {
            onChange({ id: m.id, url: m.url, alt: m.alt })
            setOpen(false)
          }}
        />
      )}
    </div>
  )
}

function ButtonField({ value, onChange, linkOnly, actionOnly }) {
  const { pages } = useLayoutData()
  const set = (patch) => onChange({ ...value, ...patch })
  return (
    <div className="adm-button-field">
      <div className="adm-row">
        <input
          className="adm-input"
          placeholder={linkOnly ? 'Link text' : 'Button text'}
          value={value.label}
          onChange={(e) => set({ label: e.target.value })}
          maxLength={120}
        />
        {!linkOnly && <IconPicker value={value.icon} onChange={(icon) => set({ icon })} />}
      </div>
      {!actionOnly && <div className="adm-row">
        <select
          className="adm-input"
          value={value.link_type}
          onChange={(e) => set({ link_type: e.target.value })}
          aria-label="Link type"
        >
          <option value="none">No link</option>
          <option value="page">Page on this website</option>
          <option value="url">External link</option>
        </select>
        {value.link_type === 'page' && (
          <select
            className="adm-input"
            value={value.link_page_id ?? ''}
            onChange={(e) => set({ link_page_id: e.target.value ? Number(e.target.value) : null })}
            aria-label="Page"
          >
            <option value="">Choose a page</option>
            {pages.map((p) => (
              <option key={p.id} value={p.id}>{p.menu_label}</option>
            ))}
          </select>
        )}
        {value.link_type === 'url' && (
          <input
            className="adm-input"
            placeholder="https://, mailto:, tel: or #"
            value={value.link_url}
            onChange={(e) => set({ link_url: e.target.value })}
            aria-label="Link address"
          />
        )}
      </div>}
    </div>
  )
}

// A password-style field for saved secrets. The saved value is never shown.
// value: { set: boolean, editing: boolean, input: string, clear: boolean }
function SecretField({ value, onChange }) {
  if (value.clear) {
    return (
      <div className="adm-secret">
        <span className="adm-muted">The saved password will be removed when you save.</span>
        <button type="button" className="adm-link-btn" onClick={() => onChange({ ...value, clear: false })}>Undo</button>
      </div>
    )
  }
  if (value.set && !value.editing) {
    return (
      <div className="adm-secret">
        <span className="adm-secret-dots" aria-label="A password is saved">••••••••</span>
        <span className="adm-muted">Saved</span>
        <button type="button" className="adm-link-btn" onClick={() => onChange({ ...value, editing: true })}>Change</button>
        <button type="button" className="adm-link-btn" onClick={() => onChange({ ...value, clear: true })}>Remove</button>
      </div>
    )
  }
  return (
    <div className="adm-secret">
      <input
        className="adm-input"
        type="password"
        autoComplete="new-password"
        placeholder={value.set ? 'Enter the new password' : 'Enter the password'}
        value={value.input}
        onChange={(e) => onChange({ ...value, input: e.target.value })}
        autoFocus={value.editing}
      />
      {value.set && (
        <button type="button" className="adm-link-btn" onClick={() => onChange({ ...value, editing: false, input: '' })}>Cancel</button>
      )}
    </div>
  )
}

function PageSelect({ value, onChange }) {
  const { pages } = useLayoutData()
  return (
    <select className="adm-input" value={value ?? ''} onChange={(e) => onChange(e.target.value ? Number(e.target.value) : null)}>
      <option value="">No page</option>
      {pages.map((p) => (
        <option key={p.id} value={p.id}>{p.menu_label}</option>
      ))}
    </select>
  )
}

// A list of short choices: add, rename, reorder or remove them. Stored as one choice per line.
function ChoicesField({ value, onChange }) {
  const toRows = (text) => String(text || '').split('\n').filter((s) => s !== '').map((text, i) => ({ id: `c${i}-${Math.random().toString(36).slice(2, 7)}`, text }))
  const [rows, setRows] = useState(() => toRows(value))
  const last = useRef(String(value || ''))
  const inputs = useRef({})
  const [focusId, setFocusId] = useState(null)

  // Pick up a change that came from outside (for example after saving).
  useEffect(() => {
    if (String(value || '') !== last.current) {
      last.current = String(value || '')
      setRows(toRows(value))
    }
  }, [value])

  useEffect(() => {
    if (focusId && inputs.current[focusId]) {
      inputs.current[focusId].focus()
      setFocusId(null)
    }
  }, [focusId, rows])

  const commit = (next) => {
    setRows(next)
    const text = next.map((r) => r.text).join('\n')
    last.current = text
    onChange(text)
  }
  const edit = (id, text) => commit(rows.map((r) => (r.id === id ? { ...r, text } : r)))
  const remove = (id) => commit(rows.filter((r) => r.id !== id))
  const move = (index, step) => {
    const to = index + step
    if (to < 0 || to >= rows.length) return
    const next = [...rows]
    ;[next[index], next[to]] = [next[to], next[index]]
    commit(next)
  }
  const add = (after) => {
    const row = { id: `n${Date.now()}${Math.random().toString(36).slice(2, 5)}`, text: '' }
    const next = [...rows]
    next.splice(after === undefined ? next.length : after + 1, 0, row)
    setRows(next)
    setFocusId(row.id)
  }

  return (
    <div className="adm-choices">
      {rows.length === 0 && <p className="adm-muted adm-choices-empty">No choices yet.</p>}
      {rows.map((r, i) => (
        <div className="adm-choice" key={r.id}>
          <input
            ref={(el) => { inputs.current[r.id] = el }}
            className="adm-input"
            value={r.text}
            maxLength={150}
            placeholder="Type a choice"
            onChange={(e) => edit(r.id, e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                e.preventDefault()
                add(i)
              }
            }}
          />
          <button type="button" className="adm-icon-btn" onClick={() => move(i, -1)} disabled={i === 0} aria-label="Move up"><FontAwesomeIcon icon={faArrowUp} /></button>
          <button type="button" className="adm-icon-btn" onClick={() => move(i, 1)} disabled={i === rows.length - 1} aria-label="Move down"><FontAwesomeIcon icon={faArrowDown} /></button>
          <button type="button" className="adm-icon-btn adm-icon-danger" onClick={() => remove(r.id)} aria-label={`Remove ${r.text || 'this choice'}`}><FontAwesomeIcon icon={faTrash} /></button>
        </div>
      ))}
      <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" onClick={() => add()}><FontAwesomeIcon icon={faPlus} /> Add a choice</button>
    </div>
  )
}

// One editable field, drawn from its definition in the backend schema.
export default function Field({ spec, value, onChange }) {
  if (spec.type === 'toggle') {
    return (
      <div className="adm-field adm-field-inline">
        <Toggle checked={value === '1'} onChange={(on) => onChange(on ? '1' : '0')} label={spec.label} />
        <span>{spec.label}</span>
      </div>
    )
  }
  if (value === undefined) return null

  let control
  switch (spec.type) {
    case 'textarea':
      control = <textarea className="adm-input" rows={3} value={value} onChange={(e) => onChange(e.target.value)} maxLength={5000} />
      break
    case 'choices':
      control = <ChoicesField value={value} onChange={onChange} />
      break
    case 'select':
      control = (
        <select className="adm-input" value={value} onChange={(e) => onChange(e.target.value)}>
          {Object.entries(spec.options).map(([k, label]) => (
            <option key={k} value={k}>{label}</option>
          ))}
        </select>
      )
      break
    case 'image':
      control = <ImageField value={value} onChange={onChange} />
      break
    case 'icon':
      control = <IconPicker value={value} onChange={onChange} />
      break
    case 'button':
      control = <ButtonField value={value} onChange={onChange} />
      break
    case 'link':
      control = <ButtonField value={value} onChange={onChange} linkOnly />
      break
    case 'action':
      control = <ButtonField value={value} onChange={onChange} actionOnly />
      break
    case 'page':
      control = <PageSelect value={value} onChange={onChange} />
      break
    case 'secret':
      control = <SecretField value={value} onChange={onChange} />
      break
    default:
      control = (
        <input
          className="adm-input"
          type={spec.type === 'email' ? 'email' : 'text'}
          inputMode={spec.type === 'int' ? 'numeric' : spec.type === 'email' ? 'email' : undefined}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          maxLength={500}
        />
      )
  }

  return (
    <div className="adm-field">
      <span className="adm-label">{spec.label}</span>
      {control}
      {spec.help && <span className="adm-hint adm-field-help">{spec.help}</span>}
    </div>
  )
}
