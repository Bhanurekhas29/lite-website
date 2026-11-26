import { faArrowLeft, faEnvelope, faEnvelopeOpen, faReply, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { api } from '../api.js'
import ConfirmDialog from '../components/ConfirmDialog.jsx'
import { formatDateTime } from '../formatDate.js'
import { useLayoutData } from '../layoutData.js'

function Detail({ label, children }) {
  if (children === null || children === undefined || children === '') return null
  return (
    <div className="adm-detail">
      <dt>{label}</dt>
      <dd>{children}</dd>
    </div>
  )
}

export default function EnquiryDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const { overview, reload } = useLayoutData()
  const timeZone = overview?.timezone
  const [enquiry, setEnquiry] = useState(null)
  const [error, setError] = useState('')
  const [confirming, setConfirming] = useState(false)
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    let cancelled = false
    setEnquiry(null)
    setError('')
    api(`/admin/enquiries/${id}`)
      .then(async (d) => {
        if (cancelled) return
        setEnquiry(d.enquiry)
        // Opening a new enquiry marks it as read.
        if (d.enquiry.status === 'new') {
          try {
            await api(`/admin/enquiries/${id}`, { method: 'PUT', body: { status: 'read' } })
            if (!cancelled) setEnquiry((e) => e && { ...e, status: 'read' })
            reload()
          } catch {
            // Not critical: it simply stays marked as new.
          }
        }
      })
      .catch((e) => !cancelled && setError(e.message))
    return () => {
      cancelled = true
    }
  }, [id, reload])

  async function toggleStatus() {
    const status = enquiry.status === 'read' ? 'new' : 'read'
    setBusy(true)
    try {
      await api(`/admin/enquiries/${id}`, { method: 'PUT', body: { status } })
      setEnquiry({ ...enquiry, status })
      reload()
    } catch (e) {
      setError(e.message)
    } finally {
      setBusy(false)
    }
  }

  async function remove() {
    setBusy(true)
    try {
      await api(`/admin/enquiries/${id}`, { method: 'DELETE', body: {} })
      reload()
      navigate('/admin/enquiries', { replace: true })
    } catch (e) {
      setConfirming(false)
      setError(e.message)
      setBusy(false)
    }
  }

  const back = (
    <Link to="/admin/enquiries" className="adm-back">
      <FontAwesomeIcon icon={faArrowLeft} /> All enquiries
    </Link>
  )

  if (error && !enquiry) {
    return (
      <>
        {back}
        <div className="adm-card"><p className="adm-error">{error}</p></div>
      </>
    )
  }
  if (!enquiry) return <p className="adm-muted">Loading…</p>

  const subject = encodeURIComponent('Your enquiry to E for Events')

  return (
    <>
      {back}
      <div className="adm-pagehead adm-pagehead-split">
        <div>
          <h1>{enquiry.name}</h1>
          <div className="adm-muted">
            Received {formatDateTime(enquiry.created_at, timeZone)}{' '}
            <span className={`adm-tag${enquiry.status === 'new' ? '' : ' adm-tag-off'}`}>{enquiry.status === 'new' ? 'New' : 'Read'}</span>
          </div>
        </div>
        <div className="adm-actions">
          <a className="adm-btn" href={`mailto:${enquiry.email}?subject=${subject}`}>
            <FontAwesomeIcon icon={faReply} /> Reply by email
          </a>
          <button type="button" className="adm-btn adm-btn-outline" onClick={toggleStatus} disabled={busy}>
            <FontAwesomeIcon icon={enquiry.status === 'read' ? faEnvelope : faEnvelopeOpen} />{' '}
            {enquiry.status === 'read' ? 'Mark as unread' : 'Mark as read'}
          </button>
          <button type="button" className="adm-btn adm-btn-outline adm-btn-danger" onClick={() => setConfirming(true)} disabled={busy}>
            <FontAwesomeIcon icon={faTrash} /> Delete
          </button>
        </div>
      </div>

      {error && <p className="adm-error" role="alert">{error}</p>}

      <div className="adm-card">
        <dl className="adm-details-grid">
          <Detail label="Email"><a href={`mailto:${enquiry.email}`}>{enquiry.email}</a></Detail>
          <Detail label="Telephone">{enquiry.phone && <a href={`tel:${enquiry.phone.replace(/\s+/g, '')}`}>{enquiry.phone}</a>}</Detail>
          <Detail label="Company or organisation">{enquiry.company}</Detail>
          <Detail label="Type of enquiry">{enquiry.enquiry_type}</Detail>
          <Detail label="Event date or timeframe">{enquiry.timeframe}</Detail>
          <Detail label="Event location">{enquiry.event_location}</Detail>
          <Detail label="Approximate attendance">{enquiry.attendance}</Detail>
          <Detail label="Services required">
            {enquiry.services.length > 0 && (
              <span className="adm-chips">
                {enquiry.services.map((s) => <span className="adm-tag" key={s}>{s}</span>)}
              </span>
            )}
          </Detail>
        </dl>

        {enquiry.message && (
          <div className="adm-message">
            <h2 className="adm-card-title">Message</h2>
            <p>{enquiry.message}</p>
          </div>
        )}
      </div>

      <p className="adm-hint">
        {enquiry.consent_given ? `Privacy consent given ${formatDateTime(enquiry.consent_at, timeZone)}.` : 'No privacy consent recorded.'}
        {enquiry.ip_address && ` Sent from ${enquiry.ip_address}.`}
      </p>

      {confirming && (
        <ConfirmDialog
          title="Delete this enquiry?"
          message={`The enquiry from ${enquiry.name} will be permanently deleted. This can't be undone.`}
          confirmLabel="Delete"
          busy={busy}
          onConfirm={remove}
          onCancel={() => setConfirming(false)}
        />
      )}
    </>
  )
}
