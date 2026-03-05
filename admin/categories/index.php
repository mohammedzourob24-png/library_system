<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../../login.php");
    exit();
}

// Add category (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $_SESSION['flash_error'] = "Category name is required.";
        header("Location: index.php");
        exit();
    }

    // prevent duplicates
    $chk = $conn->prepare("SELECT id FROM categories WHERE name=? LIMIT 1");
    $chk->bind_param("s", $name);
    $chk->execute();
    $res = $chk->get_result();
    if ($res && $res->num_rows > 0) {
        $_SESSION['flash_error'] = "Category already exists.";
        header("Location: index.php");
        exit();
    }

    $ins = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $ins->bind_param("s", $name);

    if ($ins->execute()) $_SESSION['flash_success'] = "Category added successfully.";
    else $_SESSION['flash_error'] = "Failed to add category.";

    header("Location: index.php");
    exit();
}

// fetch categories
$cats = $conn->query("SELECT id, name FROM categories ORDER BY id DESC");

include '../../includes/layout_top.php';
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert success"><?= htmlspecialchars($_SESSION['flash_success']); ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert danger"><?= htmlspecialchars($_SESSION['flash_error']); ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<h2 class="page-title">📁 Categories Management</h2>

<div class="panel panel-wide" style="max-width:900px; margin:0 auto;">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px;">
    <h3 class="panel-title" style="margin:0;">Categories Table</h3>

    <form method="POST" class="table-form" style="display:flex; gap:10px; align-items:center;">
      <input class="input" style="width:260px;" name="name" placeholder="New category name" required>
      <button class="btn primary" type="submit" name="add_category">➕ Add New Category</button>
    </form>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="min-width:80px;">ID</th>
          <th style="min-width:260px;">Name</th>
          <th style="min-width:220px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($cats && $cats->num_rows > 0): ?>
          <?php while($c = $cats->fetch_assoc()): ?>
            <tr>
              <td><?= (int)$c['id']; ?></td>
              <td><?= htmlspecialchars($c['name']); ?></td>
              <td class="actions">
                <a class="btn success" href="edit.php?id=<?= (int)$c['id']; ?>">✏️ Edit</a>
                <a class="btn danger"
                   href="delete.php?id=<?= (int)$c['id']; ?>"
                   onclick="openDeleteModal(this.href,'Delete this category?'); return false;">
                   🗑 Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3" style="text-align:center;">No categories found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../../includes/layout_bottom.php'; ?>
