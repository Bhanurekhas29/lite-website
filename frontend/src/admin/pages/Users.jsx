import { faPenToSquare, faPlus, faTrash, faUserShield } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useCallback, useEffect, useState } from 'react'
import { api } from '../api.js'
import ConfirmDialog from '../components/ConfirmDialog.jsx'
import Modal from '../components/Modal.jsx'
import Toggle from '../components/Toggle.jsx'
import { formatDateTime } from '../formatDate.js'
import { useAuth } from '../AuthContext.jsx'
import { useLayoutData } from '../layoutData.js'

const empty = { display_name: '', username: '', email: '', password: '', is_active: true }

function UserForm({ user, onClose, onSaved }) {
  const editing = !!user
  const [form, setForm] = useState(editing ? { ...user, password: '' } : empty)
  const [show, setShow] = useState(false)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const set = (patch) => {
    setForm((f) => ({ ...f, ...patch }))
    setError('')
  }

  async function save(event) {
    event.preventDefault()
    setBusy(true)
    setError('')
    try {
      const body = { display_name: form.display_name, username: form.username, email: form.email, password: form.password, is_active: form.is_active }
      await api(editing ? `/admin/users/${user.id}` : '/admin/users', { method: editing ? 'PUT' : 'POST', body })
      onSaved(editing ? 'User updated.' : 'User added. Share the username and password with them.')
    } catch (e) {
      setError(e.message)
      setBusy(false)
    }
  }

  return (
    <Modal title={editing ? 'Edit user' : 'Add a user'} onClose={onClose}>
      <form onSubmit={save} noValidate>
        <label className="adm-field">
          <span className="adm-label">Name</span>
          <input className="adm-input" value={form.display_name} onChange={(e) => set({ display_name: e.target.value })} maxLength={120} autoFocus />
        </label>
        <label className="adm-field">
          <span className="adm-label">Username</span>
          <input className="adm-input" value={form.username} onChange={(e) => set({ username: e.target.value.toLowerCase() })} maxLength={40} autoComplete="off" />
          <span className="adm-hint adm-field-help">Lowercase letters, numbers, dots, dashes or underscores. Used to sign in.</span>
        </label>
        <label className="adm-field">
          <span className="adm-label">Email</span>
          <input className="adm-input" type="email" value={form.email} onChange={(e) => set({ email: e.target.value })} maxLength={190} autoComplete="off" />
          <span className="adm-hint adm-field-help">Password reset links are sent here.</span>
        </label>
        <label className="adm-field">
          <span className="adm-label">{editing ? 'New password (leave empty to keep the current one)' : 'Password'}</span>
          <span className="adm-password">
            <input className="adm-input" type={show ? 'text' : 'password'} value={form.password} onChange={(e) => set({ password: e.target.value })} autoComplete="new-password" />
            <button type="button" className="adm-link-btn adm-show-pw" onClick={() => setShow((v) => !v)}>{show ? 'Hide' : 'Show'}</button>
          </span>
          <span className="adm-hint adm-field-help">At least 10 characters, with letters and numbers.</span>
        </label>
        {!user?.is_super && (
          <div className="adm-field adm-field-inline">
            <Toggle checked={form.is_active} onChange={(on) => set({ is_active: on })} label="Account is active" />
            <span>Account is active (can sign in)</span>
          </div>
        )}
        {error && <p className="adm-error" role="alert">{error}</p>}
        <div className="adm-actions adm-actions-end">
          <button type="button" className="adm-btn adm-btn-outline" onClick={onClose}>Cancel</button>
          <button type="submit" className="adm-btn" disabled={busy}>{busy ? 'Saving…' : editing ? 'Save changes' : 'Add user'}</button>
        </div>
      </form>
    </Modal>
  )
}

export default function Users() {
  const { user: me } = useAuth()
  const { overview, reload } = useLayoutData()
  const [users, setUsers] = useState(null)
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [editing, setEditing] = useState(undefined) // undefined = closed, null = adding, object = editing
  const [removing, setRemoving] = useState(null)
  const [busy, setBusy] = useState(false)

  const load = useCallback(async () => {
    try {
      setUsers((await api('/admin/users')).users)
      setError('')
    } catch (e) {
      setError(e.message)
    }
  }, [])

  useEffect(() => {
    load()
  }, [load])

  async function remove() {
    setBusy(true)
    try {
      await api(`/admin/users/${removing.id}`, { method: 'DELETE', body: {} })
      setMessage(`${removing.display_name} was deleted.`)
      setRemoving(null)
      reload()
      await load()
    } catch (e) {
      setError(e.message)
      setRemoving(null)
    } finally {
      setBusy(false)
    }
  }

  return (
    <>
      <div className="adm-pagehead adm-pagehead-split">
        <div>
          <h1>Users</h1>
          <div className="adm-muted">People who can sign in to this admin panel. Every admin can edit the website; only the super admin manages users.</div>
        </div>
        <button type="button" className="adm-btn" onClick={() => setEditing(null)}>
          <FontAwesomeIcon icon={faPlus} /> Add user
        </button>
      </div>

      {message && <p className="adm-status-ok adm-media-message" role="status">{message}</p>}
      {error && <p className="adm-error" role="alert">{error}</p>}
      {!users && !error && <p className="adm-muted">Loading…</p>}

      {users && (
        <div className="adm-list" role="list">
          <div className="adm-list-head adm-urow-head" aria-hidden="true">
            <span>Name</span>
            <span>Username</span>
            <span>Role</span>
            <span>Last sign in</span>
            <span>Actions</span>
          </div>
          {users.map((u) => (
            <div className="adm-urow" role="listitem" key={u.id}>
              <span className="adm-urow-name">
                <b>{u.display_name}{u.id === me?.id && <small> (you)</small>}</b>
                <small>{u.email}</small>
              </span>
              <span className="adm-row-cell" data-label="Username">{u.username}</span>
              <span className="adm-row-cell" data-label="Role">
                {u.is_super ? <span className="adm-tag"><FontAwesomeIcon icon={faUserShield} /> Super admin</span> : <span className="adm-tag adm-tag-soft">Admin</span>}
                {!u.is_active && <span className="adm-tag adm-tag-off">Switched off</span>}
              </span>
              <span className="adm-row-cell" data-label="Last sign in">{u.last_login_at ? formatDateTime(u.last_login_at, overview?.timezone) : 'Never'}</span>
              <span className="adm-erow-actions">
                <button type="button" className="adm-icon-btn" onClick={() => setEditing(u)} aria-label={`Edit ${u.display_name}`} title="Edit">
                  <FontAwesomeIcon icon={faPenToSquare} />
                </button>
                {!u.is_super && u.id !== me?.id && (
                  <button type="button" className="adm-icon-btn adm-icon-danger" onClick={() => setRemoving(u)} aria-label={`Delete ${u.display_name}`} title="Delete">
                    <FontAwesomeIcon icon={faTrash} />
                  </button>
                )}
              </span>
            </div>
          ))}
        </div>
      )}

      {editing !== undefined && (
        <UserForm
          user={editing}
          onClose={() => setEditing(undefined)}
          onSaved={async (text) => {
            setEditing(undefined)
            setMessage(text)
            reload()
            await load()
          }}
        />
      )}

      {removing && (
        <ConfirmDialog
          title={`Delete ${removing.display_name}?`}
          message="They will no longer be able to sign in. This can't be undone."
          confirmLabel="Delete"
          busy={busy}
          onConfirm={remove}
          onCancel={() => setRemoving(null)}
        />
      )}
    </>
  )
}
