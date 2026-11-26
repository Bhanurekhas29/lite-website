import { Navigate, Outlet, Route, Routes } from 'react-router-dom'
import './admin.css'
import { AuthProvider, useAuth } from './AuthContext.jsx'
import AdminLayout from './components/AdminLayout.jsx'
import Dashboard from './pages/Dashboard.jsx'
import Enquiries from './pages/Enquiries.jsx'
import EnquiryDetail from './pages/EnquiryDetail.jsx'
import LoginPage from './pages/LoginPage.jsx'
import Media from './pages/Media.jsx'
import PageEditor from './pages/PageEditor.jsx'
import Placeholder from './pages/Placeholder.jsx'
import ProjectEditor from './pages/ProjectEditor.jsx'
import Projects from './pages/Projects.jsx'
import SettingsScreen from './pages/SettingsScreen.jsx'

function RequireAuth() {
  const { user } = useAuth()
  if (user === undefined) return <div className="adm-loading">Loading…</div>
  if (user === null) return <Navigate to="/admin/login" replace />
  return <Outlet />
}

// Admin panel routes (the app mounts this at /admin).
export default function AdminRoutes() {
  return (
    <AuthProvider>
      <div className="adm">
        <Routes>
          <Route path="login" element={<LoginPage />} />
          <Route path="reset-password" element={<LoginPage />} />
          <Route element={<RequireAuth />}>
            <Route element={<AdminLayout />}>
              <Route index element={<Dashboard />} />
              <Route path="enquiries" element={<Enquiries />} />
              <Route path="enquiries/:id" element={<EnquiryDetail />} />
              <Route path="pages/:slug" element={<PageEditor />} />
              <Route path="projects" element={<Projects />} />
              <Route path="projects/new" element={<ProjectEditor />} />
              <Route path="projects/:id" element={<ProjectEditor />} />
              <Route path="media" element={<Media />} />
              <Route path="settings/brand" element={<SettingsScreen screen="brand" />} />
              <Route path="settings/contact" element={<SettingsScreen screen="contact" />} />
              <Route path="settings/seo" element={<SettingsScreen screen="seo" />} />
              <Route path="settings/email" element={<SettingsScreen screen="email" />} />
              <Route path="users" element={<Placeholder title="Users" note="Super admin and other admins." />} />
              <Route path="permissions" element={<Placeholder title="Permissions" note="Per-admin access. To be defined with you." />} />
              <Route path="profile" element={<Placeholder title="Profile settings" note="Name, email, photo and password." />} />
            </Route>
          </Route>
          <Route path="*" element={<Navigate to="/admin" replace />} />
        </Routes>
      </div>
    </AuthProvider>
  )
}
