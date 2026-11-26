import { faMagnifyingGlass, faXmark } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerRetina from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
import { useEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'

// A pin that matches the default Leaflet one (the bundler needs the image paths spelled out).
const pin = L.icon({
  iconUrl: markerIcon,
  iconRetinaUrl: markerRetina,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
})

const NOMINATIM = 'https://nominatim.openstreetmap.org'
const LONDON = [51.5074, -0.1278]

// Turn the long OpenStreetMap address into a shorter one a person would write.
function shorten(place) {
  const a = place.address || {}
  const parts = [
    a.amenity || a.building || a.tourism || a.leisure || a.shop || a.house_name,
    [a.house_number, a.road].filter(Boolean).join(' ') || a.road,
    a.suburb || a.neighbourhood,
    a.city || a.town || a.village || a.county,
    a.postcode,
  ].filter(Boolean)
  const text = [...new Set(parts)].join(', ')
  return text || place.display_name || ''
}

// A pop-up map: search for a place or click the map. Nothing is saved until the visitor confirms.
export default function LocationPicker({ initial, onPick, onClose }) {
  const mapEl = useRef(null)
  const map = useRef(null)
  const marker = useRef(null)
  const [query, setQuery] = useState(initial || '')
  const [results, setResults] = useState([])
  const [chosen, setChosen] = useState(null) // { text, lat, lng }
  const [busy, setBusy] = useState(false)
  const [note, setNote] = useState('')

  const place = (lat, lng, text) => {
    setChosen({ text, lat, lng })
    if (!marker.current) marker.current = L.marker([lat, lng], { icon: pin }).addTo(map.current)
    else marker.current.setLatLng([lat, lng])
  }

  async function reverse(lat, lng) {
    setBusy(true)
    setNote('')
    try {
      const r = await fetch(`${NOMINATIM}/reverse?format=jsonv2&addressdetails=1&accept-language=en&lat=${lat}&lon=${lng}`)
      const data = await r.json()
      place(lat, lng, shorten(data) || `${lat.toFixed(5)}, ${lng.toFixed(5)}`)
    } catch {
      place(lat, lng, `${lat.toFixed(5)}, ${lng.toFixed(5)}`)
      setNote('Could not look up the address. The map position will be used.')
    } finally {
      setBusy(false)
    }
  }

  useEffect(() => {
    const m = L.map(mapEl.current, { scrollWheelZoom: true }).setView(LONDON, 11)
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors',
    }).addTo(m)
    m.on('click', (e) => reverse(e.latlng.lat, e.latlng.lng))
    map.current = m
    setTimeout(() => m.invalidateSize(), 50)
    const onKey = (e) => e.key === 'Escape' && onClose()
    window.addEventListener('keydown', onKey)
    return () => {
      window.removeEventListener('keydown', onKey)
      m.remove()
      map.current = null
      marker.current = null
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  async function search(event) {
    event.preventDefault()
    event.stopPropagation() // React sends events from a portal up to the enquiry form too
    const q = query.trim()
    if (q.length < 3) {
      setNote('Type at least 3 letters to search.')
      return
    }
    setBusy(true)
    setNote('')
    try {
      const r = await fetch(`${NOMINATIM}/search?format=jsonv2&addressdetails=1&limit=5&accept-language=en&q=${encodeURIComponent(q)}`)
      const data = await r.json()
      setResults(data)
      if (!data.length) setNote('No places found. Try a postcode or a nearby town.')
    } catch {
      setNote('Search is not available right now. You can still click on the map.')
    } finally {
      setBusy(false)
    }
  }

  function choose(item) {
    const lat = Number(item.lat)
    const lng = Number(item.lon)
    place(lat, lng, shorten(item))
    map.current.setView([lat, lng], 16)
    setResults([])
  }

  // drawn outside the enquiry form, so its search box is never part of that form
  return createPortal(
    <div className="map-overlay" role="dialog" aria-modal="true" aria-label="Pick the event location on a map" onMouseDown={(e) => e.target === e.currentTarget && onClose()}>
      <div className="map-dialog">
        <header className="map-head">
          <h3>Pick the event location</h3>
          <button type="button" className="map-close" onClick={onClose} aria-label="Close map"><FontAwesomeIcon icon={faXmark} /></button>
        </header>
        <form className="map-search" onSubmit={search}>
          <input type="search" value={query} onChange={(e) => setQuery(e.target.value)} placeholder="Search a venue, address or postcode" aria-label="Search for a place" />
          <button type="submit" className="btn btn-primary" disabled={busy}><FontAwesomeIcon icon={faMagnifyingGlass} /> Search</button>
        </form>
        {results.length > 0 && (
          <ul className="map-results">
            {results.map((r) => (
              <li key={r.place_id}><button type="button" onClick={() => choose(r)}>{r.display_name}</button></li>
            ))}
          </ul>
        )}
        <div className="map-canvas" ref={mapEl} />
        <p className="map-hint">Search above, or click anywhere on the map to drop a pin.</p>
        {note && <p className="map-note" role="status">{note}</p>}
        <footer className="map-foot">
          <p className="map-chosen">{chosen ? chosen.text : 'No place chosen yet'}</p>
          <button type="button" className="btn btn-outline-dark" onClick={onClose}>Cancel</button>
          <button type="button" className="btn btn-primary" disabled={!chosen} onClick={() => onPick(chosen.text)}>Use this location</button>
        </footer>
      </div>
    </div>,
    document.body,
  )
}
