import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { api } from './api.js'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  // undefined = still checking, null = signed out, object = signed in
  const [user, setUser] = useState(undefined)

  useEffect(() => {
    api('/admin/me')
      .then((data) => setUser(data.user))
      .catch(() => setUser(null))
  }, [])

  const login = useCallback(async (identifier, password) => {
    const data = await api('/admin/login', { method: 'POST', body: { identifier, password } })
    setUser(data.user)
  }, [])

  const logout = useCallback(async () => {
    try {
      await api('/admin/logout', { method: 'POST', body: {} })
    } finally {
      setUser(null)
    }
  }, [])

  const value = useMemo(() => ({ user, login, logout }), [user, login, logout])
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  return useContext(AuthContext)
}
