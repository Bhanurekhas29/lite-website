import {
  faArrowLeft,
  faCircleCheck,
  faEnvelope,
  faEye,
  faEyeSlash,
  faGlobe,
  faLocationDot,
  faLock,
  faPhone,
} from '@fortawesome/free-solid-svg-icons'
import { faInstagram } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useState } from 'react'
import { Link, Navigate, useLocation, useNavigate, useSearchParams } from 'react-router-dom'
import { api } from '../api.js'
import { useAuth } from '../AuthContext.jsx'

// The public company details shown beside the form (the same ones the website uses).
function useCompany() {
  const [site, setSite] = useState(null)
  useEffect(() => {
    let cancelled = false
    fetch('/api/site')
      .then((r) => (r.ok ? r.json() : null))
      .then((d) => !cancelled && setSite(d))
      .catch(() => {})
    return () => {
      cancelled = true
    }
  }, [])
  return site
}

function PasswordInput({ id, value, onChange, autoComplete, label, autoFocus }) {
  const [show, setShow] = useState(false)
  return (
    <label className="adm-field" htmlFor={id}>
      <span>{label}</span>
      <span className="adm-password">
        <input
          id={id}
          type={show ? 'text' : 'password'}
          autoComplete={autoComplete}
          value={value}
          onChange={onChange}
          autoFocus={autoFocus}
        />
        <button type="button" className="adm-icon-btn" onClick={() => setShow((v) => !v)} aria-label={show ? 'Hide password' : 'Show password'}>
          <FontAwesomeIcon icon={show ? faEyeSlash : faEye} />
        </button>
      </span>
    </label>
  )
}

// The left half: logo, company name and details. The logo itself never moves.
function CompanySide({ site }) {
  const brand = site?.brand
  const contact = site?.contact ?? {}
  const social = site?.social ?? {}
  const logo = brand?.logo_footer || brand?.logo_header
  const rows = [
    contact.email && { icon: faEnvelope, text: contact.email, href: `mailto:${contact.email}` },
    contact.website && { icon: faGlobe, text: contact.website, href: /^https?:/i.test(contact.website) ? contact.website : `https://${contact.website}` },
    social.instagram && { icon: faInstagram, text: social.instagram, href: `https://www.instagram.com/${social.instagram.replace(/^@/, '')}` },
    contact.phone && { icon: faPhone, text: contact.phone, href: `tel:${contact.phone.replace(/\s+/g, '')}` },
    contact.address && { icon: faLocationDot, text: contact.address },
  ].filter(Boolean)

  return (
    <aside className="lg-side">
      <span className="lg-orb lg-orb-a" aria-hidden="true" />
      <span className="lg-orb lg-orb-b" aria-hidden="true" />
      <span className="lg-orb lg-orb-c" aria-hidden="true" />

      <div className="lg-side-inner">
        {logo && <img className="lg-logo" src={logo.url} alt={logo.alt || brand?.name || ''} />}

        <p className="lg-kicker lg-rise" style={{ '--i': 0 }}>Admin panel</p>
        <h1 className="lg-company lg-rise" style={{ '--i': 1 }}>{brand?.name || 'E for Events'}</h1>
        {brand?.footer_tagline && <p className="lg-tagline lg-rise" style={{ '--i': 2 }}>{brand.footer_tagline}</p>}
        {brand?.footer_description && <p className="lg-about lg-rise" style={{ '--i': 3 }}>{brand.footer_description}</p>}

        {rows.length > 0 && (
          <ul className="lg-details">
            {rows.map((row, i) => (
              <li className="lg-slide" style={{ '--i': 4 + i }} key={row.text}>
                <span className="lg-detail-icon"><FontAwesomeIcon icon={row.icon} aria-hidden="true" /></span>
                {row.href ? <a href={row.href}>{row.text}</a> : <span>{row.text}</span>}
              </li>
            ))}
          </ul>
        )}

        <p className="lg-copy lg-rise" style={{ '--i': 9 }}>{brand?.footer_copyright || `© ${brand?.name || 'E for Events'}`}</p>
      </div>
    </aside>
  )
}

