<?php
declare(strict_types=1);
// One-time content updates for the live site: SEO titles and descriptions, new page text, picture
// descriptions, the Home FAQ, project details and the enquiry address. Safe to run on every start:
// it does nothing once applied, and it never overwrites a field that has been edited since.
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/load.php';
$pdo = Database::connect($config['db']);

$patch = json_decode(<<<'PATCH_JSON'
{
 "pages": [
  {
   "slug": "home",
   "old": {
    "seo_title": "Home | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "Event Management Company London | E for Events",
    "seo_description": "E for Events is a London event management company delivering event planning, coordination and production for concerts, weddings, corporate and cultural events."
   }
  },
  {
   "slug": "about",
   "old": {
    "seo_title": "About | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "About E for Events | Our Story & Event Expertise",
    "seo_description": "Discover the story behind E for Events, our approach to event planning and coordination, and the people and partners we work with across London and the UK."
   }
  },
  {
   "slug": "concert-partnerships",
   "old": {
    "seo_title": "Concert Partnerships | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "Concert Partnerships | Concert Management & Production",
    "seo_description": "Concert management and production support in London: concert logistics, artist hospitality, venue liaison and event-day delivery from E for Events."
   }
  },
  {
   "slug": "hindu-tamil-weddings",
   "old": {
    "seo_title": "Hindu & Tamil Weddings | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "Hindu & Tamil Wedding Coordination London | E for Events",
    "seo_description": "Hindu and Tamil wedding coordination in London: venue logistics, suppliers, ceremonies, receptions, production liaison and guest arrangements from E for Events."
   }
  },
  {
   "slug": "event-solutions",
   "old": {
    "seo_title": "Event Solutions | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "Event Solutions & Planning Services | E for Events London",
    "seo_description": "E for Events provides event solutions, planning coordination, logistics, production support and specialist event services across London and the UK."
   }
  },
  {
   "slug": "sports-events-hospitality",
   "old": {
    "seo_title": "Sports Events & Hospitality | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "Sports Event Management & Hospitality London | E for Events",
    "seo_description": "E for Events provides sports event management, hospitality, logistics and coordination for corporate sporting events and sponsor events in London and the UK."
   }
  },
  {
   "slug": "our-work",
   "old": {
    "seo_title": "Our Work | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "Event Management Portfolio & Past Events | E for Events London",
    "seo_description": "Explore E for Events’ event management portfolio, featuring concerts, weddings, corporate, cultural and community events across London and the UK."
   }
  },
  {
   "slug": "contact",
   "old": {
    "seo_title": "Contact | E for Events",
    "seo_description": ""
   },
   "new": {
    "seo_title": "Event Management Services & Enquiries London | E for Events",
    "seo_description": "Contact E for Events to discuss event management, planning, coordination and support in London and the UK. Tell us about your event and the services you need."
   }
  }
 ],
 "fields": [
  {
   "section": 1,
   "item": null,
   "key": "accent_new_line",
   "old": "1",
   "new": "0",
   "kind": "text"
  },
  {
   "section": 1,
   "item": null,
   "key": "background",
   "old": "Arena concert with LED screens, stage lighting and a large crowd",
   "new": "Large concert stage with bright spotlights, LED screens and a cheering crowd",
   "kind": "alt"
  },
  {
   "section": 1,
   "item": null,
   "key": "heading",
   "old": "Your Event Vision. Our Expertise.",
   "new": "Events, Thoughtfully Planned. Beautifully Delivered.",
   "kind": "text"
  },
  {
   "section": 1,
   "item": null,
   "key": "heading_accent",
   "old": "Seamlessly Delivered.",
   "new": "",
   "kind": "text"
  },
  {
   "section": 1,
   "item": null,
   "key": "tagline",
   "old": "",
   "new": "Your Event Vision. Our Expertise.",
   "kind": "text"
  },
  {
   "section": 1,
   "item": null,
   "key": "tagline_accent",
   "old": "",
   "new": "Seamlessly Delivered.",
   "kind": "text"
  },
  {
   "section": 1,
   "item": null,
   "key": "text1",
   "old": "From live music concerts and entertainment events to weddings, corporate functions and cultural celebrations, E for Events helps turn event plans into well-coordinated experiences.",
   "new": "E for Events is an events company in London delivering event management, event planning and event coordination for live music concerts, weddings, corporate functions and cultural celebrations. We turn event plans into well-coordinated experiences.",
   "kind": "text"
  },
  {
   "section": 1,
   "item": null,
   "key": "text2",
   "old": "We work with event organisers, promoters, businesses and private clients across the UK, offering complete event management or specialist support tailored to your requirements.",
   "new": "Behind every successful event is a carefully coordinated plan. From suppliers and venues to schedules, logistics and event-day support, we bring the practical details together so your event can take centre stage.",
   "kind": "text"
  },
  {
   "section": 3,
   "item": null,
   "key": "background",
   "old": "Arena concert with LED screens, stage lighting and a large crowd",
   "new": "Large concert stage with bright spotlights, LED screens and a cheering crowd",
   "kind": "alt"
  },
  {
   "section": 3,
   "item": null,
   "key": "heading",
   "old": "Events Delivered with Care, Coordination and Attention to Detail",
   "new": "Events Delivered with Care, Coordination & Attention to Detail",
   "kind": "text"
  },
  {
   "section": 3,
   "item": null,
   "key": "intro",
   "old": "Every successful event depends on many moving parts coming together at the right time. We help coordinate the people, suppliers, venues and practical arrangements that make an event work.",
   "new": "Every event has its own requirements. From event planning and production to logistics, supplier coordination and on-site support, we bring the right people and moving parts together to keep your event on track.",
   "kind": "text"
  },
  {
   "section": 3,
   "item": {
    "id": 3,
    "title": "Concerts & Live Entertainment"
   },
   "key": "image",
   "old": "Two performers on stage facing a large smiling audience in a theatre hall",
   "new": "Two performers on stage facing a large audience at a live entertainment event",
   "kind": "alt"
  },
  {
   "section": 3,
   "item": {
    "id": 3,
    "title": "Concerts & Live Entertainment"
   },
   "key": "text",
   "old": "Practical support for concert promoters, touring productions and live entertainment organisers, from event planning and logistics to supplier coordination and on-site delivery.",
   "new": "As an event production company, we provide production coordination for concert promoters, touring productions and live entertainment organisers, from event planning and logistics to supplier coordination and on-site delivery.",
   "kind": "text"
  },
  {
   "section": 3,
   "item": {
    "id": 4,
    "title": "Hindu & Tamil Weddings"
   },
   "key": "image",
   "old": "Hindu and Tamil wedding couple holding hands at a flower-covered mandap beside the sacred fire",
   "new": "Hindu wedding ceremony with the couple seated before a sacred fire, surrounded by family",
   "kind": "alt"
  },
  {
   "section": 3,
   "item": {
    "id": 5,
    "title": "Corporate Events & Conferences"
   },
   "key": "text",
   "old": "Coordinated event arrangements for business gatherings, conferences, product launches and professional corporate functions.",
   "new": "From conferences and product launches to corporate functions, we manage the planning, coordination and delivery behind successful business events.",
   "kind": "text"
  },
  {
   "section": 3,
   "item": {
    "id": 6,
    "title": "Sports Events & Hospitality"
   },
   "key": "image",
   "old": "Table of sports trophies and cups",
   "new": "Rows of sports trophies on a table with an England flag behind",
   "kind": "alt"
  },
  {
   "section": 3,
   "item": {
    "id": 6,
    "title": "Sports Events & Hospitality"
   },
   "key": "text",
   "old": "Support with event logistics, guest arrangements and hospitality coordination, tailored to the requirements of each prestigious occasion.",
   "new": "Sports event management and sports hospitality support from an experienced sports event organiser, covering event logistics, guest arrangements and event hospitality coordination tailored to the requirements of each prestigious occasion.",
   "kind": "text"
  },
  {
   "section": 3,
   "item": {
    "id": 7,
    "title": "Cultural & Community Events"
   },
   "key": "image",
   "old": "Collage of a community arts venue exterior, stage and seating",
   "new": "Community arts venue exterior and auditorium with a stage and red seating",
   "kind": "alt"
  },
  {
   "section": 3,
   "item": {
    "id": 7,
    "title": "Cultural & Community Events"
   },
   "key": "text",
   "old": "Helping bring communities together through well-organised cultural programmes, authentic celebrations and social initiatives.",
   "new": "Helping bring communities together through well-organised cultural events, authentic celebrations and community events that carry social purpose.",
   "kind": "text"
  },
  {
   "section": 4,
   "item": null,
   "key": "heading",
   "old": "The Right Support for Your Event",
   "new": "The Right Planning and Support for Your Event",
   "kind": "text"
  },
  {
   "section": 4,
   "item": {
    "id": 8,
    "title": "Full Event Management"
   },
   "key": "image",
   "old": "Collage of stage lighting, a concert poster and empty auditoriums",
   "new": "Collage of concert stages with lighting, a Summer Symphony event stage and theatre seating set up for an event",
   "kind": "alt"
  },
  {
   "section": 4,
   "item": {
    "id": 8,
    "title": "Full Event Management"
   },
   "key": "text",
   "old": "Have an event idea but need someone to coordinate the moving parts? We can discuss your requirements and help organise the planning, suppliers, venue arrangements, logistics and event-day delivery within an agreed scope.",
   "new": "Have an event idea but need someone to coordinate the moving parts? Our event management services cover planning, suppliers, venue arrangements, logistics and event-day delivery within an agreed scope, so your event solutions come together under one coordinated team.",
   "kind": "text"
  },
  {
   "section": 4,
   "item": {
    "id": 9,
    "title": "Specialist Event Support"
   },
   "key": "image",
   "old": "Speaker presenting to guests at the Indian Team Gala Dinner",
   "new": "Speaker with a microphone presenting to guests at a corporate gala dinner in a hotel conference room",
   "kind": "alt"
  },
  {
   "section": 4,
   "item": {
    "id": 9,
    "title": "Specialist Event Support"
   },
   "key": "text",
   "old": "Already have an event team in place? We can discuss supporting specific workstreams, such as artist logistics, accommodation, local transport, venue coordination, hospitality, supplier liaison and production arrangements.",
   "new": "Already have an event team in place? Our event support services can cover specific workstreams, such as artist logistics, accommodation, local transport, venue coordination, hospitality, supplier liaison and production arrangements.",
   "kind": "text"
  },
  {
   "section": 5,
   "item": null,
   "key": "caption",
   "old": "Flawless on-stage timing backed by experienced logistics.",
   "new": "Flawless on-stage timing backed by experienced event logistics.",
   "kind": "text"
  },
  {
   "section": 5,
   "item": null,
   "key": "heading",
   "old": "A Practical Partner Behind Every Successful Event",
   "new": "The Expertise Behind Every Successful Event",
   "kind": "text"
  },
  {
   "section": 5,
   "item": null,
   "key": "image",
   "old": "Theatre with red seating and a band performing on stage",
   "new": "Theatre auditorium with rows of red seating and a lit concert stage",
   "kind": "alt"
  },
  {
   "section": 5,
   "item": {
    "id": 10,
    "title": "Flexible engagement"
   },
   "key": "text",
   "old": "Support for a complete event or specific work packages tailored to scale.",
   "new": "Full event management or targeted support, tailored to your requirements.",
   "kind": "text"
  },
  {
   "section": 5,
   "item": {
    "id": 11,
    "title": "Coordinated delivery"
   },
   "key": "text",
   "old": "Help bringing together suppliers, venues and operational requirements seamlessly.",
   "new": "Bringing suppliers, venues and event operations together seamlessly.",
   "kind": "text"
  },
  {
   "section": 5,
   "item": {
    "id": 12,
    "title": "Tailored solutions"
   },
   "key": "text",
   "old": "An approach shaped around the event, audience, location and unique creative brief.",
   "new": "Support shaped around your event, audience, location and brief.",
   "kind": "text"
  },
  {
   "section": 5,
   "item": {
    "id": 13,
    "title": "Collaborative working"
   },
   "key": "text",
   "old": "The ability to work alongside your existing team and appointed suppliers with high trust.",
   "new": "Working alongside your team and appointed suppliers.",
   "kind": "text"
  },
  {
   "section": 5,
   "item": {
    "id": 14,
    "title": "Attention to detail"
   },
   "key": "text",
   "old": "Careful coordination of the practical arrangements that help events run smoothly.",
   "new": "Careful coordination of the practical details that keep events running smoothly.",
   "kind": "text"
  },
  {
   "section": 6,
   "item": null,
   "key": "intro",
   "old": "Every event has its own requirements, challenges and objectives. Explore examples of our work to understand the types of events we support and the services we provide.",
   "new": "Every event has its own requirements and challenges. Explore examples of our work across concerts, corporate events and cultural celebrations in London and the UK.",
   "kind": "text"
  },
  {
   "section": 7,
   "item": null,
   "key": "background",
   "old": "Theatre with red seating and a band on stage under purple lights",
   "new": "Concert stage with lighting and screens seen from the balcony of a theatre",
   "kind": "alt"
  },
  {
   "section": 7,
   "item": null,
   "key": "text1",
   "old": "Whether you are an established promoter, an event agency preparing for a busy schedule or an organiser looking for additional delivery support, we would welcome a conversation.",
   "new": "Whether you are an established promoter, an event agency preparing for a busy schedule or an event organiser looking for additional delivery support, we would welcome a conversation.",
   "kind": "text"
  },
  {
   "section": 9,
   "item": null,
   "key": "background",
   "old": "Gala dinner in a chandelier ballroom with a large stage screen",
   "new": "Banqueting Hall with chandeliers, floral table settings and guests at a corporate dinner",
   "kind": "alt"
  },
  {
   "section": 9,
   "item": null,
   "key": "text1",
   "old": "E for Events is an event management and coordination business supporting concerts, live entertainment, weddings, corporate functions, sports hospitality and cultural celebrations.",
   "new": "E for Events is a London-based event management company specialising in event planning and coordination for concerts, weddings, corporate functions, sports hospitality and cultural celebrations. We bring people, suppliers and logistics together to deliver memorable events that reflect our clients' objectives.",
   "kind": "text"
  },
  {
   "section": 9,
   "item": null,
   "key": "text2",
   "old": "We believe successful events come from good planning, clear communication and the careful coordination of people, suppliers and logistics. Our role is to help clients organise these moving parts and deliver an event that reflects their objectives.",
   "new": "",
   "kind": "text"
  },
  {
   "section": 10,
   "item": null,
   "key": "background",
   "old": "Gala dinner in a chandelier ballroom with a large stage screen",
   "new": "Banqueting Hall with chandeliers, floral table settings and guests at a corporate dinner",
   "kind": "alt"
  },
  {
   "section": 10,
   "item": null,
   "key": "image_shape",
   "old": "portrait",
   "new": "wide",
   "kind": "text"
  },
  {
   "section": 10,
   "item": null,
   "key": "text2",
   "old": "E for Events was established to help make those experiences happen through practical event planning, coordination and delivery support. Our approach draws on event experience and an understanding of the work that happens behind the scenes — from organising suppliers and venue requirements to coordinating schedules and guest arrangements.",
   "new": "Our Company was established to make every event seamless and memorable through professional event planning and coordination. We manage suppliers, venues, schedules and guest arrangements with attention to every detail. Our goal is to deliver well-organised events that bring our clients' vision to life.",
   "kind": "text"
  },
  {
   "section": 10,
   "item": null,
   "key": "text3",
   "old": "We recognise that not every client needs the same level of support. Some need a partner to oversee an event from beginning to end. Others already have an experienced team but require additional capacity in specific areas.",
   "new": "Every client has different event requirements and support needs. We offer flexible event management services, from end-to-end planning to specialist event support services. Our approach ensures every event receives the right level of expertise and coordination.",
   "kind": "text"
  },
  {
   "section": 10,
   "item": null,
   "key": "text4",
   "old": "We work to understand the brief, define the support required and coordinate the agreed arrangements with care and professionalism.",
   "new": "As an event production company, we work alongside your venues and suppliers so that what is planned comes together on the day.",
   "kind": "text"
  },
  {
   "section": 11,
   "item": null,
   "key": "background",
   "old": "Gala dinner in a chandelier ballroom with a large stage screen",
   "new": "Banqueting Hall with chandeliers, floral table settings and guests at a corporate dinner",
   "kind": "alt"
  },
  {
   "section": 11,
   "item": {
    "id": 16,
    "title": "Understand the brief"
   },
   "key": "text",
   "old": "Every project starts with understanding the event's purpose, audience, timings, location and requirements.",
   "new": "Every project starts with understanding the event's purpose, audience, timings, location and requirements. This shapes our event planning services from the first conversation.",
   "kind": "text"
  },
  {
   "section": 11,
   "item": {
    "id": 17,
    "title": "Coordinate the details"
   },
   "key": "text",
   "old": "We help bring together the people, suppliers and arrangements needed for the agreed scope of work.",
   "new": "Our event coordination services bring together the people, suppliers and arrangements needed for the agreed scope of work, with event logistics organised in one plan.",
   "kind": "text"
  },
  {
   "section": 11,
   "item": {
    "id": 18,
    "title": "Work as one team"
   },
   "key": "text",
   "old": "We collaborate with clients, venues, suppliers and existing event teams to support a coordinated delivery process.",
   "new": "We collaborate with clients, venues, suppliers and existing event teams, keeping event operations coordinated from planning through to delivery.",
   "kind": "text"
  },
  {
   "section": 12,
   "item": null,
   "key": "heading",
   "old": "Supporting Different Types of Event Clients",
   "new": "Working with the People Behind Every Event",
   "kind": "text"
  },
  {
   "section": 12,
   "item": null,
   "key": "intro",
   "old": "Building strong partnerships with the people and businesses who share our vision for quality, innovation, and lasting success.",
   "new": "As an events agency, we build strong partnerships with the people and businesses who share our vision for quality, innovation and lasting success, from concert promoters to event hospitality partners.",
   "kind": "text"
  },
  {
   "section": 13,
   "item": null,
   "key": "text1",
   "old": "Whether you need full event management or support with a specific part of a project, we would be happy to discuss your requirements.",
   "new": "Planning an event? Let’s bring your vision to life! Contact E for Events for expert event management and event planning services in London, from complete event coordination to specialist support. Get in touch today to discuss your event requirements!",
   "kind": "text"
  },
  {
   "section": 14,
   "item": null,
   "key": "background",
   "old": "Rock concert with stage flames and a crowd with hands raised",
   "new": "Rock band performing on a concert stage with a large LED screen, confetti and a packed crowd",
   "kind": "alt"
  },
  {
   "section": 14,
   "item": null,
   "key": "subheading",
   "old": "Concert Logistics, Coordination and Event Delivery Support",
   "new": "Concert Management, Logistics & Event Delivery in London",
   "kind": "text"
  },
  {
   "section": 14,
   "item": null,
   "key": "text2",
   "old": "E for Events works with concert promoters, entertainment organisers and event companies to coordinate the practical requirements of live events. From artist logistics and hospitality to venue liaison and supplier coordination, we can discuss support tailored to your brief.",
   "new": "E for Events works with concert promoters, concert organisers, entertainment organisers and event companies to coordinate the practical requirements of live events across London and the UK. From artist logistics and concert hospitality to venue liaison and supplier coordination, we can discuss support tailored to your production.",
   "kind": "text"
  },
  {
   "section": 14,
   "item": null,
   "key": "text3",
   "old": "Whether you need help managing a wider event or want a partner to take responsibility for specific workstreams, we aim to fit into your delivery model.",
   "new": "Whether you need concert event management across a wider event or want a partner to take responsibility for specific workstreams, we aim to fit into your delivery model.",
   "kind": "text"
  },
  {
   "section": 15,
   "item": null,
   "key": "image",
   "old": "Crowd with hands raised in front of a large concert stage",
   "new": "Festival stage with lighting, flame effects, LED screens and a large cheering crowd",
   "kind": "alt"
  },
  {
   "section": 15,
   "item": null,
   "key": "text2",
   "old": "If you already have a promoter, production manager or event team, E for Events can discuss taking on agreed operational responsibilities to support that team.",
   "new": "If you already have a promoter, production manager or event team, E for Events, a concert production and event coordination partner in London, can discuss taking on agreed operational responsibilities to support that team, from live events management to music event management.",
   "kind": "text"
  },
  {
   "section": 16,
   "item": null,
   "key": "heading",
   "old": "",
   "new": "Behind the Scenes, We Keep Everything Moving",
   "kind": "text"
  },
  {
   "section": 16,
   "item": {
    "id": 25,
    "title": "Artist Logistics & Hospitality"
   },
   "key": "text",
   "old": "Coordination of artist travel arrangements, accommodation, local transport, hospitality and related requirements in line with the agreed brief.",
   "new": "Coordination of artist travel arrangements, accommodation, local transport and concert hospitality, with event hospitality services and artist event management support in line with the agreed brief.",
   "kind": "text"
  },
  {
   "section": 16,
   "item": {
    "id": 26,
    "title": "Venue & Site Coordination"
   },
   "key": "text",
   "old": "Liaison with venues and relevant suppliers on access, schedules, logistical arrangements and practical requirements.",
   "new": "Liaison with venues and relevant suppliers on access, schedules and practical requirements, with event logistics organised around each site.",
   "kind": "text"
  },
  {
   "section": 16,
   "item": {
    "id": 27,
    "title": "Production & Supplier Liaison"
   },
   "key": "text",
   "old": "Coordination with appointed sound, lighting, LED, staging, catering and other specialist providers as required.",
   "new": "Production coordination with appointed sound, lighting, LED, staging, catering and other specialist providers, including audio visual event production and AV event production suppliers as required.",
   "kind": "text"
  },
  {
   "section": 16,
   "item": {
    "id": 28,
    "title": "Event-Day Coordination"
   },
   "key": "text",
   "old": "Support with agreed schedules, supplier communication, logistics and coordination of operational tasks during delivery.",
   "new": "Support with agreed schedules, supplier communication and concert coordination of operational tasks on the day, with event operations and event operations management kept under one plan.",
   "kind": "text"
  },
  {
   "section": 16,
   "item": {
    "id": 29,
    "title": "Project Coordination"
   },
   "key": "text",
   "old": "Help keep agreed tasks, timings, supplier arrangements and communication aligned across the relevant workstreams.",
   "new": "Help keep agreed tasks, timings, supplier arrangements and communication aligned across the relevant workstreams through event coordination and production coordination.",
   "kind": "text"
  },
  {
   "section": 17,
   "item": null,
   "key": "heading",
   "old": "",
   "new": "Support Shaped Around Your Production",
   "kind": "text"
  },
  {
   "section": 17,
   "item": null,
   "key": "intro",
   "old": "",
   "new": "Whether you are comparing special event production teams and companies or looking for one partner to take on part of your event, there are three ways to work with E for Events.",
   "kind": "text"
  },
  {
   "section": 17,
   "item": {
    "id": 30,
    "title": "Full Event Delivery"
   },
   "key": "image",
   "old": "Theatre with red seating and a band performing on stage",
   "new": "Theatre auditorium with rows of red seating and a concert stage lit in purple",
   "kind": "alt"
  },
  {
   "section": 17,
   "item": {
    "id": 30,
    "title": "Full Event Delivery"
   },
   "key": "text",
   "old": "For organisers looking for a partner to coordinate an event across multiple stages, we can discuss the overall brief, scope, responsibilities, suppliers and delivery requirements.",
   "new": "For organisers looking for a partner to coordinate concert event production across multiple stages, we can discuss the overall brief, scope, responsibilities, suppliers and delivery requirements, from live event production to bespoke event production shaped around your event.",
   "kind": "text"
  },
  {
   "section": 17,
   "item": {
    "id": 31,
    "title": "Specialist Work Packages"
   },
   "key": "image",
   "old": "Collage of musical instruments and drums under magenta lighting",
   "new": "Collage of keyboards, tabla drums and stage equipment set up for a live concert",
   "kind": "alt"
  },
  {
   "section": 17,
   "item": {
    "id": 31,
    "title": "Specialist Work Packages"
   },
   "key": "text",
   "old": "For promoters and event agencies with an existing team, we can discuss individual work packages such as artist transport, accommodation, venue liaison, hospitality or selected supplier coordination.",
   "new": "For promoters and event agencies with an existing team, our event management support covers individual requirements such as artist transport, accommodation, venue liaison, hospitality or selected supplier coordination, packaged as concert production services or event production services to suit your scope.",
   "kind": "text"
  },
  {
   "section": 17,
   "item": {
    "id": 32,
    "title": "UK Delivery Support"
   },
   "key": "image",
   "old": "Collage of stage lighting, a concert poster and empty auditoriums",
   "new": "Collage of concert stages, a Summer Symphony event screen and a theatre auditorium with red seating",
   "kind": "alt"
  },
  {
   "section": 17,
   "item": {
    "id": 32,
    "title": "UK Delivery Support"
   },
   "key": "text",
   "old": "For international promoters or organisers planning an event in the UK, we can discuss local coordination requirements, including venue and supplier liaison, transport coordination and other agreed logistical needs.",
   "new": "For international promoters or organisers planning an event in the UK, we can discuss local coordination requirements, including venue and supplier liaison, transport arrangements and other agreed logistical needs, as a local concert production and coordination partner.",
   "kind": "text"
  },
  {
   "section": 18,
   "item": null,
   "key": "background",
   "old": "Rock concert with stage flames and a crowd with hands raised",
   "new": "Rock band performing on a concert stage with a large LED screen, confetti and a packed crowd",
   "kind": "alt"
  },
  {
   "section": 18,
   "item": {
    "id": 33,
    "title": "Concert promoters and touring organisers."
   },
   "key": "title",
   "old": "Concert promoters and touring organisers.",
   "new": "Concert promoters, concert organisers and touring organisers.",
   "kind": "text"
  },
  {
   "section": 18,
   "item": {
    "id": 35,
    "title": "Event management and production companies."
   },
   "key": "title",
   "old": "Event management and production companies.",
   "new": "Event management and production companies, including concert management companies and live event production companies.",
   "kind": "text"
  },
  {
   "section": 19,
   "item": null,
   "key": "heading",
   "old": "",
   "new": "From First Conversation to Event Day",
   "kind": "text"
  },
  {
   "section": 19,
   "item": {
    "id": 40,
    "title": "Define the scope."
   },
   "key": "text",
   "old": "We discuss the tasks, responsibilities, timings and any external suppliers involved.",
   "new": "We discuss the tasks, responsibilities, timings and any external suppliers involved, from concert planning to the event operations and planning needed on the day.",
   "kind": "text"
  },
  {
   "section": 19,
   "item": {
    "id": 41,
    "title": "Agree the delivery plan."
   },
   "key": "text",
   "old": "The proposed services, costs, responsibilities and arrangements are confirmed before work begins.",
   "new": "The proposed services, costs, responsibilities and arrangements are confirmed before work begins, including concert logistics planning and the role of any concert event planner already on your team.",
   "kind": "text"
  },
  {
   "section": 20,
   "item": null,
   "key": "background",
   "old": "Softly faded view of a seated audience",
   "new": "Audience applauding at a live community concert",
   "kind": "alt"
  },
  {
   "section": 20,
   "item": null,
   "key": "text1",
   "old": "Tell us about your next concert or live event. We would welcome the opportunity to understand your requirements and explore how E for Events could support your team.",
   "new": "Tell us about your next concert or live event. We would welcome the opportunity to understand your requirements and explore how E for Events could support your team with concert management and event management support across London and the UK.",
   "kind": "text"
  },
  {
   "section": 21,
   "item": null,
   "key": "image",
   "old": "Hindu and Tamil wedding couple holding hands at a flower-covered mandap beside the sacred fire",
   "new": "Bride and groom seated hand in hand before a sacred fire under a floral mandap at a Hindu wedding",
   "kind": "alt"
  },
  {
   "section": 21,
   "item": null,
   "key": "text1",
   "old": "A wedding brings together family, traditions, rituals, suppliers and countless details. E for Events helps coordinate the practical arrangements that allow couples and families to focus on the celebration.",
   "new": "A Hindu wedding or Tamil wedding brings together family, traditions, rituals, suppliers and countless details. E for Events provides wedding coordination in London, helping coordinate the practical arrangements that allow couples and families to focus on the celebration.",
   "kind": "text"
  },
  {
   "section": 21,
   "item": null,
   "key": "text2",
   "old": "From venue and supplier coordination to event schedules, production liaison and guest arrangements, we can discuss support tailored to your wedding plans.",
   "new": "From venue and supplier coordination to event schedules, production liaison and guest arrangements, our wedding management support can be tailored to your wedding plans, whether you are looking for a wedding organiser for the whole celebration or help with selected arrangements.",
   "kind": "text"
  },
  {
   "section": 22,
   "item": null,
   "key": "intro",
   "old": "Experience the beauty, traditions and unforgettable moments of Asian & Indian weddings, beautifully planned and seamlessly orchestrated by E for Events.",
   "new": "Experience the beauty, traditions and unforgettable moments of Asian and Indian weddings, beautifully planned and seamlessly coordinated by E for Events, an Asian wedding planner serving London and the UK.",
   "kind": "text"
  },
  {
   "section": 22,
   "item": null,
   "key": "poster",
   "old": "Wedding table with fruit and traditional decorations",
   "new": "Wedding stage with red drapes, decorated ceremony items, flowers and a deity figure",
   "kind": "alt"
  },
  {
   "section": 22,
   "item": null,
   "key": "video_title",
   "old": "",
   "new": "Every Wedding Has a Story: E for Events Cinematic Wedding Film",
   "kind": "text"
  },
  {
   "section": 23,
   "item": null,
   "key": "heading",
   "old": "Wedding Services Designed Around",
   "new": "Wedding Support Designed Around",
   "kind": "text"
  },
  {
   "section": 23,
   "item": null,
   "key": "intro",
   "old": "We provide end-to-end support for Hindu and Tamil weddings, with a focus on coordination, communication and attention to detail. Our services can be tailored to your specific requirements and the level of support you need.",
   "new": "We provide end-to-end wedding planning and coordination for Hindu and Tamil weddings, with a focus on coordination, communication and attention to detail. Our wedding services can be tailored to your specific requirements and the level of support you need.",
   "kind": "text"
  },
  {
   "section": 23,
   "item": {
    "id": 43,
    "title": "Wedding Planning & Coordination"
   },
   "key": "text",
   "old": "Help coordinating the event requirements, schedules, suppliers and arrangements within the agreed scope.",
   "new": "Help coordinating the event requirements, schedules, suppliers and arrangements within the agreed scope, with an experienced wedding coordinator supporting you from the first conversation.",
   "kind": "text"
  },
  {
   "section": 23,
   "item": {
    "id": 44,
    "title": "Venue & Logistics"
   },
   "key": "text",
   "old": "Liaison on venue arrangements, access, timings, layouts and practical requirements.",
   "new": "Liaison with your chosen wedding reception venue on arrangements, access, timings, layouts and practical requirements.",
   "kind": "text"
  },
  {
   "section": 23,
   "item": {
    "id": 45,
    "title": "Supplier Coordination"
   },
   "key": "text",
   "old": "Coordination with appointed decorators, caterers, photographers, videographers, entertainment providers and other suppliers.",
   "new": "Coordination with appointed wedding decorator teams, wedding florists, wedding catering providers, photographers, videographers, entertainment providers and other suppliers.",
   "kind": "text"
  },
  {
   "section": 23,
   "item": {
    "id": 46,
    "title": "Ceremony & Reception Coordination"
   },
   "key": "text",
   "old": "Support with the running order, timings and coordination of agreed arrangements for ceremonies and receptions.",
   "new": "Support from a wedding event coordinator with the running order, timings and coordination of agreed arrangements for ceremonies and receptions.",
   "kind": "text"
  },
  {
   "section": 23,
   "item": {
    "id": 48,
    "title": "Guest & Event-Day Arrangements"
   },
   "key": "text",
   "old": "Practical coordination of agreed guest requirements, schedules and on-the-day logistics.",
   "new": "Practical coordination of agreed guest requirements, schedules and on-the-day logistics, including day of wedding coordinator and wedding on the day coordinator support, or a wedding planner for the day where the rest of the planning is already done.",
   "kind": "text"
  },
  {
   "section": 24,
   "item": null,
   "key": "image",
   "old": "Brass diya lamp surrounded by flower garlands at a wedding",
   "new": "Brass lamp surrounded by marigold and rose garlands with fairy lights at a wedding ceremony",
   "kind": "alt"
  },
  {
   "section": 24,
   "item": null,
   "key": "text2",
   "old": "Whether you are looking for help with selected arrangements or wider event coordination, our aim is to bring clarity to the planning process and help the agreed details come together smoothly.",
   "new": "Whether you are looking for a wedding planner and coordinator for the whole celebration, a wedding planner and organiser for selected arrangements or wider wedding organisation, our aim is to bring clarity to the planning process and help the agreed details come together smoothly.",
   "kind": "text"
  },
  {
   "section": 24,
   "item": null,
   "key": "text3",
   "old": "",
   "new": "As a Hindu wedding planner and Tamil wedding planner serving London, we plan around your family’s customs, rituals and priorities, with a wedding planning coordinator supporting every stage.",
   "kind": "text"
  },
  {
   "section": 25,
   "item": null,
   "key": "background",
   "old": "Wedding hall with a large floral mandap and chandeliers",
   "new": "Grand wedding mandap decorated with flowers and chandeliers, with guests seated on either side",
   "kind": "alt"
  },
  {
   "section": 25,
   "item": null,
   "key": "text1",
   "old": "Share your date, venue and plans with us. We can discuss your requirements and help you identify the level of coordination that best suits your celebration.",
   "new": "Share your date, venue and plans with us. We can discuss your requirements and help you identify the level of coordination that best suits your celebration, from a wedding event coordinator on the day to a wedding event organizer for the whole event.",
   "kind": "text"
  },
  {
   "section": 26,
   "item": null,
   "key": "background",
   "old": "Ballroom with pink lighting, flower chandeliers and a band playing for dining guests",
   "new": "Grand ballroom with floral chandeliers, pink lighting, a live band and guests at a gala dinner",
   "kind": "alt"
  },
  {
   "section": 26,
   "item": null,
   "key": "heading",
   "old": "Event Services Designed Around",
   "new": "Event Solutions Designed Around",
   "kind": "text"
  },
  {
   "section": 26,
   "item": null,
   "key": "text1",
   "old": "No two events are the same. Some require complete planning and coordination, while others need additional support in a few specialist areas. E for Events offers a flexible approach to event management, logistics and supplier coordination, helping clients identify the support they need for their event.",
   "new": "No two events are the same. Some need an event planning and coordination partner for complete planning and event coordination, while others need additional support in a few specialist areas. E for Events offers event solutions in London, with event planning services, event coordination services, event management and supplier coordination, helping clients identify the support they need for their event.",
   "kind": "text"
  },
  {
   "section": 27,
   "item": null,
   "key": "intro",
   "old": "From large-scale concerts to intimate celebrations, we provide professional coordination and support across a wide range of event types. Explore our core service categories below.",
   "new": "From large-scale concerts to intimate celebrations, we provide professional event services and event coordination services across a wide range of event types, with event logistics organised around each brief. Explore our core service categories below.",
   "kind": "text"
  },
  {
   "section": 27,
   "item": {
    "id": 52,
    "title": "Event Planning & Management"
   },
   "key": "image",
   "old": "Delegates seated at round tables at a business conference",
   "new": "Business event guests seated at round tables in a hotel conference room",
   "kind": "alt"
  },
  {
   "section": 27,
   "item": {
    "id": 52,
    "title": "Event Planning & Management"
   },
   "key": "text",
   "old": "Support with planning, schedules, supplier coordination, logistics and delivery across the agreed scope of an event.",
   "new": "Event planning services covering schedules, supplier coordination, logistics and delivery across the agreed scope of an event.",
   "kind": "text"
  },
  {
   "section": 27,
   "item": {
    "id": 53,
    "title": "Concerts & Live Entertainment"
   },
   "key": "image",
   "old": "Live band with drums and keyboards on a purple-lit stage",
   "new": "Musicians performing on a concert stage with purple lighting and a large LED screen",
   "kind": "alt"
  },
  {
   "section": 27,
   "item": {
    "id": 54,
    "title": "Artist & Guest Logistics"
   },
   "key": "text",
   "old": "Coordination of travel, accommodation, local transport, hospitality and related arrangements according to the brief.",
   "new": "Coordination of travel, accommodation, local transport, event hospitality and related arrangements according to the brief.",
   "kind": "text"
  },
  {
   "section": 27,
   "item": {
    "id": 55,
    "title": "Venue & Supplier Coordination"
   },
   "key": "image",
   "old": "Collage of a community arts venue exterior, stage and seating",
   "new": "Community arts venue exterior and auditorium with red seating",
   "kind": "alt"
  },
  {
   "section": 27,
   "item": {
    "id": 55,
    "title": "Venue & Supplier Coordination"
   },
   "key": "text",
   "old": "Liaison with venues and appointed suppliers to help align requirements, schedules, access and practical arrangements.",
   "new": "Event logistics support, coordinating venues and suppliers around requirements, schedules, access and practical arrangements.",
   "kind": "text"
  },
  {
   "section": 27,
   "item": {
    "id": 56,
    "title": "Production Coordination"
   },
   "key": "image",
   "old": "Collage of musical instruments and drums under magenta lighting",
   "new": "Collage of keyboards, tabla drums and stage equipment set up for a live concert",
   "kind": "alt"
  },
  {
   "section": 27,
   "item": {
    "id": 56,
    "title": "Production Coordination"
   },
   "key": "text",
   "old": "Coordination with specialist sound, lighting, LED, staging and other technical suppliers. Technical services are delivered by appropriately appointed providers where applicable.",
   "new": "Production coordination with specialist sound, lighting, LED, staging and other technical suppliers, supporting event production services.",
   "kind": "text"
  },
  {
   "section": 27,
   "item": {
    "id": 58,
    "title": "Sports Events & Hospitality"
   },
   "key": "image",
   "old": "Table of sports trophies and cups",
   "new": "Rows of sports trophies and cups on a table",
   "kind": "alt"
  },
  {
   "section": 27,
   "item": {
    "id": 59,
    "title": "Cultural & Community Events"
   },
   "key": "image",
   "old": "Seated audience watching a performance in a hall",
   "new": "Audience seated in a hall watching a live community event, with a photographer in the front row",
   "kind": "alt"
  },
  {
   "section": 27,
   "item": {
    "id": 60,
    "title": "Hindu & Tamil Weddings"
   },
   "key": "image",
   "old": "Hindu and Tamil wedding ceremony with the couple at the sacred fire",
   "new": "Hindu wedding ceremony with the couple seated before a sacred fire, surrounded by family",
   "kind": "alt"
  },
  {
   "section": 28,
   "item": null,
   "key": "intro",
   "old": "Whether you need complete event management or support in specific areas, we can tailor our services to suit your requirements.",
   "new": "Whether you need complete event management services or support in specific areas, we can tailor our event solutions to suit your requirements.",
   "kind": "text"
  },
  {
   "section": 28,
   "item": {
    "id": 61,
    "title": "End-to-End Event Management"
   },
   "key": "image",
   "old": "Audience holding up glowing phone lights at an event",
   "new": "Audience holding up phone lights in a darkened theatre",
   "kind": "alt"
  },
  {
   "section": 28,
   "item": {
    "id": 61,
    "title": "End-to-End Event Management"
   },
   "key": "text",
   "old": "For clients seeking a partner to coordinate multiple elements of an event, from initial planning and supplier coordination to event-day delivery.",
   "new": "For clients seeking an event planning coordinator and event management partner to coordinate multiple elements of an event, from initial planning and supplier agreement to agreed event-day delivery.",
   "kind": "text"
  },
  {
   "section": 28,
   "item": {
    "id": 62,
    "title": "Specialist Event Support"
   },
   "key": "text",
   "old": "For organisers who already have a team and need help with selected requirements, such as artist logistics, accommodation, transport, venue coordination or supplier liaison.",
   "new": "For event organisers who already have a team and need event support services or event management support with selected requirements, such as artist logistics, accommodation, transport, venue coordination or supplier liaison.",
   "kind": "text"
  },
  {
   "section": 28,
   "item": {
    "id": 63,
    "title": "Partnership & Subcontracting"
   },
   "key": "text",
   "old": "For agencies, promoters and event companies seeking a delivery partner for defined work packages or agreed operational responsibilities.",
   "new": "For agencies, promoters and event companies, including an event production company that needs a delivery partner for defined work packages or agreed operational responsibilities.",
   "kind": "text"
  },
  {
   "section": 29,
   "item": null,
   "key": "heading",
   "old": "How We Work",
   "new": "From First Conversation to Event Day",
   "kind": "text"
  },
  {
   "section": 29,
   "item": {
    "id": 65,
    "title": "Scope"
   },
   "key": "text",
   "old": "We establish which services are needed and who is responsible for each element.",
   "new": "We establish which event planning services and event coordination are needed and who is responsible for each element.",
   "kind": "text"
  },
  {
   "section": 29,
   "item": {
    "id": 66,
    "title": "Plan"
   },
   "key": "text",
   "old": "We agree the approach, costs, timings and relevant supplier arrangements.",
   "new": "We agree the event planning approach, costs, timings and relevant supplier arrangements.",
   "kind": "text"
  },
  {
   "section": 29,
   "item": {
    "id": 67,
    "title": "Deliver"
   },
   "key": "text",
   "old": "We coordinate the agreed work and communicate with the relevant parties.",
   "new": "We coordinate the agreed work, keeping event operations on schedule and communicating with the relevant parties.",
   "kind": "text"
  },
  {
   "section": 30,
   "item": null,
   "key": "background",
   "old": "Audience holding up glowing phone lights at an event",
   "new": "Audience holding up phone lights in a darkened theatre during a live show",
   "kind": "alt"
  },
  {
   "section": 30,
   "item": null,
   "key": "text1",
   "old": "You do not need to commission a complete event-management package to speak to us. Tell us which parts of your event need support, and we can discuss a suitable scope.",
   "new": "You do not need to commission a complete event-management package to speak to us. Tell us which parts of your event need support, and we can discuss a suitable scope of event services, from event management support to full event coordination services.",
   "kind": "text"
  },
  {
   "section": 31,
   "item": null,
   "key": "background",
   "old": "Spectators at a cricket match on a sunny field with golden trophies in the foreground",
   "new": "Trophies and cups on a table beside a cricket pitch where guests are seated",
   "kind": "alt"
  },
  {
   "section": 31,
   "item": null,
   "key": "text1",
   "old": "From sporting occasions and corporate hospitality to sponsor events and group experiences, E for Events helps coordinate the details that make sporting events memorable.",
   "new": "From sporting occasions and corporate sports hospitality to sponsor events and group experiences, we also provide sports event management and sports hospitality across London and the UK, helping to coordinate the details that make sporting events memorable.",
   "kind": "text"
  },
  {
   "section": 32,
   "item": null,
   "key": "video_title",
   "old": "",
   "new": "E for Events showcase video: Over 70's World Cup Indian Team Gala Dinner",
   "kind": "text"
  },
  {
   "section": 33,
   "item": null,
   "key": "image",
   "old": "Table of sports trophies and cups",
   "new": "Trophies and cups displayed on a table at a sports club presentation",
   "kind": "alt"
  },
  {
   "section": 33,
   "item": null,
   "key": "text1",
   "old": "Sport brings people together—whether supporters are enjoying a live match, colleagues are attending corporate hospitality, sponsors are connecting with guests or organisations are hosting a sporting celebration.",
   "new": "Sport brings people together, whether supporters are enjoying a live match, colleagues are attending corporate sports hospitality, sponsors are connecting with guests or organisations are hosting a sporting celebration.",
   "kind": "text"
  },
  {
   "section": 33,
   "item": null,
   "key": "text2",
   "old": "Behind every successful occasion are practical arrangements that need to work together. E for Events helps clients coordinate event logistics, venues, suppliers, guest arrangements and the wider experience, according to each project's requirements.",
   "new": "Behind every successful occasion are practical arrangements that need to work together. As a sports event organiser and sports events agency, we help clients coordinate sports event logistics, venues, suppliers, guest arrangements and the wider experience, according to each project's requirements.",
   "kind": "text"
  },
  {
   "section": 33,
   "item": null,
   "key": "text3",
   "old": "Whether you are organising a corporate sporting event, planning a group hospitality experience or looking for additional support for an existing event, we can discuss a solution tailored to your plans.",
   "new": "Whether you are organising corporate sporting events, planning a group hospitality experience or looking for sports event support for an existing event, our sporting event management and sports event coordination can be tailored to your brief.",
   "kind": "text"
  },
  {
   "section": 34,
   "item": null,
   "key": "heading",
   "old": "Our Sports Event Services",
   "new": "Sports Event Management & Hospitality",
   "kind": "text"
  },
  {
   "section": 34,
   "item": null,
   "key": "intro",
   "old": "",
   "new": "Our sports event services cover six areas, from hospitality and logistics to sponsor events and celebrations, so you can choose the support that fits your event.",
   "kind": "text"
  },
  {
   "section": 34,
   "item": {
    "id": 69,
    "title": "Corporate Sports Hospitality"
   },
   "key": "text",
   "old": "Coordination support for businesses entertaining clients, recognising employees or building relationships through sporting experiences. This may include venue coordination, guest hospitality arrangements, guest communication, schedules and supplier liaison.",
   "new": "Coordination support for businesses delivering corporate sports hospitality: entertaining clients, recognising employees or building relationships at corporate hospitality sporting events. This may include venue and hospitality arrangements, guest communication, schedules and supplier liaison.",
   "kind": "text"
  },
  {
   "section": 34,
   "item": {
    "id": 70,
    "title": "Corporate Sporting Events"
   },
   "key": "text",
   "old": "Support for company sports days, networking events, client entertainment and team celebrations, tailored to the audience, venue and objectives.",
   "new": "Support for corporate sporting events, including a corporate sports day, networking events, client entertainment and team celebrations, tailored to the audience, venue and objectives.",
   "kind": "text"
  },
  {
   "section": 34,
   "item": {
    "id": 71,
    "title": "Sports Event Logistics"
   },
   "key": "text",
   "old": "Coordination of agreed requirements, including venue liaison, schedules, transport arrangements, supplier communication and practical event-day support.",
   "new": "Sports event logistics and coordination of agreed requirements, including venue liaison, schedules, transport arrangements, supplier communication and practical event-day support. Our sport venue and event management keeps venues and suppliers working to one plan.",
   "kind": "text"
  },
  {
   "section": 34,
   "item": {
    "id": 72,
    "title": "Sponsor & Partner Events"
   },
   "key": "text",
   "old": "Coordination support for sponsor gatherings, partner receptions, promotional activities and related events, working alongside the client's marketing and event teams.",
   "new": "Coordination support for sponsor events, including sponsor gatherings, partner receptions and promotional activities, working alongside the client's marketing and event teams. We also help businesses that sponsor for sports event activity by coordinating guest, venue and supplier arrangements.",
   "kind": "text"
  },
  {
   "section": 34,
   "item": {
    "id": 73,
    "title": "Group Sporting Experiences"
   },
   "key": "text",
   "old": "Support with planning group attendance at sporting occasions, including agreed guest arrangements, schedules, transport coordination and hospitality requirements.",
   "new": "Support with sports event planning for group attendance at sporting occasions, including agreed guest arrangements, schedules, transport coordination and hospitality requirements.",
   "kind": "text"
  },
  {
   "section": 34,
   "item": {
    "id": 74,
    "title": "Sports Dinners & Celebrations"
   },
   "key": "text",
   "old": "Planning and coordination support for awards evenings, sports club celebrations, presentation dinners and related social occasions.",
   "new": "Planning and coordination support for awards evenings, sports club events and celebrations, presentation dinners and related social occasions, with sports and hospitality management covering venue and guest arrangements.",
   "kind": "text"
  },
  {
   "section": 35,
   "item": {
    "id": 75,
    "title": "Corporate teams planning client entertainment or employee experiences."
   },
   "key": "title",
   "old": "Corporate teams planning client entertainment or employee experiences.",
   "new": "Corporate teams planning client entertainment, employee experiences or sports hospitality.",
   "kind": "text"
  },
  {
   "section": 35,
   "item": {
    "id": 76,
    "title": "Sports clubs and associations planning dinners, celebrations or gatherings."
   },
   "key": "title",
   "old": "Sports clubs and associations planning dinners, celebrations or gatherings.",
   "new": "Sports clubs and associations planning sports club events, dinners, celebrations or gatherings.",
   "kind": "text"
  },
  {
   "section": 35,
   "item": {
    "id": 78,
    "title": "Hospitality and venue partners."
   },
   "key": "title",
   "old": "Hospitality and venue partners.",
   "new": "Hospitality and venue partners, including sports hospitality companies.",
   "kind": "text"
  },
  {
   "section": 35,
   "item": {
    "id": 79,
    "title": "Sponsors and businesses organising partner events."
   },
   "key": "title",
   "old": "Sponsors and businesses organising partner events.",
   "new": "Sponsors and businesses organising sponsor events or partner events.",
   "kind": "text"
  },
  {
   "section": 36,
   "item": {
    "id": 81,
    "title": "Complete Event Coordination"
   },
   "key": "text",
   "old": "For clients who want support coordinating multiple elements of a sports-related event, from initial planning and venue arrangements to supplier liaison and agreed event-day delivery.",
   "new": "For clients who want complete sports event coordination across multiple elements of a sports-related event, from initial planning and venue arrangements to supplier coordination and agreed event-day delivery. As a sport event coordinator, we manage the agreed scope with you.",
   "kind": "text"
  },
  {
   "section": 36,
   "item": {
    "id": 82,
    "title": "Specialist Operational Support"
   },
   "key": "text",
   "old": "For event companies, corporate teams and organisers who already have a plan but need help with specific arrangements, logistics, hospitality coordination or supplier liaison.",
   "new": "For event companies, corporate teams and organisers who already have a plan but need sports event support with specific arrangements, logistics, sports hospitality management or supplier liaison. It can sit alongside sports event planning companies and in-house event teams.",
   "kind": "text"
  },
  {
   "section": 37,
   "item": null,
   "key": "text1",
   "old": "Whether you are organising corporate hospitality, a group sporting experience or a larger sports-related event, E for Events would welcome the opportunity to discuss your plans.",
   "new": "Whether you're organising corporate hospitality, a group sporting experience or a larger sports event, we'd welcome the opportunity to discuss your plans.",
   "kind": "text"
  },
  {
   "section": 38,
   "item": null,
   "key": "accent_new_line",
   "old": "1",
   "new": "0",
   "kind": "text"
  },
  {
   "section": 38,
   "item": null,
   "key": "background",
   "old": "Concert stage with a singer and band in soft pink light, faded on the left",
   "new": "Singer and guitarist performing on a lit concert stage in front of a cheering crowd",
   "kind": "alt"
  },
  {
   "section": 38,
   "item": null,
   "key": "heading",
   "old": "Our Work. Our Approach.",
   "new": "Event Management Portfolio - Our Work & Events",
   "kind": "text"
  },
  {
   "section": 38,
   "item": null,
   "key": "heading_accent",
   "old": "Our Events.",
   "new": "",
   "kind": "text"
  },
  {
   "section": 38,
   "item": null,
   "key": "tagline",
   "old": "",
   "new": "Our Work. Our Approach.",
   "kind": "text"
  },
  {
   "section": 38,
   "item": null,
   "key": "tagline_accent",
   "old": "",
   "new": "Our Events.",
   "kind": "text"
  },
  {
   "section": 38,
   "item": null,
   "key": "text2",
   "old": "Explore examples of events and projects that demonstrate the types of coordination and support E for Events provides.",
   "new": "Explore a selection of past events and projects across London, showcasing the coordination and support E for Events provides across concerts, weddings, corporate, cultural and community events.",
   "kind": "text"
  },
  {
   "section": 39,
   "item": null,
   "key": "intro",
   "old": "Each project shows how we work behind the scenes to coordinate suppliers, manage logistics and keep everything on track — so you can focus on your event.",
   "new": "Our event planning portfolio brings together real events and projects. Each one shows how we work behind the scenes in event project management, coordinating suppliers, managing logistics and keeping everything on track, so you can focus on your event.",
   "kind": "text"
  },
  {
   "section": 40,
   "item": null,
   "key": "image",
   "old": "Woman in a red sari clapping among a seated audience at a cultural concert",
   "new": "Audience members in traditional dress applauding at a live cultural event",
   "kind": "alt"
  },
  {
   "section": 40,
   "item": null,
   "key": "text1",
   "old": "An event is only as strong as the planning and coordination behind it. We focus on the practical requirements that help keep agreed workstreams organised and aligned.",
   "new": "Our approach is built around practical delivery. An event is only as strong as the planning and coordination behind it, so our event planning project management focuses on the practical requirements that keep agreed workstreams organised and aligned.",
   "kind": "text"
  },
  {
   "section": 40,
   "item": null,
   "key": "text2",
   "old": "From communicating with suppliers to coordinating schedules and venue arrangements, our role is shaped by the specific requirements of each project.",
   "new": "From communicating with suppliers to coordinating schedules and venue arrangements, our role is shaped by the specific requirements of each project. We keep people, timings and logistics aligned from planning through to event day.",
   "kind": "text"
  },
  {
   "section": 40,
   "item": {
    "id": 83,
    "title": "Supplier Communication"
   },
   "key": "text",
   "old": "",
   "new": "Clear, timely communication with venues, production teams and other suppliers.",
   "kind": "text"
  },
  {
   "section": 40,
   "item": {
    "id": 84,
    "title": "Schedule Coordination"
   },
   "key": "text",
   "old": "",
   "new": "Timings agreed, shared and tracked so every workstream stays aligned.",
   "kind": "text"
  },
  {
   "section": 40,
   "item": {
    "id": 85,
    "title": "Venue Arrangements"
   },
   "key": "text",
   "old": "",
   "new": "Access, set-up and practical requirements coordinated with the venue.",
   "kind": "text"
  },
  {
   "section": 40,
   "item": {
    "id": 86,
    "title": "Logistics & Transport"
   },
   "key": "text",
   "old": "",
   "new": "Artist and guest travel and local transport organised around the schedule.",
   "kind": "text"
  },
  {
   "section": 40,
   "item": {
    "id": 87,
    "title": "On-Day Support"
   },
   "key": "text",
   "old": "",
   "new": "Coordination on the day to keep event operations on schedule and the right people informed.",
   "kind": "text"
  },
  {
   "section": 41,
   "item": null,
   "key": "text1",
   "old": "If you are a promoter, event company, business or private client looking for event support, we would welcome the opportunity to discuss your plans.",
   "new": "If you are a promoter, event company, business or private client looking for event management support, we would welcome the opportunity to discuss your plans, from live event coordination to corporate event planning and private event management.",
   "kind": "text"
  },
  {
   "section": 42,
   "item": null,
   "key": "background",
   "old": "Theatre with red seating and a stage lit in purple",
   "new": "Empty concert hall with red seating and a stage set up with lighting and a drum kit",
   "kind": "alt"
  },
  {
   "section": 42,
   "item": null,
   "key": "text1",
   "old": "Whether you are planning a concert, exploring an event partnership or looking for help with a specific service, we would like to hear from you.",
   "new": "Whether you're planning a concert, exploring an event partnership or need support with an upcoming event, we'd love to hear from you.",
   "kind": "text"
  },
  {
   "section": 42,
   "item": null,
   "key": "text2",
   "old": "Share a few details about your event and the support you need. We will use this information to understand your brief and discuss the appropriate next steps.",
   "new": "Tell us a little about your event and the support you need, and we'll discuss the best way forward.",
   "kind": "text"
  },
  {
   "section": 43,
   "item": null,
   "key": "image",
   "old": "Speaker presenting to guests at the Indian Team Gala Dinner",
   "new": "Speaker presenting to seated guests at a gala dinner event",
   "kind": "alt"
  },
  {
   "section": 43,
   "item": null,
   "key": "intro",
   "old": "",
   "new": "Prefer to speak to us directly? Contact our team by email, phone or Instagram and tell us about the event support you need.",
   "kind": "text"
  },
  {
   "section": 44,
   "item": null,
   "key": "intro",
   "old": "The more you can tell us about your plans, the better we can understand the support you need. If your plans are still at an early stage, that's absolutely fine.",
   "new": "Use this form to tell us about your event and the support you need. The more you can share about your plans, the better we can understand your requirements. If you're still at an early stage, that's absolutely fine.",
   "kind": "text"
  },
  {
   "section": 44,
   "item": {
    "id": 93,
    "title": ""
   },
   "key": "placeholder",
   "old": "e.g. 12th Dec 2025 or Q1 2026 (optional)",
   "new": "e.g. 12th Dec 2026 or Q1 2027 (optional)",
   "kind": "text"
  },
  {
   "section": 44,
   "item": {
    "id": 94,
    "title": ""
   },
   "key": "placeholder",
   "old": "Enter event location",
   "new": "Enter event location (venue, city or area)",
   "kind": "text"
  },
  {
   "section": 44,
   "item": {
    "id": 96,
    "title": ""
   },
   "key": "hint",
   "old": "",
   "new": "Select every service that applies to your enquiry, including event production services and event logistics where you need them.",
   "kind": "text"
  },
  {
   "section": 44,
   "item": {
    "id": 96,
    "title": ""
   },
   "key": "options",
   "old": "Concert or live entertainment\nPartnership with event company\nFull event management\nHindu or Tamil wedding\nCorporate event or conference\nSports, cultural or community event\nSpecific service or subcontracting requirement\nOther",
   "new": "Concert or live entertainment\nPartnership with an event company\nFull event management services\nHindu or Tamil wedding\nCorporate event or conference\nSports, cultural or community event\nSpecific event support or subcontracting\nEvent production services\nEvent logistics support\nOther",
   "kind": "text"
  },
  {
   "section": 44,
   "item": {
    "id": 97,
    "title": ""
   },
   "key": "hint",
   "old": "",
   "new": "Include anything that helps us understand the support you need, such as venue, timings, suppliers or what you would like an event coordinator to handle.",
   "kind": "text"
  },
  {
   "section": 45,
   "item": null,
   "key": "text1",
   "old": "We're here to help you bring your vision to life.",
   "new": "Send us your event enquiry and we'll help bring your vision to life, from event planning to full event management and specialist support.",
   "kind": "text"
  }
 ],
 "projects": [
  {
   "id": 1,
   "old": {
    "our_role": "Event coordination & supplier liaison",
    "summary": "",
    "image_alt": "Seated audience watching a performance in a hall",
    "image_url": "/media/uploads/2026/11/seated-audience-watching-performance-3902dc.webp"
   },
   "new": {
    "our_role": "Live event coordination & supplier liaison",
    "summary": "Live event and concert management support in London, covering artist logistics, hospitality, venue liaison, suppliers and production coordination.",
    "image_alt": "Two performers on stage facing a large audience at a live entertainment event",
    "image_url": "/media/uploads/2026/11/live-entertainment-audience-stage-5070f7.webp"
   }
  },
  {
   "id": 2,
   "old": {
    "our_role": "Wedding planning & coordination",
    "summary": "",
    "image_alt": "Wedding table with fruit and traditional decorations",
    "image_url": "/media/uploads/2026/11/wedding-table-fruit-decorations-0ba391.webp"
   },
   "new": {
    "our_role": "Wedding planning and coordination",
    "summary": "Wedding planning and coordination for a 2024 London celebration, covering venue and suppliers, ceremony and reception support, guest arrangements and on-day coordination.",
    "image_alt": "Wedding ceremony table with fruit offerings, decorated ritual items and floral décor in a red and gold hall",
    "image_url": "/media/uploads/2026/11/wedding-table-fruit-decorations-0ba391.webp"
   }
  },
  {
   "id": 3,
   "old": {
    "our_role": "Full event coordination",
    "summary": "",
    "image_alt": "Group of men posing beside an event banner",
    "image_url": "/media/uploads/2026/11/men-posing-beside-event-banner-2b7f68.webp"
   },
   "new": {
    "our_role": "Full event coordination",
    "summary": "Full event coordination for corporate, cultural and community events, covering venues, logistics, suppliers, technical production and event-day delivery.",
    "image_alt": "Speaker with a microphone addressing a seated audience in suits at a corporate event",
    "image_url": "/media/uploads/2026/11/corporate-business-talk-audience-22576e.webp"
   }
  }
 ],
 "faq": {
  "page": "home",
  "after_type": "project_cards",
  "label": "FAQs",
  "fields": {
   "eyebrow": "FAQs",
   "eyebrow_style": "line",
   "heading": "Your Event Management Questions Answered",
   "accent_new_line": "0",
   "background_strength": "light"
  },
  "items": [
   {
    "question": "What does an event management company do?",
    "answer": "An event management company coordinates the planning, suppliers, venues, logistics and on-the-day delivery of an event. At E for Events, we can manage an event from start to finish or support the specific parts of it that you need."
   },
   {
    "question": "Can you support part of our event rather than the whole event?",
    "answer": "Yes. Alongside full event management, our specialist event support covers individual workstreams such as artist logistics, accommodation, local transport, venue coordination, hospitality and supplier liaison."
   },
   {
    "question": "What types of events do you work on?",
    "answer": "We work on concerts and live entertainment, Hindu and Tamil weddings, corporate events and conferences, sporting event management and sports hospitality, and cultural and community events."
   },
   {
    "question": "Do you offer event production services?",
    "answer": "Yes. Our event production services include production coordination, supplier liaison and on-site delivery for concerts, conferences and live events."
   },
   {
    "question": "Do you work with our existing event team?",
    "answer": "Yes. We are comfortable working alongside your team and appointed suppliers, whether you need us to lead the event or support it."
   },
   {
    "question": "Where do you work?",
    "answer": "We are an events company serving London and the wider UK. Tell us about your event and location, and we will discuss the right approach."
   }
  ]
 },
 "media_alt": [
  {
   "path_like": "e-for-events-logo-",
   "alt": "E for Events logo, UK event management and concerts"
  }
 ],
 "settings": {
  "email.enquiry_recipient": "sat@jwala.uk"
 }
}
PATCH_JSON
, true);

