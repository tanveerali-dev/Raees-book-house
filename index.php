<?php
require_once __DIR__ . '/config/db.php';
$base_url = '';
$page_title = 'Home';

$main_categories = $pdo->query("SELECT * FROM main_categories WHERE status = 'active' ORDER BY sort_order, name")->fetchAll();
$categories = $pdo->query(
    "SELECT c.*, m.name AS main_category_name FROM categories c
     JOIN main_categories m ON m.id = c.main_category_id
     WHERE c.status = 'active' ORDER BY m.sort_order, c.sort_order, c.name"
)->fetchAll();
$settings = get_settings($pdo);
$boards = array_filter(array_map('trim', explode(',', $settings['boards_publishers'] ?? '')));

// Icon cycled across the category cards (color stays consistent navy/gold — only the icon changes)
$tile_icons = ['📘', '📗', '📙', '📕', '📔'];

// A handful of featured books for the homepage, to show off the new
// hover-to-see-rating-and-order effect right away.
$featured_books = $pdo->query(
    "SELECT b.*, p.name AS part_name, c.name AS category_name, c.slug AS category_slug, p.slug AS part_slug
     FROM books b JOIN parts p ON p.id = b.part_id JOIN categories c ON c.id = p.category_id
     WHERE b.status = 'active' ORDER BY b.is_wholesale_special DESC, b.created_at DESC LIMIT 4"
)->fetchAll();

