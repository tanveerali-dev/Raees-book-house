<?php $settings = get_settings($pdo); ?>
<footer class="site-footer" id="contact">
  <div class="container">
    <div class="footer-grid">
      <div>
        <h4><?= h($settings['site_name']) ?></h4>
        <p><?= h($settings['tagline']) ?>. Serving schools, bookshops and wholesalers all across Pakistan for years, with the boards and publishers they already trust.</p>
      </div>
      <div>
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= h($base_url ?? '') ?>index.php">Home</a></li>
          <li><a href="<?= h($base_url ?? '') ?>index.php#categories">Book Categories</a></li>
          <li><a href="<?= h($base_url ?? '') ?>index.php#wholesale">Wholesale Discounts</a></li>
          <li><a href="<?= h($base_url ?? '') ?>admin/login.php">Admin Login</a></li>
        </ul>
      </div>
      <div>
        <h4>Contact</h4>
        <ul>
          <li>📞 <?= h($settings['phone']) ?></li>
          <li>💬 <?= h(str_replace('92', '0', $settings['whatsapp_1'])) ?></li>
          <li>💬 <?= h(str_replace('92', '0', $settings['whatsapp_2'])) ?></li>
          <li>✉️ <?= h($settings['email']) ?></li>
        </ul>
      </div>
      <div>
        <h4>Our Locations</h4>
        <ul>
          <li>🏬 Head Office: <?= h($settings['address_main']) ?></li>
          <li>🏬 <?= h($settings['address_branch']) ?></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= h($settings['site_name']) ?>. All rights reserved.</span>
      <span>Importers • Exporters • Wholesale Book Dealers</span>
    </div>
  </div>
</footer>

<a class="float-whatsapp" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
   href="<?= h(whatsapp_link($settings['whatsapp_1'], 'Assalam-o-Alaikum, I would like to place a book order.')) ?>">
   💬
</a>

<script src="<?= h($base_url ?? '') ?>assets/js/main.js"></script>
<!-- Premium animation libraries (loaded from CDN; premium.js checks each exists before using it) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="<?= h($base_url ?? '') ?>assets/js/premium.js"></script>
</body>
</html>