// Already applied? The marker is stored once everything below has run.
$marker = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE group_name = 'system' AND setting_key = 'patch_seo_content_1'");
$marker->execute();
if ((int) $marker->fetchColumn() > 0) { echo "Content updates already applied.\n"; exit(0); }

// New columns for projects (short description and picture description).
foreach (['summary' => 'VARCHAR(500) NULL', 'image_alt' => 'VARCHAR(255) NULL'] as $col => $def) {
    $has = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $has->execute(['projects', $col]);
    if (!(int) $has->fetchColumn()) { $pdo->exec("ALTER TABLE projects ADD COLUMN $col $def AFTER our_role"); echo "Added projects.$col\n"; }
}

$norm = fn ($v): string => trim((string) ($v ?? ''));
$applied = 0;
$skipped = [];

$pdo->beginTransaction();
try {
    // Page titles and descriptions for search engines.
    foreach ($patch['pages'] as $p) {
        $cur = $pdo->prepare('SELECT seo_title, seo_description FROM pages WHERE slug = ?');
        $cur->execute([$p['slug']]);
        $row = $cur->fetch();
        if (!$row) { $skipped[] = 'page ' . $p['slug']; continue; }
        if ($norm($row['seo_title']) !== $norm($p['old']['seo_title']) && $norm($row['seo_title']) !== '') { $skipped[] = 'seo ' . $p['slug']; continue; }
        $pdo->prepare('UPDATE pages SET seo_title = ?, seo_description = ? WHERE slug = ?')->execute([$p['new']['seo_title'], $p['new']['seo_description'], $p['slug']]);
        $applied++;
    }

    // Text and picture descriptions, field by field. A field is only changed if it still has the value it had before.
    $findItem = $pdo->prepare('SELECT id FROM section_items WHERE id = ? AND section_id = ?');
    $findByTitle = $pdo->prepare("SELECT i.id FROM section_items i JOIN section_fields f ON f.item_id = i.id AND f.field_key = 'title' WHERE i.section_id = ? AND f.value_text = ? LIMIT 1");
    $findSection = $pdo->prepare('SELECT id FROM sections WHERE id = ?');
    foreach ($patch['fields'] as $f) {
        $findSection->execute([$f['section']]);
        if (!$findSection->fetchColumn()) { $skipped[] = 'section ' . $f['section']; continue; }
        $itemId = null;
        if ($f['item'] !== null) {
            $findItem->execute([$f['item']['id'], $f['section']]);
            $itemId = $findItem->fetchColumn();
            if (!$itemId && $f['item']['title'] !== '') {
                $findByTitle->execute([$f['section'], $f['item']['title']]);
                $itemId = $findByTitle->fetchColumn();
            }
            if (!$itemId) { $skipped[] = "item {$f['section']}/{$f['item']['id']}"; continue; }
        }
        $sel = $itemId === null
            ? $pdo->prepare('SELECT id, value_text, media_id FROM section_fields WHERE section_id = ? AND item_id IS NULL AND field_key = ?')
            : $pdo->prepare('SELECT id, value_text, media_id FROM section_fields WHERE section_id = ? AND item_id = ? AND field_key = ?');
        $sel->execute($itemId === null ? [$f['section'], $f['key']] : [$f['section'], $itemId, $f['key']]);
        $row = $sel->fetch();
        if ($f['kind'] === 'alt') {
            // The picture's own description for this place.
            if (!$row || !$row['media_id'] || $norm($row['value_text']) !== '') { $skipped[] = "alt {$f['section']}/{$f['key']}"; continue; }
            $pdo->prepare('UPDATE section_fields SET value_text = ? WHERE id = ?')->execute([$f['new'], $row['id']]);
            $applied++;
            continue;
        }
        if ($row) {
            if ($norm($row['value_text']) !== $norm($f['old'])) { $skipped[] = "text {$f['section']}/{$f['key']}"; continue; }
            $pdo->prepare('UPDATE section_fields SET value_text = ? WHERE id = ?')->execute([$f['new'], $row['id']]);
        } else {
            // No saved row means the field was showing its default value, so there is nothing to protect.
            $pdo->prepare('INSERT INTO section_fields (section_id, item_id, field_key, value_text) VALUES (?, ?, ?, ?)')->execute([$f['section'], $itemId, $f['key'], $f['new']]);
        }
        $applied++;
    }

    // The new FAQ section on the Home page.
    if ($patch['faq']) {
        $q = $patch['faq'];
        $pg = $pdo->prepare('SELECT id FROM pages WHERE slug = ?');
        $pg->execute([$q['page']]);
        $pageId = (int) $pg->fetchColumn();
        $exists = $pdo->prepare("SELECT COUNT(*) FROM sections WHERE page_id = ? AND type = 'faq'");
        $exists->execute([$pageId]);
        if ($pageId && !(int) $exists->fetchColumn()) {
            $after = $pdo->prepare('SELECT sort_order FROM sections WHERE page_id = ? AND type = ?');
            $after->execute([$pageId, $q['after_type']]);
            $order = (int) $after->fetchColumn();
            $pdo->prepare('UPDATE sections SET sort_order = sort_order + 1 WHERE page_id = ? AND sort_order > ?')->execute([$pageId, $order]);
            $pdo->prepare("INSERT INTO sections (page_id, type, label, is_visible, sort_order) VALUES (?, 'faq', ?, 1, ?)")->execute([$pageId, $q['label'], $order + 1]);
            $sid = (int) $pdo->lastInsertId();
            $ins = $pdo->prepare('INSERT INTO section_fields (section_id, item_id, field_key, value_text) VALUES (?, ?, ?, ?)');
            foreach ($q['fields'] as $k => $v) { $ins->execute([$sid, null, $k, (string) $v]); }
            foreach ($q['items'] as $n => $it) {
                $pdo->prepare('INSERT INTO section_items (section_id, is_visible, sort_order) VALUES (?, 1, ?)')->execute([$sid, $n + 1]);
                $iid = (int) $pdo->lastInsertId();
                $ins->execute([$sid, $iid, 'question', $it['question']]);
                $ins->execute([$sid, $iid, 'answer', $it['answer']]);
            }
            $applied++;
        }
    }

    // Projects: role, short description, picture and its description.
    foreach ($patch['projects'] as $p) {
        $cur = $pdo->prepare('SELECT our_role FROM projects WHERE id = ?');
        $cur->execute([$p['id']]);
        $role = $cur->fetchColumn();
        if ($role === false || $norm($role) !== $norm($p['old']['our_role'])) { $skipped[] = 'project ' . $p['id']; continue; }
        $media = $pdo->prepare('SELECT id FROM media WHERE file_path = ?');
        $media->execute([preg_replace('#^/media/#', '', $p['new']['image_url'])]);
        $mid = $media->fetchColumn();
        if (!$mid) { $skipped[] = 'project picture ' . $p['id']; continue; }
        $pdo->prepare('UPDATE projects SET our_role = ?, summary = ?, image_alt = ?, image_media_id = ? WHERE id = ?')
            ->execute([$p['new']['our_role'], $p['new']['summary'], $p['new']['image_alt'], (int) $mid, $p['id']]);
        $applied++;
    }

    // The logo's description, and where new enquiries are sent.
    foreach ($patch['media_alt'] as $m) {
        $pdo->prepare('UPDATE media SET alt_text = ? WHERE file_path LIKE ?')->execute([$m['alt'], 'uploads/%' . $m['path_like'] . '%']);
        $applied++;
    }
    foreach ($patch['settings'] as $id => $value) {
        [$group, $key] = explode('.', $id, 2);
        $cur = $pdo->prepare('SELECT id, value_text FROM settings WHERE group_name = ? AND setting_key = ?');
        $cur->execute([$group, $key]);
        $row = $cur->fetch();
        if ($row && $norm($row['value_text']) !== '') { $skipped[] = 'setting ' . $id; continue; }
        if ($row) {
            $pdo->prepare('UPDATE settings SET value_text = ? WHERE id = ?')->execute([$value, $row['id']]);
        } else {
            $pdo->prepare('INSERT INTO settings (group_name, setting_key, value_text, is_encrypted) VALUES (?, ?, ?, 0)')->execute([$group, $key, $value]);
        }
        $applied++;
    }

    $pdo->prepare("INSERT INTO settings (group_name, setting_key, value_text, is_encrypted) VALUES ('system', 'patch_seo_content_1', '1', 0)")->execute();
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR, 'Content updates failed and were rolled back: ' . $e->getMessage() . "\n");
    exit(1);
}
echo "Content updates applied: $applied changes";
echo $skipped ? '; left as they were because they had been edited: ' . implode(', ', $skipped) . "\n" : "\n";
