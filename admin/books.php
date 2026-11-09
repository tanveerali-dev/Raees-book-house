<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Books';

$message = '';
$error = '';
$edit_book = null;

$upload_dir = __DIR__ . '/../uploads/books/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// --- Handle delete ---
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: books.php?msg=deleted');
    exit;
}

// --- Handle add/edit form submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $part_id = (int)($_POST['part_id'] ?? 0);
    $price = $_POST['price'] !== '' ? (float)$_POST['price'] : null;
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $is_special = isset($_POST['is_wholesale_special']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] === 'hidden' ? 'hidden' : 'active';
    $id = (int)($_POST['id'] ?? 0);

    $image_path = $_POST['existing_image'] ?? null;

    if ($title === '' || $part_id === 0) {
        $error = 'Book title and section are both required.';
    } elseif (empty($_FILES['image']['name']) && !$image_path) {
        $error = 'Please upload a cover image for the book.';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $filename = 'book_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image_path = 'uploads/books/' . $filename;
                }
            } else {
                $error = 'Image must be JPG, PNG or WEBP.';
            }
        }

        if (!$error) {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE books SET part_id=?, title=?, publisher=?, price=?, image=?, rating=?, is_wholesale_special=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$part_id, $title, $publisher, $price, $image_path, $rating, $is_special, $sort_order, $status, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO books (part_id, title, publisher, price, image, rating, is_wholesale_special, sort_order, status) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$part_id, $title, $publisher, $price, $image_path, $rating, $is_special, $sort_order, $status]);
            }
            header('Location: books.php?msg=saved');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_book = $stmt->fetch();
}

if (($_GET['msg'] ?? '') === 'saved') $message = 'Book saved successfully.';
if (($_GET['msg'] ?? '') === 'deleted') $message = 'Book deleted.';

$parts = $pdo->query(
    "SELECT p.*, c.name AS category_name, m.name AS main_category_name
     FROM parts p JOIN categories c ON c.id = p.category_id JOIN main_categories m ON m.id = c.main_category_id
     ORDER BY m.sort_order, c.name, p.name"
)->fetchAll();

$filter_part = isset($_GET['part']) ? (int)$_GET['part'] : 0;
$sql = "SELECT b.*, p.name AS part_name, c.name AS category_name, m.name AS main_category_name FROM books b
        JOIN parts p ON p.id = b.part_id JOIN categories c ON c.id = p.category_id JOIN main_categories m ON m.id = c.main_category_id";
if ($filter_part) $sql .= " WHERE b.part_id = " . $filter_part;
$sql .= " ORDER BY m.sort_order, c.name, p.name, b.sort_order, b.title";
$books = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topline"><h2 style="margin:0;">Books</h2></div>

<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<?php if (count($parts) === 0): ?>
  <div class="alert alert-error">You need to create a Category and at least one Section before adding books. <a href="parts.php">Add a section first →</a></div>
<?php endif; ?>

<div class="card">
  <h3 style="margin-bottom:16px;"><?= $edit_book ? 'Edit Book' : 'Add New Book' ?></h3>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= h($edit_book['id'] ?? '') ?>">
    <input type="hidden" name="existing_image" value="<?= h($edit_book['image'] ?? '') ?>">
    <div class="grid grid-2">
      <div class="form-group">
        <label>Section *</label>
        <select name="part_id" class="searchable-select" data-placeholder="Type to search section…" required>
          <option value="">— Select section —</option>
          <?php foreach ($parts as $part): ?>
            <option value="<?= $part['id'] ?>" <?= (($edit_book['part_id'] ?? '') == $part['id']) ? 'selected' : '' ?>><?= h($part['main_category_name']) ?> — <?= h($part['category_name']) ?> — <?= h($part['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Book Title *</label>
        <input type="text" name="title" required placeholder="e.g. Physics for Class 10" value="<?= h($edit_book['title'] ?? '') ?>">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label>Publisher (optional)</label>
        <input type="text" name="publisher" placeholder="e.g. Punjab Textbook Board" value="<?= h($edit_book['publisher'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Price in Rs. (optional)</label>
        <input type="number" step="0.01" name="price" value="<?= h($edit_book['price'] ?? '') ?>">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label>Rating (shown to customers when they hover over the book)</label>
        <select name="rating">
          <?php $current_rating = $edit_book['rating'] ?? 5; for ($s = 5; $s >= 1; $s--): ?>
            <option value="<?= $s ?>" <?= $current_rating == $s ? 'selected' : '' ?>><?= str_repeat('★', $s) . str_repeat('☆', 5 - $s) ?> (<?= $s ?>/5)</option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Cover Image <?= $edit_book ? '(leave empty to keep current)' : '*' ?></label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        <?php if (!empty($edit_book['image'])): ?>
          <img src="../<?= h($edit_book['image']) ?>" class="thumb-mini" style="margin-top:8px;">
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Display Order</label>
        <input type="number" name="sort_order" value="<?= h($edit_book['sort_order'] ?? 0) ?>">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="form-group">
        <label><input type="checkbox" name="is_wholesale_special" value="1" <?= !empty($edit_book['is_wholesale_special']) ? 'checked' : '' ?> style="width:auto;margin-right:8px;">Mark as "Wholesale Special"</label>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="active" <?= ($edit_book['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="hidden" <?= ($edit_book['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-navy"><?= $edit_book ? 'Update Book' : 'Add Book' ?></button>
      <?php if ($edit_book): ?><a href="books.php" class="btn btn-outline" style="color:var(--navy);border-color:#ccc;">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
    <h3 style="margin:0;">All Books (<?= count($books) ?>)</h3>
    <form method="GET" style="display:flex;gap:8px;">
      <select name="part" onchange="this.form.submit()" style="padding:8px 12px;border-radius:8px;border:1px solid #d9dced;">
        <option value="0">All sections</option>
        <?php foreach ($parts as $part): ?>
          <option value="<?= $part['id'] ?>" <?= $filter_part == $part['id'] ? 'selected' : '' ?>><?= h($part['main_category_name']) ?> — <?= h($part['category_name']) ?> — <?= h($part['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <table class="admin-table">
    <thead><tr><th></th><th>Title</th><th>Section</th><th>Rating</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($books as $book): ?>
      <tr>
        <td><img src="../<?= h($book['image']) ?>" class="thumb-mini"></td>
        <td><b><?= h($book['title']) ?></b><?php if ($book['is_wholesale_special']): ?> <span class="badge badge-active">Special</span><?php endif; ?><br><small style="color:var(--ink-soft);"><?= h($book['publisher']) ?></small></td>
        <td><?= h($book['main_category_name']) ?> / <?= h($book['category_name']) ?><br><small style="color:var(--ink-soft);"><?= h($book['part_name']) ?></small></td>
        <td style="color:var(--gold-deep);white-space:nowrap;"><?= str_repeat('★', (int)$book['rating']) . str_repeat('☆', 5 - (int)$book['rating']) ?></td>
        <td><?= $book['price'] ? 'Rs. ' . number_format($book['price'],0) : '—' ?></td>
        <td><span class="badge badge-<?= $book['status'] ?>"><?= h($book['status']) ?></span></td>
        <td>
          <a href="books.php?edit=<?= $book['id'] ?>" class="btn btn-navy btn-sm">Edit</a>
          <a href="books.php?delete=<?= $book['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this book?');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (count($books) === 0): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px;">No books yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>