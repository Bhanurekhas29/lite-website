import { createContext, useContext, useEffect, useState } from 'react'
import { getJson } from './api.js'

const SiteContext = createContext(null)

// Loads site-wide information (brand, contact, menus) once for the whole website.
export function SiteProvider({ children }) {
  const [state, setState] = useState({ site: null, error: false })

  useEffect(() => {
    getJson('/site')
      .then((site) => setState({ site, error: false }))
      .catch(() => setState({ site: null, error: true }))
  }, [])

  return <SiteContext.Provider value={state}>{children}</SiteContext.Provider>
}

export function useSite() {
  return useContext(SiteContext)
}
