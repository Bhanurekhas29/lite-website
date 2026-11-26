import { useCallback, useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { api } from '../api.js'
import PageSettings from '../components/PageSettings.jsx'
import SectionCard from '../components/SectionCard.jsx'
import { useLayoutData } from '../layoutData.js'

// Edits one website page: its settings and every section on it.
export default function PageEditor() {
  const { slug } = useParams()
  const navigate = useNavigate()
  const { reload } = useLayoutData()
  const [data, setData] = useState(null)
  const [error, setError] = useState('')
  const [notice, setNotice] = useState('')

  const load = useCallback(() => {
    setError('')
    return api(`/admin/pages/${slug}`)
      .then(setData)
      .catch((e) => setError(e.message))
  }, [slug])

  useEffect(() => {
    setData(null)
    load()
  }, [load])

  if (error) {
    return (
      <div className="adm-card">
        <p className="adm-error">{error}</p>
        <button type="button" className="adm-btn adm-btn-outline" onClick={() => navigate('/admin')}>Back to overview</button>
      </div>
    )
  }
  if (!data) return <p className="adm-muted">Loading…</p>

  const { page, sections, types } = data

  function replaceSection(updated) {
    setData((d) => ({ ...d, sections: d.sections.map((s) => (s.id === updated.id ? updated : s)) }))
  }

  async function toggleVisibility(section, visible) {
    setNotice('')
    replaceSection({ ...section, is_visible: visible })
    try {
      await api(`/admin/sections/${section.id}/visibility`, { method: 'PUT', body: { is_visible: visible } })
    } catch (e) {
      replaceSection(section)
      setNotice(e.message)
    }
  }

  async function move(index, step) {
    const next = [...sections]
    const target = index + step
    if (target < 0 || target >= next.length) return
    next.splice(target, 0, next.splice(index, 1)[0])
    setNotice('')
    setData((d) => ({ ...d, sections: next }))
    try {
      await api(`/admin/pages/${page.slug}/order`, { method: 'PUT', body: { ids: next.map((s) => s.id) } })
    } catch (e) {
      setNotice(e.message)
      load()
    }
  }

  function pageSaved(updated) {
    reload()
    if (updated.slug !== slug) {
      navigate(`/admin/pages/${updated.slug}`, { replace: true })
    } else {
      setData((d) => ({ ...d, page: updated }))
    }
  }

  return (
    <>
      <div className="adm-pagehead">
        <div>
          <h1>{page.title}</h1>
          <div className="adm-muted">Address: /{page.slug === 'home' ? '' : page.slug} · {sections.length} sections</div>
        </div>
      </div>

      <PageSettings key={page.slug + JSON.stringify(page)} page={page} onSaved={pageSaved} />

      <h2 className="adm-subhead">Sections</h2>
      <p className="adm-hint">Use the arrows to reorder. The switch hides a section without deleting its content.</p>
      {notice && <p className="adm-error" role="alert">{notice}</p>}

      {sections.map((section, i) => (
        <SectionCard
          key={section.id}
          section={section}
          type={types[section.type]}
          index={i}
          count={sections.length}
          onSaved={replaceSection}
          onVisibility={(v) => toggleVisibility(section, v)}
          onMove={(step) => move(i, step)}
        />
      ))}
      {!sections.length && (
        <div className="adm-card">
          <p className="adm-muted">This page has no sections yet. They are added when the page is built.</p>
        </div>
      )}
    </>
  )
}
