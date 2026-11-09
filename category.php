<?php
require_once __DIR__ . '/config/db.php';
$base_url = '';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare(
    "SELECT c.*, m.name AS main_category_name, m.slug AS main_category_slug
     FROM categories c JOIN main_categories m ON m.id = c.main_category_id
     WHERE c.slug = ? AND c.status = 'active'"
);
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit;
}

$page_title = $category['name'];

$stmt = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM books b WHERE b.part_id = p.id AND b.status='active') AS book_count
                        FROM parts p WHERE p.category_id = ? AND p.status = 'active' ORDER BY p.sort_order, p.name");
$stmt->execute([$category['id']]);
$parts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="page-banner-text">
      <div class="breadcrumb" style="color:#cfd9f4;padding-top:0;">
        <a href="index.php" style="color:#ffd66b;">Home</a><span>/</span>
        <a href="main-category.php?slug=<?= h($category['main_category_slug']) ?>" style="color:#ffd66b;"><?= h($category['main_category_name']) ?></a><span>/</span>
        <?= h($category['name']) ?>
      </div>
      <h1 style="margin-top:14px;"><?= h($category['name']) ?></h1>
      <?php if (!empty($category['description'])): ?>
        <p><?= h($category['description']) ?></p>
      <?php endif; ?>
    </div>
    <?php if (!empty($category['image'])): ?>
      <div class="page-banner-media">
        <img src="<?= h($category['image']) ?>" alt="<?= h($category['name']) ?>">
      </div>
    <?php endif; ?>
  </div>
</div>

<section>
  <div class="container">
    <?php if (count($parts) === 0): ?>
      <div class="empty-state reveal">
        <h3>Nothing added here yet</h3>
        <p>This category doesn't have any sections yet. Please check back soon or ask us on WhatsApp what's available.</p>
      </div>
    <?php else: ?>
      <div class="section-head reveal" style="margin-bottom:34px;">
        <span class="eyebrow">Sections inside <?= h($category['name']) ?></span>
        <h2>Choose a section to see the books</h2>
        <div class="gold-rule"></div>
      </div>
      <div class="grid grid-4">
        <?php foreach ($parts as $part): ?>
          <a href="part.php?slug=<?= h($part['slug']) ?>&cat=<?= h($category['slug']) ?>" class="collection-card reveal">
            <div class="collection-icon">📁</div>
            <h3><?= h($part['name']) ?></h3>
            <p><?= h($part['description'] ?: 'View all titles available in this section.') ?></p>
            <span class="btn"><?= (int)$part['book_count'] ?> Book<?= $part['book_count'] == 1 ? '' : 's' ?> →</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
