import { faCircleCheck } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useState } from 'react'
import { api } from '../api.js'
import { useAuth } from '../AuthContext.jsx'

function PasswordField({ label, value, onChange, autoComplete, hint }) {
  const [show, setShow] = useState(false)
  return (
    <label className="adm-field">
      <span className="adm-label">{label}</span>
      <span className="adm-password">
        <input className="adm-input" type={show ? 'text' : 'password'} value={value} onChange={onChange} autoComplete={autoComplete} />
        <button type="button" className="adm-link-btn adm-show-pw" onClick={() => setShow((v) => !v)}>{show ? 'Hide' : 'Show'}</button>
      </span>
      {hint && <span className="adm-hint adm-field-help">{hint}</span>}
    </label>
  )
}

// Profile settings: for now only the password can be changed.
export default function Profile() {
  const { user } = useAuth()
  const [form, setForm] = useState({ current: '', next: '', again: '' })
  const [error, setError] = useState('')
  const [done, setDone] = useState(false)
  const [busy, setBusy] = useState(false)
  const set = (patch) => {
    setForm((f) => ({ ...f, ...patch }))
    setError('')
    setDone(false)
  }

  async function save(event) {
    event.preventDefault()
    if (!form.current) {
      setError('Enter your current password')
      return
    }
    if (form.next.length < 10 || !/[A-Za-z]/.test(form.next) || !/\d/.test(form.next)) {
      setError('Use at least 10 characters, with letters and numbers')
      return
    }
    if (form.next !== form.again) {
      setError('The two new passwords do not match')
      return
    }
    setBusy(true)
    try {
      await api('/admin/profile/password', { method: 'POST', body: { current_password: form.current, password: form.next } })
      setForm({ current: '', next: '', again: '' })
      setDone(true)
    } catch (e) {
      setError(e.message)
    } finally {
      setBusy(false)
    }
  }

  return (
    <>
      <div className="adm-pagehead">
        <h1>Profile settings</h1>
      </div>

      <div className="adm-card adm-profile-card">
        <h2 className="adm-card-title">Your account</h2>
        <dl className="adm-details-grid">
          <div className="adm-detail"><dt>Name</dt><dd>{user?.display_name}</dd></div>
          <div className="adm-detail"><dt>Username</dt><dd>{user?.username}</dd></div>
          <div className="adm-detail"><dt>Email</dt><dd>{user?.email || '–'}</dd></div>
          <div className="adm-detail"><dt>Role</dt><dd>{user?.is_super ? 'Super admin' : 'Admin'}</dd></div>
        </dl>
      </div>

      <form className="adm-card adm-profile-card" onSubmit={save} noValidate>
        <h2 className="adm-card-title">Change password</h2>
        <PasswordField label="Current password" value={form.current} autoComplete="current-password" onChange={(e) => set({ current: e.target.value })} />
        <PasswordField label="New password" value={form.next} autoComplete="new-password" hint="At least 10 characters, with letters and numbers." onChange={(e) => set({ next: e.target.value })} />
        <PasswordField label="Type the new password again" value={form.again} autoComplete="new-password" onChange={(e) => set({ again: e.target.value })} />
        {error && <p className="adm-error" role="alert">{error}</p>}
        {done && <p className="adm-status-ok" role="status"><FontAwesomeIcon icon={faCircleCheck} /> Your password was changed.</p>}
        <div className="adm-actions">
          <button type="submit" className="adm-btn" disabled={busy}>{busy ? 'Saving…' : 'Change password'}</button>
        </div>
      </form>
    </>
  )
}
