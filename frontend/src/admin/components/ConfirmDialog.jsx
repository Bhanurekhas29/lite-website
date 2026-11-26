import { useEffect, useRef } from 'react'

// Simple confirmation popup. Escape or the Cancel button closes it.
export default function ConfirmDialog({ title, message, confirmLabel, onConfirm, onCancel, busy }) {
  const cancelRef = useRef(null)

  useEffect(() => {
    cancelRef.current?.focus()
    const onKey = (e) => e.key === 'Escape' && onCancel()
    document.addEventListener('keydown', onKey)
    return () => document.removeEventListener('keydown', onKey)
  }, [onCancel])

  return (
    <div className="adm-modal" onMouseDown={(e) => e.target === e.currentTarget && onCancel()}>
      <div className="adm-modal-box" role="dialog" aria-modal="true" aria-labelledby="adm-dialog-title">
        <h2 id="adm-dialog-title">{title}</h2>
        <p className="adm-muted">{message}</p>
        <div className="adm-actions adm-actions-end">
          <button type="button" className="adm-btn adm-btn-outline" onClick={onCancel} ref={cancelRef}>
            Cancel
          </button>
          <button type="button" className="adm-btn" onClick={onConfirm} disabled={busy}>
            {confirmLabel}
          </button>
        </div>
      </div>
    </div>
  )
}
