document.addEventListener('DOMContentLoaded', function () {
  // Mobile nav toggle
  var toggle = document.querySelector('.menu-toggle');
  var nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      nav.classList.toggle('open');
      toggle.textContent = nav.classList.contains('open') ? '✕' : '☰';
    });
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('open');
        toggle.textContent = '☰';
      });
    });
  }

  // Duplicate marquee content so the scrolling loop is seamless
  var track = document.querySelector('.marquee-track');
  if (track) {
    track.innerHTML += track.innerHTML;
  }

  // Reveal-on-scroll animation
  var revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(function (el) { observer.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in-view'); });
  }

  // Sticky header shadow on scroll
  var header = document.querySelector('.site-header');
  if (header) {
    window.addEventListener('scroll', function () {
      header.style.boxShadow = window.scrollY > 12
        ? '0 8px 22px rgba(0,0,0,.22)'
        : '0 6px 18px rgba(0,0,0,.15)';
    });
  }

  // 3D tilt-on-hover for category tiles, collection cards and book covers.
  // The card rotates slightly toward the cursor (like tilting a physical
  // book cover in your hands) and eases back flat on mouse-leave.
  var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!prefersReducedMotion && window.matchMedia && window.matchMedia('(hover: hover)').matches) {
    var tiltSelectors = '.tile-card, .collection-card, .book-card';
    var maxTilt = 9; // degrees

    document.querySelectorAll(tiltSelectors).forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var centerX = rect.width / 2;
        var centerY = rect.height / 2;
        var rotateX = ((y - centerY) / centerY) * -maxTilt;
        var rotateY = ((x - centerX) / centerX) * maxTilt;
        card.style.transform =
          'perspective(900px) rotateX(' + rotateX.toFixed(2) + 'deg) rotateY(' + rotateY.toFixed(2) + 'deg) translateY(-6px)';
      });
      card.addEventListener('mouseleave', function () {
        card.style.transform = '';
      });
    });
  }
});
