/* ==========================================================================
   West Coast Geothermal — theme JS
   Bootstrap's bundle handles the navbar collapse; this file carries the
   small bits of progressive enhancement.
   ========================================================================== */
(function () {
  'use strict';

  // --- Current year in the footer -----------------------------------------
  var yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  // --- Smooth scroll for in-page anchors ----------------------------------
  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var id = link.getAttribute('href');
      if (id === '#' || id.length < 2) { return; }
      var target = document.querySelector(id);
      if (!target) { return; }

      e.preventDefault();
      var top = target.getBoundingClientRect().top + window.pageYOffset - 8;
      window.scrollTo({ top: top, behavior: prefersReduced ? 'auto' : 'smooth' });

      // Collapse the mobile nav after navigating, if open
      var nav = document.getElementById('primaryNav');
      if (nav && nav.classList.contains('show') && window.bootstrap) {
        var instance = window.bootstrap.Collapse.getInstance(nav);
        if (instance) { instance.hide(); }
      }
    });
  });
})();
