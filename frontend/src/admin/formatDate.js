// Dates come from the API as UTC (ISO 8601). Show them in the time zone chosen in Settings.
export function formatDateTime(iso, timeZone = 'Europe/London') {
  if (!iso) return ''
  return new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZone,
  }).format(new Date(iso))
}

export function formatBytes(bytes) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

// A short, friendly version for lists: the time today, "Yesterday", or the date.
export function formatShort(iso, timeZone = 'Europe/London') {
  if (!iso) return ''
  const day = (d) => new Intl.DateTimeFormat('en-CA', { timeZone }).format(d) // YYYY-MM-DD in that zone
  const date = new Date(iso)
  const now = new Date()
  if (day(date) === day(now)) {
    return new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit', timeZone }).format(date)
  }
  if (day(date) === day(new Date(now.getTime() - 86400000))) return 'Yesterday'
  return new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone }).format(date)
}
