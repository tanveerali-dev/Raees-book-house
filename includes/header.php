<?php
/**
 * Shared header. Expects $pdo to already be available (require config/db.php first).
 * Optional $page_title can be set before including this file.
 */
$settings = get_settings($pdo);
$page_title = isset($page_title) ? $page_title . ' — ' . $settings['site_name'] : $settings['site_name'];

// Data for the "Categories" nav mega-menu (Main Category -> Category -> Section)
$nav_main_cats = $pdo->query("SELECT id, name, slug FROM main_categories WHERE status='active' ORDER BY sort_order, name")->fetchAll();
$nav_cats = $pdo->query("SELECT id, main_category_id, name, slug FROM categories WHERE status='active' ORDER BY sort_order, name")->fetchAll();
$nav_parts = $pdo->query("SELECT id, category_id, name, slug FROM parts WHERE status='active' ORDER BY sort_order, name")->fetchAll();
$nav_cats_by_main = [];
foreach ($nav_cats as $c) { $nav_cats_by_main[$c['main_category_id']][] = $c; }
$nav_parts_by_cat = [];
foreach ($nav_parts as $p) { $nav_parts_by_cat[$p['category_id']][] = $p; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?></title>
<meta name="description" content="<?= h($settings['tagline']) ?> — School textbooks, university press titles, notes & guides at genuine wholesale rates across Pakistan.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h($base_url ?? '') ?>assets/css/style.css">
<?php if (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
<link rel="icon" type="image/png" href="<?= h($base_url ?? '') ?>assets/images/logo.png">
<?php endif; ?>
</head>
<body>

<div class="topbar">
  <div class="container">
    <div class="topbar-links">
      <a href="tel:<?= h(preg_replace('/[^0-9+]/', '', $settings['phone'])) ?>">📞 <?= h($settings['phone']) ?></a>
      <a href="mailto:<?= h($settings['email']) ?>">✉️ <?= h($settings['email']) ?></a>
    </div>
    <div class="topbar-links">
      <span>Wholesale • Import • Export</span>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="container nav-wrap">
    <a href="<?= h($base_url ?? '') ?>index.php" class="brand">
      <div class="brand-logo">
        <?php if (!empty($settings['logo']) && file_exists(__DIR__ . '/../' . $settings['logo'])): ?>
          <img src="<?= h($base_url ?? '') ?><?= h($settings['logo']) ?>" alt="<?= h($settings['site_name']) ?> logo">
        <?php elseif (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
          <img src="<?= h($base_url ?? '') ?>assets/images/logo.png" alt="<?= h($settings['site_name']) ?> logo">
        <?php else: ?>
          RD
        <?php endif; ?>
      </div>
      <div class="brand-text">
        <div class="name"><?= h($settings['site_name']) ?></div>
        <div class="tagline"><?= h($settings['tagline']) ?></div>
      </div>
    </a>

    <nav class="main-nav">
      <ul>
        <li><a href="<?= h($base_url ?? '') ?>index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a></li>
        <?php foreach ($nav_main_cats as $mc): $subs = $nav_cats_by_main[$mc['id']] ?? []; ?>
        <li class="nav-mega">
          <a href="<?= h($base_url ?? '') ?>main-category.php?slug=<?= h($mc['slug']) ?>" class="mega-trigger"><?= h($mc['name']) ?></a>
          <?php if ($subs): ?>
          <div class="mega-panel">
            <ul class="mega-col-main">
              <?php foreach ($subs as $cat): $secs = $nav_parts_by_cat[$cat['id']] ?? []; ?>
                <li>
                  <a href="<?= h($base_url ?? '') ?>category.php?slug=<?= h($cat['slug']) ?>">
                    <?= h($cat['name']) ?>
                    <?php if ($secs): ?><span class="mega-arrow">›</span><?php endif; ?>
                  </a>
                  <?php if ($secs): ?>
                  <ul class="mega-col-sub">
                    <?php foreach ($secs as $part): ?>
                      <li><a href="<?= h($base_url ?? '') ?>part.php?slug=<?= h($part['slug']) ?>&cat=<?= h($cat['slug']) ?>"><?= h($part['name']) ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php if (!$nav_main_cats): ?>
        <li><a href="<?= h($base_url ?? '') ?>index.php#categories" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? '' : '' ?>">Categories</a></li>
        <?php endif; ?>
        <li><a href="<?= h($base_url ?? '') ?>index.php#wholesale">Wholesale Discounts</a></li>
        <li><a href="<?= h($base_url ?? '') ?>index.php#contact">Contact</a></li>
      </ul>
    </nav>

    <div class="nav-cta">
      <a class="btn btn-whatsapp" target="_blank" rel="noopener" href="<?= h(whatsapp_link($settings['whatsapp_1'], 'Assalam-o-Alaikum, I would like to know more about your wholesale book prices.')) ?>">
        💬 <span class="long">WhatsApp Us</span>
      </a>
      <button class="menu-toggle" aria-label="Toggle menu">☰</button>
    </div>
  </div>
</header>