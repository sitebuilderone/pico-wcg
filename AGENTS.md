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
