import { faCloudArrowUp } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useRef, useState } from 'react'
import { api, uploadImage } from '../api.js'
import Modal from './Modal.jsx'

// Choose an image from the library, or upload a new one.
export default function MediaPicker({ onSelect, onClose }) {
  const [items, setItems] = useState(null)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const fileRef = useRef(null)

  useEffect(() => {
    api('/admin/media')
      .then((d) => setItems(d.media))
      .catch((e) => setError(e.message))
  }, [])

  async function upload(event) {
    const file = event.target.files?.[0]
    event.target.value = ''
    if (!file) return
    setBusy(true)
    setError('')
    try {
      const data = await uploadImage(file)
      onSelect(data.media)
    } catch (e) {
      setError(e.message)
      setBusy(false)
    }
  }

  return (
    <Modal title="Choose an image" onClose={onClose} wide>
      <div className="adm-actions">
        <input ref={fileRef} type="file" accept="image/jpeg,image/png,image/webp,image/gif" hidden onChange={upload} />
        <button type="button" className="adm-btn" onClick={() => fileRef.current?.click()} disabled={busy}>
          <FontAwesomeIcon icon={faCloudArrowUp} /> {busy ? 'Uploading…' : 'Upload new image'}
        </button>
        <span className="adm-hint">JPG, PNG, WebP or GIF, up to 8 MB.</span>
      </div>
      {error && <p className="adm-error" role="alert">{error}</p>}

      {items === null && !error && <p className="adm-muted">Loading…</p>}
      {items?.length === 0 && <p className="adm-muted">No images yet. Upload the first one.</p>}
      <div className="adm-media-grid">
        {items?.map((m) => (
          <button type="button" key={m.id} className="adm-media-cell" onClick={() => onSelect(m)} title={m.alt || m.file_name}>
            <img src={m.url} alt={m.alt} loading="lazy" />
            <span>{m.alt || m.file_name}</span>
          </button>
        ))}
      </div>
    </Modal>
  )
}
