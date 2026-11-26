import { faArrowUpRightFromSquare, faEnvelope } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { Link } from 'react-router-dom'
import { useLayoutData } from '../layoutData.js'

export default function Dashboard() {
  const { overview } = useLayoutData()
  const stats = [
    ['Pages', overview?.pages],
    ['New enquiries', overview?.new_enquiries],
    ['Media files', overview?.media],
    ['Admins', overview?.admins],
  ]

  return (
    <>
      <div className="adm-pagehead">
        <h1>Overview</h1>
      </div>

      <div className="adm-stats">
        {stats.map(([label, value]) => (
          <div className="adm-stat" key={label}>
            <b>{value ?? '–'}</b>
            {label}
          </div>
        ))}
      </div>

      <div className="adm-card">
        <h2 className="adm-card-title">Quick links</h2>
        <div className="adm-actions">
          <a className="adm-btn adm-btn-outline" href="/" target="_blank" rel="noopener noreferrer">
            <FontAwesomeIcon icon={faArrowUpRightFromSquare} /> View website
          </a>
          <Link className="adm-btn adm-btn-outline" to="/admin/enquiries">
            <FontAwesomeIcon icon={faEnvelope} /> View enquiries
          </Link>
        </div>
      </div>
    </>
  )
}
