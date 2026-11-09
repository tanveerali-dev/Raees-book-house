<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Dashboard';

$counts = [
    'main_categories' => $pdo->query("SELECT COUNT(*) c FROM main_categories")->fetch()['c'],
    'categories'      => $pdo->query("SELECT COUNT(*) c FROM categories")->fetch()['c'],
    'parts'           => $pdo->query("SELECT COUNT(*) c FROM parts")->fetch()['c'],
    'books'           => $pdo->query("SELECT COUNT(*) c FROM books")->fetch()['c'],
];

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topline">
  <div>
    <h2 style="margin-bottom:2px;">Welcome back, <?= h($_SESSION['admin_username']) ?> 👋</h2>
    <p style="color:var(--ink-soft);margin:0;">Here's what's currently live on your website.</p>
  </div>
</div>

<div class="grid grid-4" style="margin-bottom: 10px;">
  <div class="card">
    <div style="font-size:.8rem;color:var(--ink-soft);font-weight:700;text-transform:uppercase;letter-spacing:.03em;">Main Categories</div>
    <div style="font-family:'Fraunces',serif;font-size:2.4rem;color:var(--navy);"><?= (int)$counts['main_categories'] ?></div>
    <a href="main_categories.php" class="btn btn-navy btn-sm" style="margin-top:6px;">Manage →</a>
  </div>
  <div class="card">
    <div style="font-size:.8rem;color:var(--ink-soft);font-weight:700;text-transform:uppercase;letter-spacing:.03em;">Categories</div>
    <div style="font-family:'Fraunces',serif;font-size:2.4rem;color:var(--navy);"><?= (int)$counts['categories'] ?></div>
    <a href="categories.php" class="btn btn-navy btn-sm" style="margin-top:6px;">Manage →</a>
  </div>
  <div class="card">
    <div style="font-size:.8rem;color:var(--ink-soft);font-weight:700;text-transform:uppercase;letter-spacing:.03em;">Sections / Parts</div>
    <div style="font-family:'Fraunces',serif;font-size:2.4rem;color:var(--navy);"><?= (int)$counts['parts'] ?></div>
    <a href="parts.php" class="btn btn-navy btn-sm" style="margin-top:6px;">Manage →</a>
  </div>
  <div class="card">
    <div style="font-size:.8rem;color:var(--ink-soft);font-weight:700;text-transform:uppercase;letter-spacing:.03em;">Books</div>
    <div style="font-family:'Fraunces',serif;font-size:2.4rem;color:var(--navy);"><?= (int)$counts['books'] ?></div>
    <a href="books.php" class="btn btn-navy btn-sm" style="margin-top:6px;">Manage →</a>
  </div>
</div>

<div class="card">
  <h3 style="margin-bottom:14px;">How the site is structured</h3>
  <p style="color:var(--ink-soft);font-size:.92rem;">
    1. <b>Main Categories</b> — the 3 fixed types shown on the homepage: Publisher, Countries, School. Each has its own image (edit these from <a href="main_categories.php">Main Categories</a>).<br>
    2. Inside each Main Category, add one or more <b>Categories</b> (e.g. under Publisher: "Oxford University Press"; under School: "Punjab Textbook Board") — each can also have its own image.<br>
    3. Inside each category, add one or more <b>Sections/Parts</b> (e.g. "Class 1–5", "Class 9–10") — you decide the names.<br>
    4. Inside each section, add <b>Books</b> with a cover image — each one automatically gets a WhatsApp order button on the website.<br>
    5. Use <b>Site Settings</b> to update your WhatsApp numbers, phone, address and logo any time.
  </p>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
