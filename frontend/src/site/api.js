// Read-only calls to the public API (no sign-in needed).
export async function getJson(path) {
  const response = await fetch('/api' + path)
  if (!response.ok) {
    const error = new Error('Request failed')
    error.status = response.status
    throw error
  }
  return response.json()
}
