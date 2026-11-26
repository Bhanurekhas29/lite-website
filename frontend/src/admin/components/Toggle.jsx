// On/off switch. Used for visibility and yes/no options.
export default function Toggle({ checked, onChange, label, disabled }) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      aria-label={label}
      className={`adm-toggle${checked ? ' is-on' : ''}`}
      onClick={() => onChange(!checked)}
      disabled={disabled}
    />
  )
}
