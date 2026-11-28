import CardGrid from './CardGrid.jsx'
import ContactInfo from './ContactInfo.jsx'
import Cta from './Cta.jsx'
import EnquiryForm from './EnquiryForm.jsx'
import Faq from './Faq.jsx'
import Hero from './Hero.jsx'
import ImageCards from './ImageCards.jsx'
import ImageText from './ImageText.jsx'
import ProjectCards from './ProjectCards.jsx'
import Stats from './Stats.jsx'
import Steps from './Steps.jsx'
import Video from './Video.jsx'

// Section type (set in the admin schema) -> the component that draws it.
const components = {
  hero: Hero,
  stats: Stats,
  card_grid: CardGrid,
  image_cards: ImageCards,
  image_text: ImageText,
  steps: Steps,
  faq: Faq,
  video: Video,
  project_cards: ProjectCards,
  contact_info: ContactInfo,
  enquiry_form: EnquiryForm,
  cta: Cta,
}

export function renderSection(section) {
  const Component = components[section.type]
  return Component ? <Component key={section.id} section={section} /> : null
}
