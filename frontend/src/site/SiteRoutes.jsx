import { Navigate, Route, Routes, useParams } from 'react-router-dom'
import './site.css'
import Layout from './components/Layout.jsx'
import PageView from './pages/PageView.jsx'
import { SiteProvider } from './SiteContext.jsx'

function SlugPage() {
  const { slug } = useParams()
  // The Home page lives at "/", not "/home".
  if (slug === 'home') return <Navigate to="/" replace />
  return <PageView key={slug} slug={slug} />
}

// Public website routes. Every page is loaded from the admin-managed content.
export default function SiteRoutes() {
  return (
    <SiteProvider>
      <Routes>
        <Route element={<Layout />}>
          <Route index element={<PageView slug="home" />} />
          <Route path=":slug" element={<SlugPage />} />
        </Route>
      </Routes>
    </SiteProvider>
  )
}
