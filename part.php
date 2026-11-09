<?php
require_once __DIR__ . '/config/db.php';
$base_url = '';

$cat_slug = $_GET['cat'] ?? '';
$part_slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare(
    "SELECT c.*, m.name AS main_category_name, m.slug AS main_category_slug
     FROM categories c JOIN main_categories m ON m.id = c.main_category_id
     WHERE c.slug = ? AND c.status = 'active'"
);
$stmt->execute([$cat_slug]);
$category = $stmt->fetch();

if (!$category) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM parts WHERE slug = ? AND category_id = ? AND status = 'active'");
$stmt->execute([$part_slug, $category['id']]);
$part = $stmt->fetch();

if (!$part) { header('Location: category.php?slug=' . urlencode($cat_slug)); exit; }

$page_title = $part['name'] . ' — ' . $category['name'];
$settings = get_settings($pdo);

$stmt = $pdo->prepare("SELECT * FROM books WHERE part_id = ? AND status = 'active' ORDER BY sort_order, title");
$stmt->execute([$part['id']]);
$books = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="page-banner-text">
      <div class="breadcrumb" style="color:#cfd9f4;padding-top:0;">
        <a href="index.php" style="color:#ffd66b;">Home</a><span>/</span>
        <a href="main-category.php?slug=<?= h($category['main_category_slug']) ?>" style="color:#ffd66b;"><?= h($category['main_category_name']) ?></a><span>/</span>
        <a href="category.php?slug=<?= h($category['slug']) ?>" style="color:#ffd66b;"><?= h($category['name']) ?></a><span>/</span>
        <?= h($part['name']) ?>
      </div>
      <h1 style="margin-top:14px;"><?= h($part['name']) ?></h1>
      <?php if (!empty($part['description'])): ?>
        <p><?= h($part['description']) ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

<section>
  <div class="container">
    <?php if (count($books) === 0): ?>
      <div class="empty-state reveal">
        <h3>No books added here yet</h3>
        <p>We're updating this section. Message us on WhatsApp and we'll confirm what's currently in stock.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-4">
        <?php foreach ($books as $i => $book):
          $rating = (int)$book['rating'];
          $theme = ($i % 6) + 1;
          $msg = "Assalam-o-Alaikum, I would like to order this book:\n\"" . $book['title'] . "\"" . (!empty($book['publisher']) ? ' (' . $book['publisher'] . ')' : '') . ($category ? ' — ' . $category['name'] . ' / ' . $part['name'] : '');
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
              <a class="btn btn-whatsapp" target="_blank" rel="noopener" href="<?= h(whatsapp_link($settings['whatsapp_1'], $msg)) ?>">
                💬 Order on WhatsApp
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