function SignIn({ onForgot }) {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [identifier, setIdentifier] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

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
    <form className="lg-form" onSubmit={submit} noValidate>
      <p className="lg-eyebrow lg-rise" style={{ '--i': 0 }}>Welcome back</p>
      <h2 className="lg-title lg-rise" style={{ '--i': 1 }}>Sign in to your account</h2>
      <p className="lg-lead lg-rise" style={{ '--i': 2 }}>Use your username or email address and your password.</p>

      <div className="lg-rise" style={{ '--i': 3 }}>
        <label className="adm-field" htmlFor="login-id">
          <span>Username or email</span>
          <input
            id="login-id"
            type="text"
            autoComplete="username"
            value={identifier}
            onChange={(e) => { setIdentifier(e.target.value); setError('') }}
            autoFocus
          />
        </label>
      </div>

      <div className="lg-rise" style={{ '--i': 4 }}>
        <PasswordInput
          id="login-password"
          label="Password"
          autoComplete="current-password"
          value={password}
          onChange={(e) => { setPassword(e.target.value); setError('') }}
        />
      </div>

      <div className="lg-row lg-rise" style={{ '--i': 5 }}>
        <button type="button" className="lg-link" onClick={onForgot}>Forgot password?</button>
      </div>

      {error && <p className="adm-error" role="alert">{error}</p>}

      <div className="lg-rise" style={{ '--i': 6 }}>
        <button className="adm-btn adm-btn-block" type="submit" disabled={busy}>
          {busy ? 'Signing in…' : 'Sign in'}
        </button>
      </div>
      <p className="lg-secure lg-rise" style={{ '--i': 7 }}><FontAwesomeIcon icon={faLock} aria-hidden="true" /> Authorised users only</p>
    </form>
  )
}

function Forgot({ onBack }) {
  const [identifier, setIdentifier] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const [sent, setSent] = useState(false)

  async function submit(event) {
    event.preventDefault()
    if (!identifier.trim()) {
      setError('Enter your username or email')
      return
    }
    setBusy(true)
    setError('')
    try {
      await api('/admin/forgot-password', { method: 'POST', body: { identifier: identifier.trim() } })
      setSent(true)
    } catch (err) {
      setError(err.message)
    } finally {
      setBusy(false)
    }
  }

  if (sent) {
    return (
      <div className="lg-form lg-done">
        <span className="lg-bigicon"><FontAwesomeIcon icon={faEnvelope} aria-hidden="true" /></span>
        <h2 className="lg-title lg-rise" style={{ '--i': 0 }}>Check your email</h2>
        <p className="lg-lead lg-rise" style={{ '--i': 1 }}>
          If an account matches, we have sent a link to reset the password. It works for one hour.
        </p>
        <p className="lg-lead lg-rise" style={{ '--i': 2 }}>No email after a few minutes? Check your spam folder, or ask the super admin to help.</p>
        <button type="button" className="lg-link lg-rise" style={{ '--i': 3 }} onClick={onBack}>
          <FontAwesomeIcon icon={faArrowLeft} aria-hidden="true" /> Back to sign in
        </button>
      </div>
    )
  }

  return (
    <form className="lg-form" onSubmit={submit} noValidate>
      <p className="lg-eyebrow lg-rise" style={{ '--i': 0 }}>Password help</p>
      <h2 className="lg-title lg-rise" style={{ '--i': 1 }}>Forgot your password?</h2>
      <p className="lg-lead lg-rise" style={{ '--i': 2 }}>Enter your username or email and we will email you a link to choose a new one.</p>
      <div className="lg-rise" style={{ '--i': 3 }}>
        <label className="adm-field" htmlFor="forgot-id">
          <span>Username or email</span>
          <input id="forgot-id" type="text" autoComplete="username" value={identifier} onChange={(e) => { setIdentifier(e.target.value); setError('') }} autoFocus />
        </label>
      </div>
      {error && <p className="adm-error" role="alert">{error}</p>}
      <div className="lg-rise" style={{ '--i': 4 }}>
        <button className="adm-btn adm-btn-block" type="submit" disabled={busy}>{busy ? 'Sending…' : 'Send reset link'}</button>
      </div>
      <button type="button" className="lg-link lg-rise" style={{ '--i': 5 }} onClick={onBack}>
        <FontAwesomeIcon icon={faArrowLeft} aria-hidden="true" /> Back to sign in
      </button>
    </form>
  )
}

