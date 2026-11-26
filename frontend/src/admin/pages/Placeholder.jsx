export default function Placeholder({ title, note }) {
  return (
    <>
      <div className="adm-pagehead">
        <h1>{title}</h1>
      </div>
      <div className="adm-card">
        <p className="adm-muted">{note}</p>
        <p className="adm-hint">This screen is a placeholder. It will be built later.</p>
      </div>
    </>
  )
}
