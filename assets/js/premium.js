/* ===================================================================
   Rais Doultana Book House — Premium interaction layer
   Loads AFTER main.js. Requires (loaded via CDN in footer.php):
   Lenis, GSAP + ScrollTrigger, Three.js (r128). Every block below
   checks the library exists before using it, so nothing breaks if a
   CDN is slow/blocked — the site still works, just without that
   one flourish.
   =================================================================== */
(function () {
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.addEventListener('DOMContentLoaded', function () {
    initScrollReveals();
    initHeroCanvas();
    initMagneticButtons();
    initRipple();
    initCounters();
    initGalleryLightbox();
    initCursorGlow();
  });

  /* Lenis smooth-scroll was removed on purpose — it added a noticeable
     delay/lag between the mouse-wheel/touchpad input and the page
     actually moving, and made the next section take a while to appear.
     Native scrolling + ScrollTrigger below is instant and still smooth. */

  /* ---------------- GSAP ScrollTrigger reveals ----------------
     main.js already adds .in-view via IntersectionObserver for the
     no-JS-library fallback. If GSAP is available we do a nicer
     staggered version instead, driven by ScrollTrigger. */
  function initScrollReveals() {
    if (reduceMotion || typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);

    document.querySelectorAll('.grid').forEach(function (grid) {
      var cards = grid.querySelectorAll('.reveal');
      if (!cards.length) return;
      gsap.fromTo(cards,
        { opacity: 0, y: 24, rotateX: -6 },
        {
          opacity: 1, y: 0, rotateX: 0, duration: .5, ease: 'power2.out', stagger: .06,
          scrollTrigger: { trigger: grid, start: 'top 95%' },
          onStart: function () { cards.forEach(function (c) { c.classList.add('in-view'); }); }
        }
      );
    });

    // Section headings: soft text reveal
    document.querySelectorAll('.section-head').forEach(function (head) {
      gsap.fromTo(head, { opacity: 0, y: 16 }, {
        opacity: 1, y: 0, duration: .45, ease: 'power2.out',
        scrollTrigger: { trigger: head, start: 'top 98%' }
      });
    });
  }

  /* ---------------- Three.js hero — floating golden particles + gently tumbling "books" ---------------- */
  function initHeroCanvas() {
    var mount = document.getElementById('hero-canvas');
    if (!mount || reduceMotion || typeof THREE === 'undefined') return;

    var width = mount.clientWidth, height = mount.clientHeight;
    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 100);
    camera.position.set(0, 0, 18);

    var renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    mount.appendChild(renderer.domElement);

    // Golden floating particles
    var particleCount = width < 700 ? 60 : 130;
    var particleGeo = new THREE.BufferGeometry();
    var positions = new Float32Array(particleCount * 3);
    for (var i = 0; i < particleCount; i++) {
      positions[i * 3] = (Math.random() - 0.5) * 30;
      positions[i * 3 + 1] = (Math.random() - 0.5) * 18;
      positions[i * 3 + 2] = (Math.random() - 0.5) * 12;
    }
    particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    var particleMat = new THREE.PointsMaterial({ color: 0xfff6e0, size: 0.12, transparent: true, opacity: 0.9 });
    var particles = new THREE.Points(particleGeo, particleMat);
    scene.add(particles);

    // Small tumbling "books" — simple gold-edged navy boxes
    var books = [];
    var bookColors = [0x12235c, 0xc9971f, 0x1d3fae];
    var bookCount = width < 700 ? 3 : 5;
    for (var b = 0; b < bookCount; b++) {
      var geo = new THREE.BoxGeometry(1.6, 2.2, 0.35);
      var mat = new THREE.MeshBasicMaterial({ color: bookColors[b % bookColors.length], transparent: true, opacity: 0.85 });
      var mesh = new THREE.Mesh(geo, mat);
      var edges = new THREE.EdgesGeometry(geo);
      var line = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: 0xfff6e0, transparent: true, opacity: 0.8 }));
      mesh.add(line);
      mesh.position.set((Math.random() - 0.5) * 12, (Math.random() - 0.5) * 8, (Math.random() - 0.5) * 6 - 2);
      mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, Math.random() * Math.PI);
      mesh.userData.speed = 0.15 + Math.random() * 0.2;
      mesh.userData.float = Math.random() * Math.PI * 2;
      scene.add(mesh);
      books.push(mesh);
    }

    var mouseX = 0, mouseY = 0;
    window.addEventListener('mousemove', function (e) {
      mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
      mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
    });

    var clock = new THREE.Clock();
    function animate() {
      requestAnimationFrame(animate);
      var t = clock.getElapsedTime();

      particles.rotation.y = t * 0.02;
      particles.rotation.x = t * 0.01;

      books.forEach(function (mesh) {
        mesh.rotation.x += 0.002 * mesh.userData.speed * 10 * 0.02;
        mesh.rotation.y += 0.003 * mesh.userData.speed * 10 * 0.02;
        mesh.position.y += Math.sin(t + mesh.userData.float) * 0.002;
      });

      camera.position.x += (mouseX * 1.5 - camera.position.x) * 0.03;
      camera.position.y += (-mouseY * 1.5 - camera.position.y) * 0.03;
      camera.lookAt(0, 0, 0);

      renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', function () {
      var w = mount.clientWidth, h = mount.clientHeight;
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    });
  }

  /* ---------------- Magnetic buttons ---------------- */
  function initMagneticButtons() {
    if (reduceMotion || !window.matchMedia || !window.matchMedia('(hover: hover)').matches) return;
    document.querySelectorAll('.btn').forEach(function (btn) {
      btn.classList.add('magnetic');
      btn.addEventListener('mousemove', function (e) {
        var rect = btn.getBoundingClientRect();
        var x = e.clientX - rect.left - rect.width / 2;
        var y = e.clientY - rect.top - rect.height / 2;
        btn.style.transform = 'translate(' + (x * 0.18).toFixed(1) + 'px,' + (y * 0.35).toFixed(1) + 'px)';
      });
      btn.addEventListener('mouseleave', function () { btn.style.transform = ''; });
    });
  }

  /* ---------------- Button ripple ---------------- */
  function initRipple() {
    document.querySelectorAll('.btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        var rect = btn.getBoundingClientRect();
        var ripple = document.createElement('span');
        ripple.className = 'ripple';
        var size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(ripple);
        setTimeout(function () { ripple.remove(); }, 650);
      });
    });
  }

  /* ---------------- Animated stat counters ---------------- */
  function initCounters() {
    var stats = document.querySelectorAll('.stat-row .stat b');
    if (!stats.length) return;

    function animateCounter(el) {
      var raw = el.textContent.trim();
      var match = raw.match(/^([^\d]*)(\d+(?:\.\d+)?)(.*)$/);
      if (!match) return;
      var prefix = match[1], target = parseFloat(match[2]), suffix = match[3];
      var duration = 1400, startTime = null;
      function step(ts) {
        if (!startTime) startTime = ts;
        var progress = Math.min((ts - startTime) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        var current = (target * eased);
        el.textContent = prefix + (Number.isInteger(target) ? Math.round(current) : current.toFixed(1)) + suffix;
        if (progress < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    if ('IntersectionObserver' in window) {
      var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) { animateCounter(entry.target); obs.unobserve(entry.target); }
        });
      }, { threshold: 0.6 });
      stats.forEach(function (el) { obs.observe(el); });
    }
  }

  /* ---------------- Gallery lightbox ---------------- */
  function initGalleryLightbox() {
    var items = document.querySelectorAll('.gallery-item');
    if (!items.length) return;

    var overlay = document.createElement('div');
    overlay.className = 'lightbox-overlay';
    overlay.innerHTML = '<div class="lightbox-close">&times;</div><img alt="">';
    document.body.appendChild(overlay);
    var overlayImg = overlay.querySelector('img');

    function open(src, alt) {
      overlayImg.src = src;
      overlayImg.alt = alt || '';
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }

    items.forEach(function (item) {
      var img = item.querySelector('img');
      if (!img) return;
      item.addEventListener('click', function () { open(img.src, img.alt); });
    });
    overlay.addEventListener('click', function (e) { if (e.target === overlay || e.target.classList.contains('lightbox-close')) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }

  /* ---------------- Cursor glow (desktop only) ---------------- */
  function initCursorGlow() {
    if (reduceMotion || !window.matchMedia || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
    var glow = document.createElement('div');
    glow.className = 'cursor-glow';
    document.body.appendChild(glow);
    window.addEventListener('mousemove', function (e) {
      glow.style.left = e.clientX + 'px';
      glow.style.top = e.clientY + 'px';
    });
  }
})();
