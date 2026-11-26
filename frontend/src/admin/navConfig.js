import {
  faAddressBook,
  faArrowUpRightFromSquare,
  faAt,
  faCalendarCheck,
  faCircleInfo,
  faDiagramProject,
  faEnvelope,
  faGaugeHigh,
  faHeart,
  faHouse,
  faImage,
  faImages,
  faMicrophone,
  faPalette,
  faPhone,
  faMagnifyingGlassChart,
  faTrophy,
  faUsers,
} from '@fortawesome/free-solid-svg-icons'

// Icon for each website page, by slug. Unknown slugs fall back to faHouse.
export const pageIcons = {
  home: faHouse,
  about: faCircleInfo,
  'concert-partnerships': faMicrophone,
  'hindu-tamil-weddings': faHeart,
  'event-solutions': faCalendarCheck,
  'sports-events-hospitality': faTrophy,
  'our-work': faImages,
  contact: faAddressBook,
}

// Sidebar items outside the website pages list.
export const dashboardItems = [
  { label: 'Overview', to: '/admin', icon: faGaugeHigh, end: true },
  { label: 'View website', href: '/', icon: faArrowUpRightFromSquare },
  { label: 'Enquiries', to: '/admin/enquiries', icon: faEnvelope, badge: 'new_enquiries' },
]

export const libraryItems = [
  { label: 'Projects', to: '/admin/projects', icon: faDiagramProject },
  { label: 'Media', to: '/admin/media', icon: faImage },
]

export const settingsItems = [
  { label: 'Brand', to: '/admin/settings/brand', icon: faPalette },
  { label: 'Contact and social', to: '/admin/settings/contact', icon: faPhone },
  { label: 'SEO settings', to: '/admin/settings/seo', icon: faMagnifyingGlassChart },
  { label: 'Email settings', to: '/admin/settings/email', icon: faAt },
]

export const accessItems = [
  { label: 'Users', to: '/admin/users', icon: faUsers },
]
