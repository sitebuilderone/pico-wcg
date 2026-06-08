# AGENTS.md

## Project Context

This repository is a LiveCanvas child theme for West Coast Geothermal (WCG).

Repository live online at:

https://github.com/sitebuilderone/pico-wcg

LiveCanvas is a Bootstrap 5 based plugin and theme for WordPress, so theme work
should generally follow Bootstrap 5 structure, spacing, grid, utility classes,
and component conventions unless the existing code points another way.

## Important Folders

- `/html` contains flat HTML development files.
- `/html-lc` contains LiveCanvas partials and dynamic templates ( https://docs.livecanvas.com/dynamic-templating/ )
- `/sass` contains Sass customizations for the site.

Current Sass customization files include:

- `/sass/custom.scss`
- `/sass/_header.scss`

## Working Notes

- Keep changes aligned with LiveCanvas and Bootstrap 5 patterns.
- Prefer editing the child theme instead of modifying parent theme or plugin
  files.
- Keep flat HTML prototypes and LiveCanvas partials in sync when a change
  affects both.
- Treat Sass files as the primary place for custom visual styling.

## Design Reference Pages

Use these pages as the primary design and messaging references when creating or
updating landing pages, service pages, and campaign-style layouts:

- `/html-lc/pages/services.html` (current services page baseline; includes
  search-entry messaging, dynamic service list patterns, and conversion-first
  section flow)
- `/html/condo-heat-pump-replacement.html` (campaign reference for high-clarity
  heat pump repair vs replacement storytelling and section pacing)

Related styling references:

- `/sass/_services.scss` (services page component styles)
- `/sass/_condo.scss` (condo campaign component styles)

When designing new pages, prefer these patterns:

- Keep conversion-focused structure: clear hero, practical trust indicators,
  scannable service blocks, and direct CTA sections.
- Maintain WCG visual language already established in the reference pages
  (typography scale, spacing rhythm, border/rule usage, and warm neutral color
  system).
- For dynamic service content, reuse loop-based patterns from
  `/html-lc/pages/services.html` before introducing new custom structures.

## Private Strategy Reference (Local Only)

- Keep your private planning document in `/private-docs/` (this folder is
  gitignored).
- Recommended filename: `/private-docs/HeatPumpCampaign.md`.
- If present, use this document as the primary source for messaging,
  prioritization, and implementation direction.
- Keep sensitive business details in that local document, not in repository
  tracked files.
