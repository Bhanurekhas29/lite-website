import { createContext, useContext } from 'react'

// Data loaded once by the admin layout and shared with its screens.
export const LayoutDataContext = createContext({ pages: [], overview: null, reload: () => {} })

export function useLayoutData() {
  return useContext(LayoutDataContext)
}
