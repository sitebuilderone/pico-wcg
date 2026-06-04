/* ==========================================================================
   West Coast Geothermal — Condo Heat Pump Replacement page

   ========================================================================== */
(function () {
  'use strict';

  // --- Footer year (in case main.js isn't present) ------------------------
  var yearEl = document.getElementById('year');
  if (yearEl) { yearEl.textContent = new Date().getFullYear(); }

  // --- FAQ accordion ------------------------------------------------------
  var items = document.querySelectorAll('.faq-item');
  items.forEach(function (item) {
    var row = item.querySelector('.faq-row');
    if (!row) { return; }
    row.addEventListener('click', function () {
      var isOpen = item.classList.contains('open');
      items.forEach(function (other) {
        other.classList.remove('open');
        var r = other.querySelector('.faq-row');
        if (r) { r.setAttribute('aria-expanded', 'false'); }
      });
      if (!isOpen) {
        item.classList.add('open');
        row.setAttribute('aria-expanded', 'true');
      }
    });
    // keyboard support
    row.setAttribute('role', 'button');
    row.setAttribute('tabindex', '0');
    row.setAttribute('aria-expanded', 'false');
    row.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
    });
  });

  // --- Contact form: intent radios ---------------------------------------
  var radios = document.querySelectorAll('#intent-radios .form-radio');
  var intentInput = document.getElementById('intent-value');
  radios.forEach(function (radio) {
    radio.addEventListener('click', function () {
      radios.forEach(function (r) { r.classList.remove('active'); });
      radio.classList.add('active');
      if (intentInput) { intentInput.value = radio.getAttribute('data-val') || ''; }
    });
  });

  // Deep-link offers -> preselect the matching radio + scroll
  document.querySelectorAll('[data-intent]').forEach(function (link) {
    link.addEventListener('click', function () {
      var want = link.getAttribute('data-intent');
      radios.forEach(function (r) {
        if (r.getAttribute('data-val') === want) { r.click(); }
      });
    });
  });

  // --- Contact form: demo submit -----------------------------------------
  var form = document.getElementById('contact-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('.form-submit');
      if (btn) {
        btn.innerHTML = 'Thanks — we\u2019ll call you back today \u2713';
        btn.style.background = '#2C8A4A';
        btn.disabled = true;
      }
    });
  }
})();
