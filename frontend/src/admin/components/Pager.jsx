// Page numbers with "Previous" and "Next". Long lists show the first, last and nearby pages.
function pageList(page, pages) {
  if (pages <= 7) return Array.from({ length: pages }, (_, i) => i + 1)
  const list = new Set([1, 2, pages - 1, pages, page - 1, page, page + 1])
  const sorted = [...list].filter((n) => n >= 1 && n <= pages).sort((a, b) => a - b)
  const out = []
  sorted.forEach((n, i) => {
    if (i > 0 && n - sorted[i - 1] > 1) out.push('gap')
    out.push(n)
  })
  return out
}

export default function Pager({ page, pages, total, noun = 'items', onPage }) {
  if (pages <= 1) return <div className="adm-pager"><span className="adm-muted">{total} {total === 1 ? noun.replace(/s$/, '') : noun}</span></div>
  return (
    <nav className="adm-pager" aria-label="Pages">
      <span className="adm-muted adm-pager-total">{total} {noun}</span>
      <div className="adm-pages">
        <button type="button" className="adm-page-btn" disabled={page <= 1} onClick={() => onPage(page - 1)}>Previous</button>
        {pageList(page, pages).map((n, i) =>
          n === 'gap' ? (
            <span className="adm-page-gap" key={`g${i}`} aria-hidden="true">…</span>
          ) : (
            <button
              type="button"
              key={n}
              className={`adm-page-btn${n === page ? ' is-current' : ''}`}
              aria-current={n === page ? 'page' : undefined}
              onClick={() => onPage(n)}
            >
              {n}
            </button>
          ),
        )}
        <button type="button" className="adm-page-btn" disabled={page >= pages} onClick={() => onPage(page + 1)}>Next</button>
      </div>
    </nav>
  )
}
