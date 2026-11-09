<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Main Categories';

$message = '';
$error = '';
$edit_main = null;

$upload_dir = __DIR__ . '/../uploads/main_categories/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// --- Handle delete ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM main_categories WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: main_categories.php?msg=deleted');
    exit;
}

// --- Handle add/edit form submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] === 'hidden' ? 'hidden' : 'active';
    $id = (int)($_POST['id'] ?? 0);

    if ($name === '') {
        $error = 'Main category name is required.';
    } else {
        $slug = make_slug($name);

        // handle image upload
        $image_path = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $filename = 'main_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image_path = 'uploads/main_categories/' . $filename;
                }
            } else {
                $error = 'Image must be JPG, PNG or WEBP.';
            }
        }

        if (!$error) {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE main_categories SET name=?, slug=?, description=?, image=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$name, $slug, $description, $image_path, $sort_order, $status, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO main_categories (name, slug, description, image, sort_order, status) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$name, $slug, $description, $image_path, $sort_order, $status]);
            }
            header('Location: main_categories.php?msg=saved');
            exit;
        }
    }
}

// --- Load main category to edit ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM main_categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_main = $stmt->fetch();
}

if (($_GET['msg'] ?? '') === 'saved') $message = 'Main category saved successfully.';
if (($_GET['msg'] ?? '') === 'deleted') $message = 'Main category deleted.';

$main_categories = $pdo->query(
    "SELECT m.*, (SELECT COUNT(*) FROM categories c WHERE c.main_category_id = m.id) AS category_count
     FROM main_categories m ORDER BY m.sort_order, m.name"
)->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topline">
  <h2 style="margin:0;">Main Categories</h2>
</div>

<div class="alert" style="background:#eef1fb;color:var(--navy);">
  These are the 3 top-level types shown on the homepage — <b>Publisher</b>, <b>Countries</b> and <b>School</b>.
  Normally you don't need to add more; just upload/update the image and description for each one below.
  Sub-categories, sections and books all go <i>inside</i> these from the other admin pages.
</div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="card">
  <h3 style="margin-bottom:16px;"><?= $edit_main ? 'Edit Main Category' : 'Add New Main Category' ?></h3>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= h($edit_main['id'] ?? '') ?>">
    <input type="hidden" name="existing_image" value="<?= h($edit_main['image'] ?? '') ?>">
    <div class="grid grid-2">
      <div class="form-group">
        <label>Name *</label>
        <input type="text" name="name" required placeholder="e.g. Publisher" value="<?= h($edit_main['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Display Order (lower shows first)</label>
        <input type="number" name="sort_order" value="<?= h($edit_main['sort_order'] ?? 0) ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Short Description</label>
      <textarea name="description" placeholder="Shown under the name on the homepage card"><?= h($edit_main['description'] ?? '') ?></textarea>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label>Card Image *</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        <?php if (!empty($edit_main['image'])): ?>
          <img src="../<?= h($edit_main['image']) ?>" class="thumb-mini" style="margin-top:8px;width:80px;height:80px;">
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="active" <?= ($edit_main['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (visible on site)</option>
          <option value="hidden" <?= ($edit_main['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-navy"><?= $edit_main ? 'Update Main Category' : 'Add Main Category' ?></button>
      <?php if ($edit_main): ?><a href="main_categories.php" class="btn btn-outline" style="color:var(--navy);border-color:#ccc;">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h3 style="margin-bottom:16px;">All Main Categories (<?= count($main_categories) ?>)</h3>
  <table class="admin-table">
    <thead><tr><th></th><th>Name</th><th>Sub-Categories Inside</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($main_categories as $mc): ?>
      <tr>
        <td><?php if ($mc['image']): ?><img src="../<?= h($mc['image']) ?>" class="thumb-mini" style="width:46px;height:46px;"><?php endif; ?></td>
        <td><b><?= h($mc['name']) ?></b><br><small style="color:var(--ink-soft);">/<?= h($mc['slug']) ?></small></td>
        <td><?= (int)$mc['category_count'] ?></td>
        <td><?= (int)$mc['sort_order'] ?></td>
        <td><span class="badge badge-<?= $mc['status'] ?>"><?= h($mc['status']) ?></span></td>
        <td>
          <a href="main_categories.php?edit=<?= $mc['id'] ?>" class="btn btn-navy btn-sm">Edit</a>
          <a href="main_categories.php?delete=<?= $mc['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this main category AND all sub-categories, sections and books inside it? This cannot be undone.');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
