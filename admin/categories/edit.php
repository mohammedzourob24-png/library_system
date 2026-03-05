<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../../login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, name FROM categories WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$cat = $stmt->get_result()->fetch_assoc();

if (!$cat) {
    $_SESSION['flash_error'] = "Category not found.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $_SESSION['flash_error'] = "Category name is required.";
        header("Location: edit.php?id=".$id);
        exit();
    }

    // prevent duplicates (except same id)
    $chk = $conn->prepare("SELECT id FROM categories WHERE name=? AND id<>? LIMIT 1");
    $chk->bind_param("si", $name, $id);
    $chk->execute();
    $res = $chk->get_result();
    if ($res && $res->num_rows > 0) {
        $_SESSION['flash_error'] = "Another category already has this name.";
        header("Location: edit.php?id=".$id);
        exit();
    }

    $up = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $up->bind_param("si", $name, $id);

    if ($up->execute()) $_SESSION['flash_success'] = "Category updated successfully.";
    else $_SESSION['flash_error'] = "Failed to update category.";

    header("Location: index.php");
    exit();
}

include '../../includes/layout_top.php';
?>

<div class="center-page">
  <div class="center-card">
    <h2 style="margin:0 0 12px 0; text-align:center;">✏️ Edit Category</h2>

    <div class="panel">
      <h3 class="panel-title" style="text-align:center;">Category Details</h3>

      <form class="form dark-form" method="POST">
        <div class="row">
          <label class="label">Name</label>
          <input class="input" name="name" value="<?= htmlspecialchars($cat['name']); ?>" required>
        </div>

        <button class="btn primary w-100" type="submit" name="update_category">Update</button>

        <div style="margin-top:10px;">
          <a class="btn" href="index.php">Back</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/layout_bottom.php'; ?>