// Sample testimonials — replace with real customer feedback any time.
$reviews = [
    ['name' => 'M. Asad Iqbal', 'location' => 'Lahore, Pakistan', 'rating' => 5,
     'quote' => 'We\'ve bought our entire school\'s textbook stock from Raees Doultana for three years running. Prices are always genuinely wholesale, and orders reach our branch in Khanewal within days.'],
    ['name' => 'Ayesha Farooq', 'location' => 'Karachi, Pakistan', 'rating' => 5,
     'quote' => 'Ordering was as easy as sending a WhatsApp message with the book covers I wanted. No confusing forms, no waiting on hold — just quick, honest service.'],
    ['name' => 'Bilal Ahmed', 'location' => 'Manchester, United Kingdom', 'rating' => 5,
     'quote' => 'I import Urdu curriculum books for a weekend school here in the UK. Raees Doultana has been reliable for export packing and paperwork every single time.'],
    ['name' => 'Fatima Noor', 'location' => 'Dubai, UAE', 'rating' => 4,
     'quote' => 'Good range of Oxford and Cambridge titles at prices well below what we were paying locally. Communication over WhatsApp made the whole process simple.'],
    ['name' => 'Hamza Sheikh', 'location' => 'Multan, Pakistan', 'rating' => 5,
     'quote' => 'As a bookshop owner, the wholesale discount genuinely helps my margins. Been dealing with them since Urdu Bazar days — trustworthy people.'],
    ['name' => 'Sarah Thompson', 'location' => 'Toronto, Canada', 'rating' => 5,
     'quote' => 'Shipped a full set of Pakistan Studies and Urdu textbooks to our community library abroad. Everything arrived well packed and exactly as ordered.'],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div id="hero-canvas" aria-hidden="true"></div>
  <div class="container">
    <div class="hero-copy">
      <span class="eyebrow">Importers • Exporters • Wholesale Book Dealers</span>
      <h1>The next chapter in <span class="hl">school &amp; university books</span> starts here.</h1>
      <p class="lead">From Punjab &amp; Federal board textbooks to Oxford and Cambridge University Press titles, notes and guides — we supply bookshops, schools and libraries across the country, direct from Urdu Bazar, Lahore.</p>

      <form class="hero-search" action="search.php" method="GET">
        <select name="category">
          <option value="">All Categories</option>
          <?php $current_group = null; foreach ($categories as $cat):
            if ($cat['main_category_name'] !== $current_group):
              if ($current_group !== null) echo '</optgroup>';
              $current_group = $cat['main_category_name'];
              echo '<optgroup label="' . h($current_group) . '">';
            endif; ?>
            <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
          <?php endforeach; if ($current_group !== null) echo '</optgroup>'; ?>
        </select>
        <input type="text" name="q" placeholder="Search by book title or publisher...">
        <button type="submit">🔍 Search</button>
      </form>

      <div class="hero-ctas">
        <a href="#categories" class="btn btn-gold">📚 Browse Categories</a>
        <a href="<?= h(whatsapp_link($settings['whatsapp_1'], 'Assalam-o-Alaikum, I would like to place a bulk book order.')) ?>" target="_blank" rel="noopener" class="btn btn-outline">💬 Order on WhatsApp</a>
      </div>
      <div class="stat-row">
        <div class="stat"><b>2</b><span>Branches in Lahore &amp; Khanewal</span></div>
        <div class="stat"><b>7+</b><span>Boards &amp; Publishers Stocked</span></div>
        <div class="stat"><b>100%</b><span>Genuine Wholesale Pricing</span></div>
      </div>
    </div>
    <div class="shelf-art">
      <div class="shelf-books">
        <div class="spine"></div><div class="spine"></div><div class="spine"></div><div class="spine"></div>
        <div class="spine"></div><div class="spine"></div><div class="spine"></div><div class="spine"></div>
      </div>
      <div class="shelf-board"></div>
    </div>
  </div>
</section>


<?php if (!empty($boards)): ?>
<div class="marquee-wrap">
  <div class="marquee-track">
    <?php foreach ($boards as $b): ?>
      <span>📘 <?= h($b) ?></span>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<section id="categories">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">What we stock</span>
      <h2>Browse by category</h2>
      <div class="gold-rule"></div>
      <p>Pick a category below to see the publishers, countries or school boards curated by our team — updated regularly.</p>
    </div>

    <?php if (count($main_categories) === 0): ?>
      <div class="empty-state reveal">
        <h3>Categories coming soon</h3>
        <p>Our catalogue is being set up. Please check back shortly, or message us directly on WhatsApp for current stock.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($main_categories as $i => $mc): $icon = $tile_icons[$i % count($tile_icons)]; ?>
          <a href="main-category.php?slug=<?= h($mc['slug']) ?>" class="collection-card reveal">
            <?php if (!empty($mc['image'])): ?>
              <div class="collection-card-media"><img src="<?= h($mc['image']) ?>" alt="<?= h($mc['name']) ?>" loading="lazy"></div>
            <?php else: ?>
              <div class="collection-icon"><?= $icon ?></div>
            <?php endif; ?>
            <h3><?= h($mc['name']) ?></h3>
            <p><?= h($mc['description'] ?: 'Explore the full range of titles available in this category.') ?></p>
            <span class="btn">View Categories →</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (count($featured_books) > 0): ?>
<section style="background:var(--paper);">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Fresh on the shelf</span>
      <h2>Featured books</h2>
      <div class="gold-rule"></div>
      <p>Hover over any cover to see its rating and order it instantly — or tap it on mobile.</p>
    </div>
    <div class="grid grid-4">
      <?php foreach ($featured_books as $i => $book):
        $rating = (int)$book['rating'];
        $theme = ($i % 6) + 1;
        $msg = "Assalam-o-Alaikum, I would like to order this book:\n\"" . $book['title'] . "\"" . (!empty($book['publisher']) ? ' (' . $book['publisher'] . ')' : '') . ' — ' . $book['category_name'] . ' / ' . $book['part_name'];
      ?>
        <div class="book-card theme-<?= $theme ?> reveal">
          <div class="book-thumb">
            <?php if ($book['is_wholesale_special']): ?><span class="book-badge">Wholesale Special</span><?php endif; ?>
            <img src="<?= h($book['image']) ?>" alt="<?= h($book['title']) ?>" loading="lazy">
            <div class="book-overlay">
              <div class="overlay-stars"><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?></div>
              <div class="overlay-rating-text"><?= $rating ?>/5 rating</div>
              <a class="btn btn-whatsapp" target="_blank" rel="noopener" href="<?= h(whatsapp_link($settings['whatsapp_1'], $msg)) ?>">💬 Order Now</a>
            </div>
          </div>
          <div class="book-info">
            <h4><?= h($book['title']) ?></h4>
            <?php if (!empty($book['publisher'])): ?><div class="pub"><?= h($book['publisher']) ?></div><?php endif; ?>
            <?php if (!empty($book['price'])): ?><div class="price">Rs. <?= h(number_format($book['price'], 0)) ?></div><?php endif; ?>
            <a class="btn btn-whatsapp" target="_blank" rel="noopener" href="<?= h(whatsapp_link($settings['whatsapp_1'], $msg)) ?>">💬 Order on WhatsApp</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section id="wholesale">
  <div class="container">
    <div class="wholesale-banner reveal">
      <div>
        <h2>Special discounts for wholesalers &amp; bookshop owners</h2>
        <p>Buying in bulk for your shop, school or library? Message us your order list on WhatsApp and get genuine wholesale rates — no middlemen, no inflated prices.</p>
      </div>
      <a class="btn btn-gold" target="_blank" rel="noopener" href="<?= h(whatsapp_link($settings['whatsapp_1'], 'Assalam-o-Alaikum, I am a wholesaler/bookshop owner and would like to know your special discount rates.')) ?>">
        💬 Get Wholesale Rates
      </a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Why order with us</span>
      <h2>Built on trust, run for the trade</h2>
      <div class="gold-rule"></div>
    </div>
    <div class="grid grid-3">
      <div class="book-card reveal" style="padding:26px;">
        <h3 style="font-size:1.1rem;">Board-Approved Stock</h3>
        <p style="color:var(--ink-soft);font-size:.92rem;">Punjab Textbook Board and Federal Textbook Board titles alongside leading university presses, always current edition.</p>
      </div>
      <div class="book-card reveal" style="padding:26px;">
        <h3 style="font-size:1.1rem;">Order Straight from a Photo</h3>
        <p style="color:var(--ink-soft);font-size:.92rem;">Hover over any cover to see its rating, tap the WhatsApp button, and your order message is ready to send — no forms, no delays.</p>
      </div>
      <div class="book-card reveal" style="padding:26px;">
        <h3 style="font-size:1.1rem;">Two Locations, One Network</h3>
        <p style="color:var(--ink-soft);font-size:.92rem;">Head office in Urdu Bazar, Lahore, with a branch in Khanewal — so orders move quickly across central Punjab.</p>
      </div>
    </div>
  </div>
</section>

<section id="reviews" style="background:var(--paper);">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Customer reviews</span>
      <h2>Trusted by schools &amp; bookshops, near and far</h2>
      <div class="gold-rule"></div>
      <p>A few words from customers across Pakistan and abroad who order with us regularly.</p>
    </div>
    <div class="grid grid-3">
      <?php
      foreach ($reviews as $i => $r):
          $initial = strtoupper(substr($r['name'], 0, 1));
          $stars = '';
          for ($s = 1; $s <= 5; $s++) { $stars .= '<span>' . ($s <= $r['rating'] ? '★' : '☆') . '</span>'; }
      ?>
        <div class="review-card reveal">
          <div class="review-stars"><?= $stars ?></div>
          <p class="review-quote">"<?= h($r['quote']) ?>"</p>
          <div class="review-person">
            <div class="review-avatar"><?= h($initial) ?></div>
            <div class="who">
              <b><?= h($r['name']) ?></b>
              <span><?= h($r['location']) ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="gallery">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">A look inside</span>
      <h2>Our gallery</h2>
      <div class="gold-rule"></div>
      <p>Photos from our Urdu Bazar shop and stock. Drop your own images into <code>/assets/images/gallery/</code> (named 1.jpg – 8.jpg) to replace these placeholders any time.</p>
    </div>
    <div class="gallery-grid">
      <?php for ($i = 1; $i <= 8; $i++): $img_path = __DIR__ . "/assets/images/gallery/{$i}.jpg"; ?>
        <div class="gallery-item reveal">
          <?php if (file_exists($img_path)): ?>
            <img src="assets/images/gallery/<?= $i ?>.jpg" alt="Rais Doultana Book House photo <?= $i ?>" loading="lazy">
          <?php else: ?>
            <div class="gallery-placeholder">📚</div>
          <?php endif; ?>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
