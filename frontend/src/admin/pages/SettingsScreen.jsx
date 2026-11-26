import { faPaperPlane } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useMemo, useState } from 'react'
import { api } from '../api.js'
import { useAuth } from '../AuthContext.jsx'
import Field from '../components/Field.jsx'

// Turn the values from the API into form state. Secrets get extra editing state.
function toDraft(screen, values) {
  const draft = {}
  for (const card of screen.cards) {
    for (const spec of card.fields) {
      const v = values[spec.id]
      draft[spec.id] = spec.type === 'secret' ? { set: v.set, editing: false, input: '', clear: false } : v
    }
  }
  return draft
}

// Turn form state into what the API expects. Unchanged secrets are left out.
function toPayload(screen, draft) {
  const values = {}
  for (const card of screen.cards) {
    for (const spec of card.fields) {
      const v = draft[spec.id]
      if (spec.type === 'secret') {
        if (v.clear) values[spec.id] = null
        else if (v.input) values[spec.id] = v.input
      } else {
        values[spec.id] = v
      }
    }
  }
  return values
}

function TestEmail({ disabled }) {
  const { user } = useAuth()
  const [to, setTo] = useState(user?.email ?? '')
  const [status, setStatus] = useState({ kind: '', text: '' })
  const [busy, setBusy] = useState(false)

  async function send() {
    setBusy(true)
    setStatus({ kind: '', text: '' })
    try {
      await api('/admin/settings/email/test', { method: 'POST', body: { to } })
      setStatus({ kind: 'ok', text: `Test email sent to ${to}. Check the inbox, and the spam folder.` })
    } catch (e) {
      setStatus({ kind: 'error', text: e.message })
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="adm-card">
      <h2 className="adm-card-title">Send a test email</h2>
      <p className="adm-muted">Uses the saved settings above.{disabled && ' Save your changes first.'}</p>
      <div className="adm-row">
        <input className="adm-input" type="email" placeholder="Send the test to" value={to} onChange={(e) => setTo(e.target.value)} aria-label="Send the test to" />
        <button type="button" className="adm-btn" onClick={send} disabled={busy || disabled || !to}>
          <FontAwesomeIcon icon={faPaperPlane} /> {busy ? 'Sending…' : 'Send test email'}
        </button>
      </div>
      {status.text && (
        <p className={status.kind === 'error' ? 'adm-error' : 'adm-status-ok'} role="status">{status.text}</p>
      )}
    </div>
  )
}

// One Settings screen (Brand, Contact and social, SEO, Email), built from the backend definition.
export default function SettingsScreen({ screen: name }) {
  const [data, setData] = useState(null)
  const [draft, setDraft] = useState(null)
  const [base, setBase] = useState('')
  const [error, setError] = useState('')
  const [status, setStatus] = useState({ kind: '', text: '' })
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    let cancelled = false
    api(`/admin/settings/${name}`)
      .then((d) => {
        if (cancelled) return
        const initial = toDraft(d.screen, d.values)
        setData(d)
        setDraft(initial)
        setBase(JSON.stringify(initial))
      })
      .catch((e) => !cancelled && setError(e.message))
    return () => {
      cancelled = true
    }
  }, [name])

  const dirty = useMemo(() => draft !== null && JSON.stringify(draft) !== base, [draft, base])

  if (error) return <div className="adm-card"><p className="adm-error">{error}</p></div>
  if (!data) return <p className="adm-muted">Loading…</p>

  async function save() {
    setBusy(true)
    setStatus({ kind: '', text: '' })
    try {
      const res = await api(`/admin/settings/${name}`, { method: 'PUT', body: { values: toPayload(data.screen, draft) } })
      const fresh = toDraft(res.screen, res.values)
      setData(res)
      setDraft(fresh)
      setBase(JSON.stringify(fresh))
      setStatus({ kind: 'ok', text: 'Saved' })
    } catch (e) {
      setStatus({ kind: 'error', text: e.message })
    } finally {
      setBusy(false)
    }
  }

  function discard() {
    const initial = toDraft(data.screen, data.values)
    setDraft(initial)
    setBase(JSON.stringify(initial))
    setStatus({ kind: '', text: '' })
  }

  return (
    <>
      <div className="adm-pagehead">
        <h1>{data.screen.title}</h1>
      </div>

      {data.screen.cards.map((card) => (
        <div className="adm-card" key={card.title}>
          <h2 className="adm-card-title">{card.title}</h2>
          {card.help && <p className="adm-muted">{card.help}</p>}
          <div className="adm-settings-fields">
            {card.fields.map((spec) => (
              <Field
                key={spec.id}
                spec={spec}
                value={draft[spec.id]}
                onChange={(v) => setDraft((d) => ({ ...d, [spec.id]: v }))}
              />
            ))}
          </div>
        </div>
      ))}

      <div className="adm-actions adm-actions-end adm-savebar adm-sticky-save">
        {status.text && <span className={status.kind === 'error' ? 'adm-status-error' : 'adm-status-ok'} role="status">{status.text}</span>}
        <button type="button" className="adm-btn adm-btn-outline" onClick={discard} disabled={!dirty || busy}>Discard</button>
        <button type="button" className="adm-btn" onClick={save} disabled={!dirty || busy}>{busy ? 'Saving…' : 'Save changes'}</button>
      </div>

      {name === 'email' && <TestEmail disabled={dirty} />}
    </>
  )
}
