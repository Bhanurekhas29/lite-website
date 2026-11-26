import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { Link } from 'react-router-dom'
import { icons } from '../../shared/icons.js'

// Small building blocks shared by every section.

export function Icon({ name, ...props }) {
  const definition = icons[name]
  return definition ? <FontAwesomeIcon icon={definition} {...props} /> : null
}

// The small label above a heading: plain, a line on the right, or lines on both sides.
export function Eyebrow({ text, style = 'line', center, light }) {
  if (!text) return null
  return (
    <p className={`eyebrow eyebrow-${style}${center ? ' is-center' : ''}${light ? ' is-light' : ''}`}>
      <span>{text}</span>
    </p>
  )
}

// Heading with an optional pink italic accent, on the same line or a new one.
export function Heading({ text, accent, newLine, as: Tag = 'h2', className = '' }) {
  if (!text && !accent) return null
  return (
    <Tag className={`heading ${className}`}>
      {text}
      {accent && (newLine ? <><br /><em>{accent}</em></> : <>{' '}<em>{accent}</em></>)}
    </Tag>
  )
}

// Class and style for a section with an optional faded background image.
export function bgProps(fields) {
  if (!fields.background) return { className: '', style: undefined }
  return {
    className: ` has-bg veil-${fields.background_strength || 'light'}`,
    style: { '--bg-image': `url(${fields.background.url})` },
  }
}

// A text field with blank lines becomes separate paragraphs.
export function Paragraphs({ value, className = '' }) {
  if (!value) return null
  return value
    .split(/\n+/)
    .filter(Boolean)
    .map((line, i) => <p className={className} key={i}>{line}</p>)
}

// A button from the API ({label, icon, href}). Internal links use the router.
export function SmartButton({ button, variant = 'primary', className = '' }) {
  if (!button) return null
  const classes = `btn btn-${variant} ${className}`
  const content = (
    <>
      <span>{button.label}</span>
      <Icon name={button.icon} aria-hidden="true" />
    </>
  )
  if (button.href.startsWith('/')) {
    return <Link className={classes} to={button.href}>{content}</Link>
  }
  const external = /^https?:/i.test(button.href)
  return (
    <a className={classes} href={button.href} {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : {})}>
      {content}
    </a>
  )
}
