// to enable the enqueue of this optional JS file, 
// you'll have to uncomment a row in the functions.php file
// just read the comments in there mate

console.log("Custom js file loaded");

//add here your own js code. Vanilla JS welcome.


 (function () {
    'use strict';
    var body = document.body;
    var root = document.querySelector('.lc-header-wcg');
    if (!root) { return; }

    var header = root.querySelector('.site-header');
    if (!header) { return; }

    var fixedSpacer = document.createElement('div');
    fixedSpacer.setAttribute('aria-hidden', 'true');
    fixedSpacer.style.display = 'none';
    header.insertAdjacentElement('afterend', fixedSpacer);

    function applyStickyFallback(isStuck) {
      if (isStuck) {
        var h = header.offsetHeight || 0;
        fixedSpacer.style.display = 'block';
        fixedSpacer.style.height = h + 'px';
        header.style.position = 'fixed';
        header.style.top = '0';
        header.style.left = '0';
        header.style.right = '0';
        header.style.width = '100%';
        header.style.zIndex = '1030';
      } else {
        fixedSpacer.style.display = 'none';
        fixedSpacer.style.height = '0';
        header.style.position = '';
        header.style.top = '';
        header.style.left = '';
        header.style.right = '';
        header.style.width = '';
        header.style.zIndex = '';
      }
    }

    // Compact the header once the topbar scrolls away
    var topbar = root.querySelector('.topbar');
    function onScroll() {
      var threshold = (topbar ? topbar.offsetHeight : 40) - 2;
      var isStuck = window.scrollY > Math.max(threshold, 8);
      body.classList.toggle('is-stuck', isStuck);
      root.classList.toggle('is-stuck', isStuck);
      applyStickyFallback(isStuck);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    onScroll();

    // Mobile panel open/close
    function setNav(open) {
      body.classList.toggle('nav-open', open);
      root.classList.toggle('nav-open', open);
      root.querySelectorAll('[data-nav-open]').forEach(function (btn) {
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      body.style.overflow = open ? 'hidden' : '';
    }
    root.addEventListener('click', function (e) {
      var openBtn = e.target.closest('[data-nav-open]');
      if (openBtn) {
        e.preventDefault();
        setNav(!root.classList.contains('nav-open'));
        return;
      }

      var closeBtn = e.target.closest('[data-nav-close], .mobile-nav a');
      if (closeBtn) {
        setNav(false);
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { setNav(false); }
    });

    // Mobile Services accordion
    var acc = root.querySelector('.m-acc');
    var accBtn = root.querySelector('.m-acc-btn');
    if (acc && accBtn) {
      accBtn.addEventListener('click', function () {
        var open = acc.classList.toggle('open');
        accBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }
  })();



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
