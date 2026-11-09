<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Incorrect username or password. Please try again.';
    }
}

$settings = get_settings($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — <?= h($settings['site_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { display:flex; align-items:center; justify-content:center; min-height:100vh; background: linear-gradient(160deg, var(--navy-deep), var(--navy) 60%, var(--royal)); }
  .login-card { background:#fff; width: 100%; max-width: 380px; padding: 40px 34px; border-radius: 18px; box-shadow: var(--shadow-lg); }
  .login-card h1 { font-size: 1.3rem; text-align:center; margin-bottom: 4px; }
  .login-card p.sub { text-align:center; color: var(--ink-soft); font-size:.86rem; margin-bottom: 26px; }
  .form-group { margin-bottom: 16px; }
  .form-group label { display:block; font-weight:700; font-size:.85rem; margin-bottom:6px; color: var(--navy); }
  .form-group input { width:100%; padding: 12px 14px; border:1px solid #d9dced; border-radius: 10px; font-size:.95rem; }
  .login-card .btn { width:100%; justify-content:center; margin-top: 8px; }
  .alert-error { background:#f5e2e2; color:#a4262c; padding: 10px 14px; border-radius: 10px; font-size:.86rem; margin-bottom:18px; font-weight:600; }
</style>
</head>
<body>
  <div class="login-card">
    <?php if (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
      <img src="../assets/images/logo.png" style="width:70px;height:70px;border-radius:50%;display:block;margin:0 auto 16px;box-shadow:0 0 0 2px var(--navy), 0 0 0 3px var(--gold-soft);object-fit:cover;">
    <?php endif; ?>
    <h1>Admin Panel</h1>
    <p class="sub"><?= h($settings['site_name']) ?></p>
    <?php if ($error): ?><div class="alert-error"><?= h($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-navy">Log In</button>
    </form>
  </div>
</body>
</html>
