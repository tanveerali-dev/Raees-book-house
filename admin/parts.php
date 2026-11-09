<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Sections / Parts';

$message = '';
$error = '';
$edit_part = null;

// --- Handle delete ---
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM parts WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: parts.php?msg=deleted');
    exit;
}

// --- Handle add/edit form submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] === 'hidden' ? 'hidden' : 'active';
    $id = (int)($_POST['id'] ?? 0);

    if ($name === '' || $category_id === 0) {
        $error = 'Section name and parent category are both required.';
    } else {
        $slug = make_slug($name);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE parts SET category_id=?, name=?, slug=?, description=?, sort_order=?, status=? WHERE id=?");
            $stmt->execute([$category_id, $name, $slug, $description, $sort_order, $status, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO parts (category_id, name, slug, description, sort_order, status) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$category_id, $name, $slug, $description, $sort_order, $status]);
        }
        header('Location: parts.php?msg=saved');
        exit;
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM parts WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_part = $stmt->fetch();
}

if (($_GET['msg'] ?? '') === 'saved') $message = 'Section saved successfully.';
if (($_GET['msg'] ?? '') === 'deleted') $message = 'Section deleted.';

$categories = $pdo->query(
    "SELECT c.*, m.name AS main_category_name FROM categories c
     JOIN main_categories m ON m.id = c.main_category_id
     ORDER BY m.sort_order, c.sort_order, c.name"
)->fetchAll();
$parts = $pdo->query(
    "SELECT p.*, c.name AS category_name, m.name AS main_category_name
     FROM parts p JOIN categories c ON c.id = p.category_id JOIN main_categories m ON m.id = c.main_category_id
     ORDER BY m.sort_order, c.name, p.sort_order, p.name"
)->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topline"><h2 style="margin:0;">Sections / Parts</h2></div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<?php if (count($categories) === 0): ?>
  <div class="alert alert-error">You need to create at least one Category before adding sections. <a href="categories.php">Add a category first →</a></div>
<?php endif; ?>

<div class="card">
  <h3 style="margin-bottom:16px;"><?= $edit_part ? 'Edit Section' : 'Add New Section' ?></h3>
  <form method="POST">
    <input type="hidden" name="id" value="<?= h($edit_part['id'] ?? '') ?>">
    <div class="grid grid-2">
      <div class="form-group">
        <label>Parent Category *</label>
        <select name="category_id" class="searchable-select" data-placeholder="Type to search category…" required>
          <option value="">— Select category —</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= (($edit_part['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= h($cat['main_category_name']) ?> — <?= h($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Section Name *</label>
        <input type="text" name="name" required placeholder="e.g. Class 9-10, Oxford Titles" value="<?= h($edit_part['name'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Description (optional)</label>
      <textarea name="description"><?= h($edit_part['description'] ?? '') ?></textarea>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label>Display Order</label>
        <input type="number" name="sort_order" value="<?= h($edit_part['sort_order'] ?? 0) ?>">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="active" <?= ($edit_part['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="hidden" <?= ($edit_part['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-navy"><?= $edit_part ? 'Update Section' : 'Add Section' ?></button>
      <?php if ($edit_part): ?><a href="parts.php" class="btn btn-outline" style="color:var(--navy);border-color:#ccc;">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h3 style="margin-bottom:16px;">All Sections (<?= count($parts) ?>)</h3>
  <table class="admin-table">
    <thead><tr><th>Section</th><th>Main Category</th><th>Category</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($parts as $part): ?>
      <tr>
        <td><b><?= h($part['name']) ?></b></td>
        <td><?= h($part['main_category_name']) ?></td>
        <td><?= h($part['category_name']) ?></td>
        <td><?= (int)$part['sort_order'] ?></td>
        <td><span class="badge badge-<?= $part['status'] ?>"><?= h($part['status']) ?></span></td>
        <td>
          <a href="parts.php?edit=<?= $part['id'] ?>" class="btn btn-navy btn-sm">Edit</a>
          <a href="parts.php?delete=<?= $part['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this section and all books inside it?');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (count($parts) === 0): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px;">No sections yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>