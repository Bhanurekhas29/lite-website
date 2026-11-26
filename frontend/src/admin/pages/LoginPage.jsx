import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { useAuth } from '../AuthContext.jsx'

export default function LoginPage() {
  const { user, login } = useAuth()
  const navigate = useNavigate()
  const [identifier, setIdentifier] = useState('')
  const [password, setPassword] = useState('')
  const [show, setShow] = useState(false)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  if (user) return <Navigate to="/admin" replace />

  async function submit(event) {
    event.preventDefault()
    if (!identifier.trim() || !password) {
      setError('Enter your username or email and password')
      return
    }
    setBusy(true)
    setError('')
    try {
      await login(identifier.trim(), password)
      navigate('/admin', { replace: true })
    } catch (err) {
      setError(err.message)
      setBusy(false)
    }
  }

  return (
    <main className="adm-login">
      <form className="adm-login-card" onSubmit={submit} noValidate>
        <div className="adm-login-brand">
          <span className="adm-login-name">E for Events</span>
          <span className="adm-login-sub">Admin panel</span>
        </div>

        <label className="adm-field">
          <span>Username or email</span>
          <input
            type="text"
            autoComplete="username"
            value={identifier}
            onChange={(e) => { setIdentifier(e.target.value); setError('') }}
            autoFocus
          />
        </label>

        <label className="adm-field">
          <span>Password</span>
          <span className="adm-password">
            <input
              type={show ? 'text' : 'password'}
              autoComplete="current-password"
              value={password}
              onChange={(e) => { setPassword(e.target.value); setError('') }}
            />
            <button
              type="button"
              className="adm-icon-btn"
              onClick={() => setShow((v) => !v)}
              aria-label={show ? 'Hide password' : 'Show password'}
            >
              <FontAwesomeIcon icon={show ? faEyeSlash : faEye} />
            </button>
          </span>
        </label>

        {error && <p className="adm-error" role="alert">{error}</p>}

        <button className="adm-btn adm-btn-block" type="submit" disabled={busy}>
          {busy ? 'Signing in…' : 'Sign in'}
        </button>
      </form>
    </main>
  )
}
