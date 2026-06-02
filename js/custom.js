// to enable the enqueue of this optional JS file, 
// you'll have to uncomment a row in the functions.php file
// just read the comments in there mate

console.log("Custom js file loaded");

//add here your own js code. Vanilla JS welcome.


 (function () {
    'use strict';
    var body = document.body;

    // Compact the header once the topbar scrolls away
    var topbar = document.querySelector('.topbar');
    function onScroll() {
      var threshold = (topbar ? topbar.offsetHeight : 40) - 2;
      body.classList.toggle('is-stuck', window.scrollY > Math.max(threshold, 8));
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Mobile panel open/close
    function setNav(open) {
      body.classList.toggle('nav-open', open);
      var t = document.querySelector('[data-nav-open]');
      if (t) { t.setAttribute('aria-expanded', open ? 'true' : 'false'); }
      body.style.overflow = open ? 'hidden' : '';
    }
    document.querySelectorAll('[data-nav-open]').forEach(function (el) {
      el.addEventListener('click', function () { setNav(!body.classList.contains('nav-open')); });
    });
    document.querySelectorAll('[data-nav-close]').forEach(function (el) {
      el.addEventListener('click', function () { setNav(false); });
    });
    document.querySelectorAll('.mobile-nav a').forEach(function (el) {
      el.addEventListener('click', function () { setNav(false); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { setNav(false); }
    });

    // Mobile Services accordion
    var acc = document.querySelector('.m-acc');
    var accBtn = document.querySelector('.m-acc-btn');
    if (acc && accBtn) {
      accBtn.addEventListener('click', function () {
        var open = acc.classList.toggle('open');
        accBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }
  })();
