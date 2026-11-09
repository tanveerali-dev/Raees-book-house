<?php
require_once __DIR__ . '/config/db.php';
$base_url = '';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM main_categories WHERE slug = ? AND status = 'active'");
$stmt->execute([$slug]);
$main_category = $stmt->fetch();

if (!$main_category) {
    header('Location: index.php');
    exit;
}

$page_title = $main_category['name'];

$stmt = $pdo->prepare(
    "SELECT c.*, (SELECT COUNT(*) FROM parts p WHERE p.category_id = c.id AND p.status='active') AS part_count
     FROM categories c WHERE c.main_category_id = ? AND c.status = 'active' ORDER BY c.sort_order, c.name"
);
$stmt->execute([$main_category['id']]);
$categories = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="page-banner-text">
      <div class="breadcrumb" style="color:#cfd9f4;padding-top:0;">
        <a href="index.php" style="color:#ffd66b;">Home</a><span>/</span><?= h($main_category['name']) ?>
      </div>
      <h1 style="margin-top:14px;"><?= h($main_category['name']) ?></h1>
      <?php if (!empty($main_category['description'])): ?>
        <p><?= h($main_category['description']) ?></p>
      <?php endif; ?>
    </div>
    <?php if (!empty($main_category['image'])): ?>
      <div class="page-banner-media">
        <img src="<?= h($main_category['image']) ?>" alt="<?= h($main_category['name']) ?>">
      </div>
    <?php endif; ?>
  </div>
</div>

<section>
  <div class="container">
    <?php if (count($categories) === 0): ?>
      <div class="empty-state reveal">
        <h3>Nothing added here yet</h3>
        <p>This category doesn't have any sub-categories yet. Please check back soon or ask us on WhatsApp what's available.</p>
      </div>
    <?php else: ?>
      <div class="section-head reveal" style="margin-bottom:34px;">
        <span class="eyebrow">Inside <?= h($main_category['name']) ?></span>
        <h2>Choose a category to see the sections &amp; books</h2>
        <div class="gold-rule"></div>
      </div>
      <div class="grid grid-4">
        <?php foreach ($categories as $i => $cat): $theme = ($i % 6) + 1; ?>
          <a href="category.php?slug=<?= h($cat['slug']) ?>" class="collection-card theme-<?= $theme ?> reveal">
            <?php if (!empty($cat['image'])): ?>
              <div class="collection-card-media"><img src="<?= h($cat['image']) ?>" alt="<?= h($cat['name']) ?>" loading="lazy"></div>
            <?php else: ?>
              <div class="collection-icon">📁</div>
            <?php endif; ?>
            <h3><?= h($cat['name']) ?></h3>
            <p><?= h($cat['description'] ?: 'View all sections and titles available in this category.') ?></p>
            <span class="btn"><?= (int)$cat['part_count'] ?> Section<?= $cat['part_count'] == 1 ? '' : 's' ?> →</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
