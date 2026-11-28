import { faCalendarDays, faLocationDot } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { lazy, Suspense, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { siteIcons } from '../../shared/icons.js'
import { bgProps, Eyebrow, Heading, Icon, Paragraphs } from '../components/ui.jsx'

// The map only loads when a visitor asks for it, so it never slows the page down.
const LocationPicker = lazy(() => import('../components/LocationPicker.jsx'))

const shortDate = (iso) =>
  new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${iso}T12:00:00`))

// Telephone: numbers only, with one optional + at the very start (for example +44).
const phoneDigits = (text) => {
  const plus = text.trimStart().startsWith('+') ? '+' : ''
  return plus + text.replace(/\D/g, '')
}

const lines = (text) => (text || '').split(/\r?\n/).map((s) => s.trim()).filter(Boolean)

// The public enquiry form. Which fields show, their labels, order and choices come from the admin.
export default function EnquiryForm({ section }) {
  const f = section.fields
  const items = section.items ?? []
  const [values, setValues] = useState({})
  const [services, setServices] = useState([])
  const [consent, setConsent] = useState(false)
  const [trap, setTrap] = useState('')
  const [state, setState] = useState({ status: 'idle', error: '' })
  const [mapOpen, setMapOpen] = useState(false)
  const [errors, setErrors] = useState({}) // field key -> message, for every field that needs attention
  const dateRef = useRef(null)

  // Back to an empty form, so a visitor can send a second enquiry without reloading the page.
  const another = () => {
    setValues({})
    setServices([])
    setConsent(false)
    setTrap('')
    setErrors({})
    setMapOpen(false)
    setState({ status: 'idle', error: '' })
    requestAnimationFrame(() => document.getElementById(`section-${section.id}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' }))
  }

  const clearError = (key) => setErrors((e) => (e[key] ? { ...e, [key]: undefined } : e))
  const set = (key, value) => {
    setValues((v) => ({ ...v, [key]: value }))
    clearError(key)
  }
  const toggleService = (name) => {
    setServices((s) => (s.includes(name) ? s.filter((x) => x !== name) : [...s, name]))
    clearError('services')
  }

  async function submit(event) {
    event.preventDefault()
    // Check every field, so all the ones that need attention are shown at once.
    const found = {}
    for (const item of items) {
      const { field, label, required } = item.fields
      const text = (values[field] || '').trim()
      const empty = field === 'services' ? services.length === 0 : !text
      if (required && empty) {
        found[field] =
          field === 'services' ? 'Select at least one option' : field === 'enquiry_type' || field === 'attendance' ? 'Choose an option' : `${label || 'This field'} is required`
      }
      else if (field === 'email' && text && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text)) found[field] = 'Enter a valid email address'
    }
    if (!consent) found.consent = 'Please confirm that you agree to the privacy policy'
    setErrors(found)
    if (Object.keys(found).length) {
      setState({ status: 'idle', error: '' })
      const firstKey = [...items.map((i) => i.fields.field), 'consent'].find((k) => found[k])
      requestAnimationFrame(() => {
        const target = document.getElementById(firstKey === 'consent' ? 'enquiry-consent' : `enquiry-${firstKey}`) || document.querySelector('.field-error')
        target?.scrollIntoView({ block: 'center', behavior: 'smooth' })
        target?.focus?.({ preventScroll: true })
      })
      return
    }

    setState({ status: 'sending', error: '' })
    try {
      const response = await fetch('/api/enquiries', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...values, services, consent: true, website_url: trap }),
      })
      const data = await response.json().catch(() => ({}))
      if (!response.ok) throw new Error(data.error || 'Your enquiry could not be sent. Please try again.')
      setState({ status: 'sent', error: '' })
    } catch (e) {
      setState({ status: 'idle', error: e.message })
    }
  }

  function control(item) {
    const { field, placeholder, label } = item.fields
    const options = lines(item.fields.options)
    const id = `enquiry-${field}`
    const common = {
      id,
      name: field,
      value: values[field] || '',
      onChange: (e) => set(field, field === 'phone' ? phoneDigits(e.target.value) : e.target.value),
      placeholder,
      'aria-invalid': errors[field] ? 'true' : undefined,
      'aria-describedby': errors[field] ? `${id}-error` : undefined,
    }

    if (field === 'enquiry_type' || field === 'attendance') {
      return (
        <select {...common} placeholder={undefined}>
          <option value="">{placeholder || 'Select'}</option>
          {options.map((o) => <option key={o} value={o}>{o}</option>)}
        </select>
      )
    }
    if (field === 'services') {
      return (
        <div className="check-box" role="group" aria-label={label}>
          {options.map((o) => (
            <label key={o}>
              <input type="checkbox" checked={services.includes(o)} onChange={() => toggleService(o)} />
              <span>{o}</span>
            </label>
          ))}
        </div>
      )
    }
    if (field === 'timeframe') {
      const today = new Date().toISOString().slice(0, 10)
      return (
        <div className="input-icon">
          <input {...common} type="text" maxLength={150} />
          <input
            ref={dateRef}
            type="date"
            className="date-native"
            min={today}
            tabIndex={-1}
            aria-hidden="true"
            onChange={(e) => e.target.value && set(field, shortDate(e.target.value))}
          />
          <button
            type="button"
            className="input-icon-btn"
            aria-label="Pick a date from a calendar"
            onClick={() => (dateRef.current?.showPicker ? dateRef.current.showPicker() : dateRef.current?.focus())}
          >
            <FontAwesomeIcon icon={faCalendarDays} />
          </button>
        </div>
      )
    }
    if (field === 'location') {
      return (
        <div className="input-icon">
          <input {...common} type="text" maxLength={190} />
          <button type="button" className="input-icon-btn" aria-label="Pick the location on a map" onClick={() => setMapOpen(true)}>
            <FontAwesomeIcon icon={faLocationDot} />
          </button>
        </div>
      )
    }
    if (field === 'message') return <textarea {...common} rows={4} maxLength={5000} />
    return (
      <input
        {...common}
        type={field === 'email' ? 'email' : field === 'phone' ? 'tel' : 'text'}
        inputMode={field === 'phone' ? 'numeric' : undefined}
        autoComplete={field === 'phone' ? 'tel-national' : undefined}
        maxLength={190}
      />
    )
  }

  const renderField = (item) => (
    <div className="form-field" key={item.id}>
      <label htmlFor={`enquiry-${item.fields.field}`}>
        {item.fields.label}
        {item.fields.required && <span className="req" aria-hidden="true"> *</span>}
        {item.fields.help && <span className="form-help"> ({item.fields.help})</span>}
      </label>
      {item.fields.hint && <p className="form-hint">{item.fields.hint}</p>}
      {control(item)}
      {errors[item.fields.field] && <p className="field-error" id={`enquiry-${item.fields.field}-error`} role="alert">{errors[item.fields.field]}</p>}
    </div>
  )

  const left = items.filter((i) => i.fields.column !== 'right')
  const right = items.filter((i) => i.fields.column === 'right')
  const link = f.privacy_link

  return (
    <section className={`section section-soft${bgProps(f).className}`} style={bgProps(f).style} id={`section-${section.id}`}>
      <div className="container">
        <header className="section-head is-center">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} center />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
          <Paragraphs value={f.intro} className="section-intro" />
        </header>

        {state.status === 'sent' ? (
          <div className="form-success" role="status">
            <span className="check"><FontAwesomeIcon icon={siteIcons.check} aria-hidden="true" /></span>
            <Paragraphs value={f.success || 'Thank you. We have received your enquiry.'} />
            <button type="button" className="btn btn-outline-dark form-another" onClick={another}>
              <span>{f.another_label || 'Send another enquiry'}</span>
            </button>
          </div>
        ) : (
          <form className="enquiry-form" onSubmit={submit} noValidate>
            <div className="form-columns">
              <div>{left.map(renderField)}</div>
              <div>
                {right.map(renderField)}
                <label className="form-consent">
                  <input id="enquiry-consent" type="checkbox" checked={consent} aria-invalid={errors.consent ? 'true' : undefined} onChange={(e) => { setConsent(e.target.checked); clearError('consent') }} />
                  <span>
                    {f.consent_before}
                    {f.consent_before && link ? ' ' : ''}
                    {link && (link.href
                      ? (link.href.startsWith('/') ? <Link to={link.href}>{link.label}</Link> : <a href={link.href} target="_blank" rel="noopener noreferrer">{link.label}</a>)
                      : link.label)}
                    {f.consent_after && (link || f.consent_before) ? ' ' : ''}
                    {f.consent_after} <span className="req" aria-hidden="true">*</span>
                  </span>
                </label>
                {errors.consent && <p className="field-error" role="alert">{errors.consent}</p>}
              </div>
            </div>

            <div className="visually-hidden" aria-hidden="true">
              <label>Leave this empty<input tabIndex={-1} autoComplete="off" value={trap} onChange={(e) => setTrap(e.target.value)} /></label>
            </div>

            {mapOpen && (
              <Suspense fallback={null}>
                <LocationPicker
                  initial={values.location}
                  onClose={() => setMapOpen(false)}
                  onPick={(text) => {
                    set('location', text)
                    setMapOpen(false)
                  }}
                />
              </Suspense>
            )}
            {state.error && <p className="form-error" role="alert">{state.error}</p>}
            <div className="form-submit">
              <button type="submit" className="btn btn-primary" disabled={state.status === 'sending'}>
                <Icon name={f.submit?.icon} aria-hidden="true" />
                <span>{state.status === 'sending' ? 'Sending…' : f.submit?.label || 'Send'}</span>
              </button>
            </div>
          </form>
        )}
      </div>
    </section>
  )
}
