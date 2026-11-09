<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Categories';

$message = '';
$error = '';
$edit_category = null;

$upload_dir = __DIR__ . '/../uploads/categories/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// --- Handle delete ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: categories.php?msg=deleted');
    exit;
}

// --- Handle add/edit form submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $main_category_id = (int)($_POST['main_category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] === 'hidden' ? 'hidden' : 'active';
    $id = (int)($_POST['id'] ?? 0);

    if ($name === '' || $main_category_id === 0) {
        $error = 'Category name and Main Category are both required.';
    } else {
        $slug = make_slug($name);

        // handle image upload
        $image_path = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $filename = 'cat_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image_path = 'uploads/categories/' . $filename;
                }
            } else {
                $error = 'Image must be JPG, PNG or WEBP.';
            }
        }

        if (!$error) {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE categories SET main_category_id=?, name=?, slug=?, description=?, image=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$main_category_id, $name, $slug, $description, $image_path, $sort_order, $status, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (main_category_id, name, slug, description, image, sort_order, status) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$main_category_id, $name, $slug, $description, $image_path, $sort_order, $status]);
            }
            header('Location: categories.php?msg=saved');
            exit;
        }
    }
}

// --- Load category to edit ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_category = $stmt->fetch();
}

if (($_GET['msg'] ?? '') === 'saved') $message = 'Category saved successfully.';
if (($_GET['msg'] ?? '') === 'deleted') $message = 'Category deleted.';

$main_categories = $pdo->query("SELECT * FROM main_categories ORDER BY sort_order, name")->fetchAll();
$categories = $pdo->query(
    "SELECT c.*, m.name AS main_category_name FROM categories c
     JOIN main_categories m ON m.id = c.main_category_id
     ORDER BY m.sort_order, c.sort_order, c.name"
)->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topline">
  <h2 style="margin:0;">Categories</h2>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<?php if (count($main_categories) === 0): ?>
  <div class="alert alert-error">You need at least one Main Category before adding a category. <a href="main_categories.php">Set up Main Categories first →</a></div>
<?php endif; ?>

<div class="card">
  <h3 style="margin-bottom:16px;"><?= $edit_category ? 'Edit Category' : 'Add New Category' ?></h3>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= h($edit_category['id'] ?? '') ?>">
    <input type="hidden" name="existing_image" value="<?= h($edit_category['image'] ?? '') ?>">
    <div class="grid grid-2">
      <div class="form-group">
        <label>Main Category *</label>
        <select name="main_category_id" class="searchable-select" data-placeholder="Type to search main category…" required>
          <option value="">— Select main category —</option>
          <?php foreach ($main_categories as $mc): ?>
            <option value="<?= $mc['id'] ?>" <?= (($edit_category['main_category_id'] ?? '') == $mc['id']) ? 'selected' : '' ?>><?= h($mc['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Category Name *</label>
        <input type="text" name="name" required placeholder="e.g. Punjab Textbook Board" value="<?= h($edit_category['name'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Display Order (lower shows first)</label>
      <input type="number" name="sort_order" value="<?= h($edit_category['sort_order'] ?? 0) ?>">
    </div>
    <div class="form-group">
      <label>Short Description</label>
      <textarea name="description" placeholder="Shown under the category name on the homepage"><?= h($edit_category['description'] ?? '') ?></textarea>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label>Cover Image (optional)</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        <?php if (!empty($edit_category['image'])): ?>
          <img src="../<?= h($edit_category['image']) ?>" class="thumb-mini" style="margin-top:8px;">
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="active" <?= ($edit_category['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (visible on site)</option>
          <option value="hidden" <?= ($edit_category['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-navy"><?= $edit_category ? 'Update Category' : 'Add Category' ?></button>
      <?php if ($edit_category): ?><a href="categories.php" class="btn btn-outline" style="color:var(--navy);border-color:#ccc;">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h3 style="margin-bottom:16px;">All Categories (<?= count($categories) ?>)</h3>
  <table class="admin-table">
    <thead><tr><th></th><th>Name</th><th>Main Category</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($categories as $cat): ?>
      <tr>
        <td><?php if ($cat['image']): ?><img src="../<?= h($cat['image']) ?>" class="thumb-mini"><?php endif; ?></td>
        <td><b><?= h($cat['name']) ?></b><br><small style="color:var(--ink-soft);">/<?= h($cat['slug']) ?></small></td>
        <td><?= h($cat['main_category_name']) ?></td>
        <td><?= (int)$cat['sort_order'] ?></td>
        <td><span class="badge badge-<?= $cat['status'] ?>"><?= h($cat['status']) ?></span></td>
        <td>
          <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-navy btn-sm">Edit</a>
          <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this category and all its sections/books inside it?');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (count($categories) === 0): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px;">No categories yet. Add your first one above.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>