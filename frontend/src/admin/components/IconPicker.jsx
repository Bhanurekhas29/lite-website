import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useState } from 'react'
import { iconLabel, iconNames, icons } from '../../shared/icons.js'
import Modal from './Modal.jsx'

// Shows the chosen icon and opens a searchable grid to change it.
export default function IconPicker({ value, onChange }) {
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState('')
  const shown = iconNames.filter((n) => iconLabel(n).includes(query.trim().toLowerCase()))

  function pick(name) {
    onChange(name)
    setOpen(false)
    setQuery('')
  }

  return (
    <div className="adm-icon-field">
      <span className="adm-icon-preview">
        {value && icons[value] ? <FontAwesomeIcon icon={icons[value]} /> : <span className="adm-muted">None</span>}
      </span>
      <button type="button" className="adm-btn adm-btn-outline adm-btn-sm" onClick={() => setOpen(true)}>
        {value ? 'Change' : 'Choose icon'}
      </button>
      {value && (
        <button type="button" className="adm-link-btn" onClick={() => onChange(null)}>
          Remove
        </button>
      )}

      {open && (
        <Modal title="Choose an icon" onClose={() => setOpen(false)} wide>
          <input
            className="adm-input"
            type="search"
            placeholder="Search icons"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            autoFocus
          />
          <div className="adm-icon-grid">
            {shown.map((name) => (
              <button
                type="button"
                key={name}
                className={`adm-icon-cell${name === value ? ' is-selected' : ''}`}
                onClick={() => pick(name)}
                title={iconLabel(name)}
              >
                <FontAwesomeIcon icon={icons[name]} />
                <span>{iconLabel(name)}</span>
              </button>
            ))}
            {!shown.length && <p className="adm-muted">No icons match that search.</p>}
          </div>
        </Modal>
      )}
    </div>
  )
}
