<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Site Settings';

$message = '';
$error = '';

$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? '');
    $tagline = trim($_POST['tagline'] ?? '');
    $whatsapp_1 = preg_replace('/[^0-9]/', '', $_POST['whatsapp_1'] ?? '');
    $whatsapp_2 = preg_replace('/[^0-9]/', '', $_POST['whatsapp_2'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address_main = trim($_POST['address_main'] ?? '');
    $address_branch = trim($_POST['address_branch'] ?? '');
    $boards_publishers = trim($_POST['boards_publishers'] ?? '');
    $logo_path = $_POST['existing_logo'] ?? null;

    if (!empty($_FILES['logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','svg'])) {
            $filename = 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                $logo_path = 'uploads/' . $filename;
            }
        } else {
            $error = 'Logo must be JPG, PNG, WEBP or SVG.';
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE settings SET site_name=?, tagline=?, logo=?, whatsapp_1=?, whatsapp_2=?, phone=?, email=?, address_main=?, address_branch=?, boards_publishers=? WHERE id=1");
        $stmt->execute([$site_name, $tagline, $logo_path, $whatsapp_1, $whatsapp_2, $phone, $email, $address_main, $address_branch, $boards_publishers]);
        $message = 'Settings updated successfully.';
    }
}

$settings = null;
$stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt->fetch();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topline"><h2 style="margin:0;">Site Settings</h2></div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="existing_logo" value="<?= h($settings['logo'] ?? '') ?>">

    <div class="grid grid-2">
      <div class="form-group">
        <label>Site Name</label>
        <input type="text" name="site_name" value="<?= h($settings['site_name']) ?>">
      </div>
      <div class="form-group">
        <label>Logo</label>
        <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg">
        <?php if (!empty($settings['logo'])): ?>
          <img src="../<?= h($settings['logo']) ?>" class="thumb-mini" style="margin-top:8px;width:60px;height:60px;">
        <?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label>Tagline</label>
      <input type="text" name="tagline" value="<?= h($settings['tagline']) ?>">
    </div>

    <div class="grid grid-2">
      <div class="form-group">
        <label>Primary WhatsApp Number (with country code, no + or spaces — e.g. 923002873276)</label>
        <input type="text" name="whatsapp_1" value="<?= h($settings['whatsapp_1']) ?>">
      </div>
      <div class="form-group">
        <label>Secondary WhatsApp Number</label>
        <input type="text" name="whatsapp_2" value="<?= h($settings['whatsapp_2']) ?>">
      </div>
    </div>

    <div class="grid grid-2">
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?= h($settings['phone']) ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="text" name="email" value="<?= h($settings['email']) ?>">
      </div>
    </div>

    <div class="grid grid-2">
      <div class="form-group">
        <label>Head Office Address</label>
        <input type="text" name="address_main" value="<?= h($settings['address_main']) ?>">
      </div>
      <div class="form-group">
        <label>Branch Address</label>
        <input type="text" name="address_branch" value="<?= h($settings['address_branch']) ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Boards / Publishers (comma-separated — shown in the scrolling strip on the homepage)</label>
      <textarea name="boards_publishers"><?= h($settings['boards_publishers']) ?></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-navy">Save Settings</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
