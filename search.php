<?php
require_once __DIR__ . '/config/db.php';
$base_url = '';
$page_title = 'Search Results';
$settings = get_settings($pdo);

$q = trim($_GET['q'] ?? '');
$category_id = (int)($_GET['category'] ?? 0);

$sql = "SELECT b.*, p.name AS part_name, p.slug AS part_slug, c.name AS category_name, c.slug AS category_slug
        FROM books b
        JOIN parts p ON p.id = b.part_id
        JOIN categories c ON c.id = p.category_id
        WHERE b.status = 'active'";
$params = [];

if ($q !== '') {
    $sql .= " AND (b.title LIKE ? OR b.publisher LIKE ?)";
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($category_id > 0) {
    $sql .= " AND c.id = ?";
    $params[] = $category_id;
}
$sql .= " ORDER BY c.name, p.name, b.title";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$categories = $pdo->query(
    "SELECT c.*, m.name AS main_category_name FROM categories c
     JOIN main_categories m ON m.id = c.main_category_id
     WHERE c.status = 'active' ORDER BY m.sort_order, c.name"
)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="page-banner-text">
      <div class="breadcrumb" style="color:#cdd4ee;padding-top:0;">
        <a href="index.php" style="color:#f2cf7e;">Home</a><span>/</span>Search
      </div>
      <h1 style="margin-top:14px;">Search Results</h1>
      <p><?= $q !== '' ? 'Showing results for "' . h($q) . '"' : 'Showing all books' ?><?= $category_id ? ' in the selected category' : '' ?> — <?= count($books) ?> found.</p>
    </div>
  </div>
</div>

<section>
  <div class="container">
    <form class="hero-search" action="search.php" method="GET" style="max-width:640px;margin:0 auto 44px;">
      <select name="category">
        <option value="">All Categories</option>
        <?php $current_group = null; foreach ($categories as $cat):
          if ($cat['main_category_name'] !== $current_group):
            if ($current_group !== null) echo '</optgroup>';
            $current_group = $cat['main_category_name'];
            echo '<optgroup label="' . h($current_group) . '">';
          endif; ?>
          <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
        <?php endforeach; if ($current_group !== null) echo '</optgroup>'; ?>
      </select>
      <input type="text" name="q" placeholder="Search by book title or publisher..." value="<?= h($q) ?>">
      <button type="submit">🔍 Search</button>
    </form>

    <?php if (count($books) === 0): ?>
      <div class="empty-state reveal">
        <h3>No books matched your search</h3>
        <p>Try a different keyword, or message us on WhatsApp and we'll check current stock for you.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-4">
        <?php foreach ($books as $book):
          $rating = (int)$book['rating'];
          $msg = "Assalam-o-Alaikum, I would like to order this book:\n\"" . $book['title'] . "\"" . (!empty($book['publisher']) ? ' (' . $book['publisher'] . ')' : '') . ' — ' . $book['category_name'] . ' / ' . $book['part_name'];
        ?>
          <div class="book-card reveal">
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
              <div class="pub" style="margin-top:-6px;"><?= h($book['category_name']) ?> · <?= h($book['part_name']) ?></div>
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
