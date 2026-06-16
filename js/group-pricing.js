/* ==========================================================================
   West Coast Geothermal — Building Group Pricing page
   Progressive enhancement: FAQ accordion, role radios, demo form submit.
   The shared js/main.js handles smooth-scroll on every page.
   ========================================================================== */
(function () {
  'use strict';

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
    row.setAttribute('role', 'button');
    row.setAttribute('tabindex', '0');
    row.setAttribute('aria-expanded', 'false');
    row.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); row.click(); }
    });
  });

  // --- Form: role radios --------------------------------------------------
  var radios = document.querySelectorAll('#role-radios .form-radio');
  var roleInput = document.getElementById('role-value');
  radios.forEach(function (radio) {
    radio.addEventListener('click', function () {
      radios.forEach(function (r) { r.classList.remove('active'); });
      radio.classList.add('active');
      if (roleInput) { roleInput.value = radio.getAttribute('data-val') || ''; }
    });
  });

  // --- Form: demo submit --------------------------------------------------
  var form = document.getElementById('contact-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('.form-submit');
      if (btn) {
        btn.innerHTML = 'Thanks \u2014 we\u2019ll review your building today \u2713';
        btn.style.background = '#2C8A4A';
        btn.disabled = true;
      }
    });
  }
})();
