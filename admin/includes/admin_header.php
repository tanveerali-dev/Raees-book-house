<?php $settings = get_settings($pdo); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title ?? 'Admin') ?> — Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { background: #f1f3f9; }
  .admin-wrap { display: flex; min-height: 100vh; }
  .admin-sidebar {
    width: 240px; background: var(--navy-deep); color: #cfd9f4; flex-shrink: 0;
    padding: 26px 18px; position: sticky; top: 0; height: 100vh; overflow-y: auto;
  }
  .admin-sidebar .brand-mini { font-family:'Fraunces',serif; color:#fff; font-size:1.1rem; margin-bottom: 26px; padding: 0 8px; }
  .admin-sidebar a {
    display: block; padding: 11px 14px; border-radius: 10px; color: #cfd9f4; font-weight: 600; font-size: .92rem; margin-bottom: 4px;
  }
  .admin-sidebar a:hover, .admin-sidebar a.active { background: rgba(255,255,255,.1); color: #fff; }
  .admin-main { flex: 1; padding: 34px 40px; max-width: 1100px; }
  .admin-topline { display:flex; justify-content:space-between; align-items:center; margin-bottom: 28px; flex-wrap: wrap; gap: 14px; }
  .card { background:#fff; border-radius: var(--radius); padding: 26px; box-shadow: var(--shadow); margin-bottom: 26px; }
  table.admin-table { width: 100%; border-collapse: collapse; }
  table.admin-table th, table.admin-table td { text-align: left; padding: 12px 10px; border-bottom: 1px solid #eee; font-size: .92rem; vertical-align: middle; }
  table.admin-table th { color: var(--ink-soft); font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; }
  .form-group { margin-bottom: 18px; }
  .form-group label { display:block; font-weight: 700; font-size: .85rem; margin-bottom: 6px; color: var(--navy); }
  .form-group input[type=text], .form-group input[type=number], .form-group input[type=password], .form-group textarea, .form-group select {
    width: 100%; padding: 11px 14px; border: 1px solid #d9dced; border-radius: 10px; font-family: inherit; font-size: .95rem; background: #fbfbfe;
  }
  .form-group textarea { min-height: 90px; resize: vertical; }
  .form-actions { display:flex; gap: 12px; margin-top: 22px; }
  .badge { padding: 3px 10px; border-radius: 999px; font-size: .74rem; font-weight: 700; }
  .badge-active { background: #e2f5e9; color: #1a7a41; }
  .badge-hidden { background: #f5e2e2; color: #a4262c; }
  .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: .9rem; font-weight: 600; }
  .alert-success { background: #e2f5e9; color: #1a7a41; }
  .alert-error { background: #f5e2e2; color: #a4262c; }
  .thumb-mini { width: 46px; height: 60px; object-fit: cover; border-radius: 6px; background:#eee; }
  .btn-sm { padding: 7px 14px; font-size: .8rem; }
  .btn-danger { background: #b3261e; color: #fff; }
  @media (max-width: 800px) {
    .admin-wrap { flex-direction: column; }
    .admin-sidebar { width: 100%; height: auto; position: static; }
    .admin-main { padding: 24px 18px; }
  }
</style>
</head>
<body>
<div class="admin-wrap">
  <aside class="admin-sidebar">
    <div class="brand-mini" style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($settings['logo']) && file_exists(__DIR__ . '/../../' . $settings['logo'])): ?>
        <img src="../<?= h($settings['logo']) ?>" style="width:40px;height:40px;border-radius:50%;box-shadow:0 0 0 2px var(--gold-soft);object-fit:cover;">
      <?php elseif (file_exists(__DIR__ . '/../../assets/images/logo.png')): ?>
        <img src="../assets/images/logo.png" style="width:40px;height:40px;border-radius:50%;box-shadow:0 0 0 2px var(--gold-soft);object-fit:cover;">
      <?php endif; ?>
      <span><?= h($settings['site_name']) ?><br><small style="font-family:'Manrope',sans-serif;font-size:.7rem;color:#9fadd8;">Admin Panel</small></span>
    </div>
    <nav>
      <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>">📊 Dashboard</a>
      <a href="main_categories.php" class="<?= basename($_SERVER['PHP_SELF'])=='main_categories.php'?'active':'' ?>">🏛️ Main Categories</a>
      <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF'])=='categories.php'?'active':'' ?>">🗂️ Categories</a>
      <a href="parts.php" class="<?= basename($_SERVER['PHP_SELF'])=='parts.php'?'active':'' ?>">📁 Sections / Parts</a>
      <a href="books.php" class="<?= basename($_SERVER['PHP_SELF'])=='books.php'?'active':'' ?>">📖 Books</a>
      <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php'?'active':'' ?>">⚙️ Site Settings</a>
      <a href="../index.php" target="_blank">🌐 View Website</a>
      <a href="logout.php" style="color:#f5a3a3;">🚪 Logout</a>
    </nav>
  </aside>
  <main class="admin-main">
