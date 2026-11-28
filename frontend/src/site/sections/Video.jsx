import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useState } from 'react'
import { siteIcons } from '../../shared/icons.js'
import { bgProps, Eyebrow, Heading, Paragraphs } from '../components/ui.jsx'

// Turn a pasted link into something we can safely play: YouTube, Vimeo or a direct MP4/WebM file.
function embedFor(link) {
  try {
    const url = new URL(link)
    const host = url.hostname.replace(/^www\./, '')
    const idOk = (id) => /^[\w-]{6,20}$/.test(id || '')
    let id = null
    if (host === 'youtube.com' || host === 'm.youtube.com') {
      id = url.searchParams.get('v') || (/^\/(embed|shorts)\//.test(url.pathname) ? url.pathname.split('/')[2] : null)
    } else if (host === 'youtu.be') {
      id = url.pathname.slice(1)
    }
    if (idOk(id)) return { type: 'iframe', src: `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0` }
    if (host === 'vimeo.com') {
      const vid = url.pathname.split('/').filter(Boolean)[0]
      if (/^\d+$/.test(vid || '')) return { type: 'iframe', src: `https://player.vimeo.com/video/${vid}?autoplay=1` }
    }
    if (url.protocol === 'https:' && /\.(mp4|webm)$/i.test(url.pathname)) return { type: 'video', src: link }
  } catch {
    // Not a valid link: no video.
  }
  return null
}

export default function Video({ section }) {
  const f = section.fields
  const [playing, setPlaying] = useState(false)
  const embed = f.video_url ? embedFor(f.video_url) : null
  const bg = bgProps(f)
  const newTab = f.open_mode === 'newtab' && /^https?:\/\//i.test(f.video_url || '')

  return (
    <section className={`section section-soft${bg.className}`} style={bg.style} id={`section-${section.id}`}>
      <div className="container">
        <header className="section-head is-center">
          <Eyebrow text={f.eyebrow} style={f.eyebrow_style} center />
          <Heading text={f.heading} accent={f.heading_accent} newLine={f.accent_new_line} />
          <Paragraphs value={f.intro} className="section-intro" />
        </header>

        <div className="video">
          {playing && embed ? (
            embed.type === 'iframe' ? (
              <iframe
                src={embed.src}
                title={f.video_title || f.heading || 'Video'}
                allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                allowFullScreen
                referrerPolicy="strict-origin-when-cross-origin"
              />
            ) : (
              <video src={embed.src} controls autoPlay playsInline />
            )
          ) : (
            <>
              {f.poster && <img className="video-poster" src={f.poster.url} alt={f.poster.alt} loading="lazy" />}
              <div className="video-shade" />
              {f.badge_left && <span className="video-badge video-badge-left"><i className="rec" /> {f.badge_left}</span>}
              {f.badge_right && <span className="video-badge video-badge-right">{f.badge_right}</span>}
              {newTab && (
                <a className="video-open" href={f.video_url} target="_blank" rel="noopener noreferrer" aria-label={f.video_title ? `${f.video_title} (opens in a new tab)` : `Watch the video in a new tab${f.caption ? `: ${f.caption}` : ''}`}>
                  <span className="video-play"><FontAwesomeIcon icon={siteIcons.play} /></span>
                </a>
              )}
              {!newTab && embed && (
                <button type="button" className="video-play" onClick={() => setPlaying(true)} aria-label={f.video_title ? `Play: ${f.video_title}` : `Play video${f.caption ? `: ${f.caption}` : ''}`}>
                  <FontAwesomeIcon icon={siteIcons.play} />
                </button>
              )}
              {(newTab || embed) && f.play_label && <span className="video-label">{f.play_label}</span>}
              {f.caption && <span className="video-caption">{f.caption}</span>}
            </>
          )}
        </div>
      </div>
    </section>
  )
}
