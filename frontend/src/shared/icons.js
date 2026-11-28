import {
  faArrowRight, faBriefcase, faBullseye, faBuilding, faBus, faCakeCandles, faCalendarCheck,
  faCalendarDays, faCamera, faChampagneGlasses, faChevronRight, faCircleCheck, faClipboardList,
  faClock, faCrown, faDrum, faEnvelope, faFileContract, faFlag, faGem, faGift, faGuitar,
  faHandshake, faHandshakeAngle, faHeadset, faHeart, faHotel, faLandmark, faLayerGroup,
  faLightbulb, faListCheck, faLocationDot, faMagnifyingGlass, faMapLocationDot, faMasksTheater,
  faMicrophone, faMusic, faPaperPlane, faPeopleArrows, faPeopleGroup, faPhone, faPlane, faRing,
  faShieldHalved, faSitemap, faSliders, faStar, faTicket, faTrophy, faTruck, faUserGear,
  faUserTie, faUsers, faUtensils, faVideo, faWandMagicSparkles,
  faCheck, faComments, faFileLines, faGear, faGlobe, faHandHoldingHeart, faPlay,
  faCalendarDay, faUser, faBuildingColumns, faShareNodes,
} from '@fortawesome/free-solid-svg-icons'
import { faInstagram } from '@fortawesome/free-brands-svg-icons'

// Icons used by the site itself (not offered in the admin icon picker).
export const siteIcons = { play: faPlay, check: faCheck, globe: faGlobe, instagram: faInstagram, envelope: faEnvelope, phone: faPhone, calendar: faCalendarDay, location: faLocationDot, user: faUser, share: faShareNodes }

// Icons the admin can choose from. The database stores the name (the key).
// To offer another icon, import it above and add it here.
export const icons = {
  'arrow-right': faArrowRight,
  'chevron-right': faChevronRight,
  'paper-plane': faPaperPlane,
  music: faMusic,
  microphone: faMicrophone,
  guitar: faGuitar,
  drum: faDrum,
  ticket: faTicket,
  'masks-theater': faMasksTheater,
  ring: faRing,
  heart: faHeart,
  'cake-candles': faCakeCandles,
  'champagne-glasses': faChampagneGlasses,
  gift: faGift,
  gem: faGem,
  crown: faCrown,
  briefcase: faBriefcase,
  building: faBuilding,
  hotel: faHotel,
  landmark: faLandmark,
  trophy: faTrophy,
  flag: faFlag,
  utensils: faUtensils,
  'people-group': faPeopleGroup,
  users: faUsers,
  'user-tie': faUserTie,
  'user-gear': faUserGear,
  'people-arrows': faPeopleArrows,
  handshake: faHandshake,
  'handshake-angle': faHandshakeAngle,
  'calendar-days': faCalendarDays,
  'calendar-check': faCalendarCheck,
  clock: faClock,
  'location-dot': faLocationDot,
  'map-location-dot': faMapLocationDot,
  truck: faTruck,
  bus: faBus,
  plane: faPlane,
  camera: faCamera,
  video: faVideo,
  'layer-group': faLayerGroup,
  sitemap: faSitemap,
  sliders: faSliders,
  'magnifying-glass': faMagnifyingGlass,
  'clipboard-list': faClipboardList,
  'list-check': faListCheck,
  'file-contract': faFileContract,
  'circle-check': faCircleCheck,
  'shield-halved': faShieldHalved,
  lightbulb: faLightbulb,
  bullseye: faBullseye,
  star: faStar,
  'wand-magic-sparkles': faWandMagicSparkles,
  headset: faHeadset,
  envelope: faEnvelope,
  phone: faPhone,
  gear: faGear,
  check: faCheck,
  comments: faComments,
  'file-lines': faFileLines,
  'hand-holding-heart': faHandHoldingHeart,
  'building-columns': faBuildingColumns,
}

export const iconNames = Object.keys(icons)

export function iconLabel(name) {
  return name.replace(/-/g, ' ')
}
