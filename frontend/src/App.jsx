import { lazy, Suspense } from 'react'
import { Route, Routes } from 'react-router-dom'
import SiteRoutes from './site/SiteRoutes.jsx'

// The admin panel is loaded only when someone opens /admin,
// so public visitors never download it.
const AdminRoutes = lazy(() => import('./admin/AdminRoutes.jsx'))

export default function App() {
  return (
    <Suspense fallback={null}>
      <Routes>
        <Route path="/admin/*" element={<AdminRoutes />} />
        <Route path="/*" element={<SiteRoutes />} />
      </Routes>
    </Suspense>
  )
}
