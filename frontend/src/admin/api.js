// Small helper for calling the PHP API. Requests are same-origin
// (Vite forwards /api to the backend in development).
async function send(path, options) {
  const response = await fetch('/api' + path, { credentials: 'same-origin', ...options })

  let data = null
  try {
    data = await response.json()
  } catch {
    // Non-JSON response; handled below.
  }

  if (!response.ok) {
    const error = new Error(data?.error || 'Something went wrong. Try again.')
    error.status = response.status
    throw error
  }
  return data
}

export function api(path, { method = 'GET', body } = {}) {
  return send(path, {
    method,
    headers: body !== undefined ? { 'Content-Type': 'application/json' } : {},
    body: body !== undefined ? JSON.stringify(body) : undefined,
  })
}

// Replace the picture of an existing library image (every page that uses it updates).
export function replaceImage(id, file) {
  const form = new FormData()
  form.append('file', file)
  return send(`/admin/media/${id}/replace`, { method: 'POST', headers: { 'X-Requested-With': 'fetch' }, body: form })
}

// Upload an image to the media library.
export function uploadImage(file) {
  const form = new FormData()
  form.append('file', file)
  return send('/admin/media', { method: 'POST', headers: { 'X-Requested-With': 'fetch' }, body: form })
}

// Download a file the server creates (for example the Excel export) and save it.
export async function downloadFile(path, fileName) {
  const response = await fetch('/api' + path, { credentials: 'same-origin' })
  if (!response.ok) {
    let message = 'The download did not work. Try again.'
    try {
      message = (await response.json()).error || message
    } catch {
      // not JSON
    }
    throw new Error(message)
  }
  const blob = await response.blob()
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = fileName
  document.body.appendChild(link)
  link.click()
  link.remove()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