function Reset({ token }) {
  const navigate = useNavigate()
  const [state, setState] = useState('checking') // checking | ready | invalid | done
  const [password, setPassword] = useState('')
  const [again, setAgain] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    let cancelled = false
    api('/admin/reset-password/check', { method: 'POST', body: { token } })
      .then((d) => !cancelled && setState(d.valid ? 'ready' : 'invalid'))
      .catch(() => !cancelled && setState('invalid'))
    return () => {
      cancelled = true
    }
  }, [token])

  async function submit(event) {
    event.preventDefault()
    if (password.length < 10 || !/[A-Za-z]/.test(password) || !/\d/.test(password)) {
      setError('Use at least 10 characters, with letters and numbers')
      return
    }
    if (password !== again) {
      setError('The two passwords do not match')
      return
    }
    setBusy(true)
    setError('')
    try {
      await api('/admin/reset-password', { method: 'POST', body: { token, password } })
      setState('done')
    } catch (err) {
      setError(err.message)
    } finally {
      setBusy(false)
    }
  }

  if (state === 'checking') return <div className="lg-form"><p className="lg-lead">Checking your link…</p></div>

  if (state === 'invalid') {
    return (
      <div className="lg-form lg-done">
        <h2 className="lg-title lg-rise" style={{ '--i': 0 }}>This link has expired</h2>
        <p className="lg-lead lg-rise" style={{ '--i': 1 }}>Reset links work once and for one hour. Ask for a new one.</p>
        <Link className="adm-btn adm-btn-block lg-rise" style={{ '--i': 2 }} to="/admin/login">Back to sign in</Link>
      </div>
    )
  }

  if (state === 'done') {
    return (
      <div className="lg-form lg-done">
        <span className="lg-bigicon lg-ok"><FontAwesomeIcon icon={faCircleCheck} aria-hidden="true" /></span>
        <h2 className="lg-title lg-rise" style={{ '--i': 0 }}>Password updated</h2>
        <p className="lg-lead lg-rise" style={{ '--i': 1 }}>You can now sign in with your new password.</p>
        <button type="button" className="adm-btn adm-btn-block lg-rise" style={{ '--i': 2 }} onClick={() => navigate('/admin/login', { replace: true })}>Sign in</button>
      </div>
    )
  }

  return (
    <form className="lg-form" onSubmit={submit} noValidate>
      <p className="lg-eyebrow lg-rise" style={{ '--i': 0 }}>Password help</p>
      <h2 className="lg-title lg-rise" style={{ '--i': 1 }}>Choose a new password</h2>
      <p className="lg-lead lg-rise" style={{ '--i': 2 }}>At least 10 characters, with letters and numbers.</p>
      <div className="lg-rise" style={{ '--i': 3 }}>
        <PasswordInput id="new-password" label="New password" autoComplete="new-password" value={password} onChange={(e) => { setPassword(e.target.value); setError('') }} autoFocus />
      </div>
      <div className="lg-rise" style={{ '--i': 4 }}>
        <PasswordInput id="new-password-again" label="Type it again" autoComplete="new-password" value={again} onChange={(e) => { setAgain(e.target.value); setError('') }} />
      </div>
      {error && <p className="adm-error" role="alert">{error}</p>}
      <div className="lg-rise" style={{ '--i': 5 }}>
        <button className="adm-btn adm-btn-block" type="submit" disabled={busy}>{busy ? 'Saving…' : 'Set new password'}</button>
      </div>
    </form>
  )
}

export default function LoginPage() {
  const { user } = useAuth()
  const site = useCompany()
  const location = useLocation()
  const [params] = useSearchParams()
  const token = params.get('token') || ''
  const isReset = location.pathname.endsWith('/reset-password')
  const [view, setView] = useState('signin') // signin | forgot

  if (user) return <Navigate to="/admin" replace />

  return (
    <main className="lg">
      <CompanySide site={site} />
      <section className="lg-panel">
        <div className="lg-panel-inner" key={isReset ? 'reset' : view}>
          {isReset ? (
            <Reset token={token} />
          ) : view === 'forgot' ? (
            <Forgot onBack={() => setView('signin')} />
          ) : (
            <SignIn onForgot={() => setView('forgot')} />
          )}
        </div>
      </section>
    </main>
  )
}
