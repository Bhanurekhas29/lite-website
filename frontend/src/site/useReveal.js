import { useEffect } from 'react'

// Scroll animation for a page:
//  - parts of each section fade up as they scroll into view;
//  - lists (like the "Why E for Events" points) appear one step at a time;
//  - photos settle in with a gentle zoom.
// The first screen (hero and stats) is left alone. Visitors who ask for
// reduced motion see everything straight away.
const LISTS = '.cards, .icon-row, .steps, .features, .iconlist, .pills, .checklist, .hcards, .contact-rows'
const IMAGES =
  '.card-media img, .project-media img, .hcard-media img, .split-media:not(.has-frame):not(.has-second) img:not(.script-badge-img), .intro-image'

export function useReveal(enabled) {
  useEffect(() => {
    if (!enabled || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return undefined

    const targets = []
    const add = (el, delay = 0) => targets.push([el, delay])

    // A block is one thing that fades up, or a list whose children come in one by one.
    const addBlock = (block, base = 0) => {
      // the enquiry form: its fields appear one after another, then the send button
      if (block.matches('.enquiry-form')) {
        const parts = [...block.querySelectorAll('.form-field, .form-consent, .form-submit')]
        parts.forEach((el, n) => add(el, Math.min(n * 80, 800)))
        return base
      }
      if (block.matches(LISTS)) {
        // steps arrive slowly, in time with the line drawing between them
        const gap = block.matches('.steps') ? 450 : block.matches('.checklist') ? 300 : 150
        ;[...block.children].forEach((child, n) => add(child, base + n * gap))
        return base + block.children.length * gap
      }
      add(block, base)
      return base
    }

    document.querySelectorAll('.site-main > section.section').forEach((section) => {
      section.querySelectorAll(':scope > .container').forEach((container) => {
        if (container.classList.contains('split')) {
          // text column first, point by point, then the photo
          let delay = 0
          container.querySelectorAll(':scope > .split-text > *').forEach((block) => {
            delay = addBlock(block, block.matches(LISTS) ? delay : 0)
          })
          container.querySelectorAll(':scope > :not(.split-text)').forEach((b) => add(b, 150))
        } else {
          container.querySelectorAll(':scope > *').forEach((block) => addBlock(block))
        }
      })
    })

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return
          entry.target.classList.add('is-in', 'zoomed')
          observer.unobserve(entry.target)
        })
      },
      { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    )

    const cleanup = (e) => {
      if (e.target !== e.currentTarget || e.propertyName !== 'opacity') return
      if (!e.currentTarget.classList.contains('is-in')) return
      e.currentTarget.classList.remove('reveal', 'is-in')
      e.currentTarget.style.transitionDelay = ''
      e.currentTarget.removeEventListener('transitionend', cleanup)
    }
    targets.forEach(([el, delay]) => {
      el.classList.add('reveal')
      el.style.transitionDelay = `${delay}ms`
      el.style.setProperty('--d', `${delay}ms`)
      el.addEventListener('transitionend', cleanup)
      observer.observe(el)
    })

    const marks = [...document.querySelectorAll('.site-main > section.section:not(:first-child) :is(.eyebrow, .heading em, .steps)')]
    marks.forEach((el) => {
      el.classList.add('fx')
      observer.observe(el)
    })

    const images = [...document.querySelectorAll(`.site-main > section.section:not(:first-child) :is(${IMAGES})`)]
    images.forEach((img) => {
      img.classList.add('zoom-img')
      observer.observe(img)
    })

    return () => {
      observer.disconnect()
      targets.forEach(([el]) => {
        el.classList.remove('reveal', 'is-in')
        el.style.transitionDelay = ''
        el.removeEventListener('transitionend', cleanup)
      })
      images.forEach((img) => img.classList.remove('zoom-img', 'zoomed', 'is-in'))
      marks.forEach((el) => el.classList.remove('fx', 'zoomed', 'is-in'))
    }
  }, [enabled])
}
