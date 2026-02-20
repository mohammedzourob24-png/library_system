<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = "Invalid book ID.";
    header("Location: index.php");
    exit();
}

/* Fetch book */
$stmt = $conn->prepare("SELECT id, title, author, category_id, status FROM books WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$bookRes = $stmt->get_result();
$book = $bookRes->fetch_assoc();

if (!$book) {
    $_SESSION['flash_error'] = "Book not found.";
    header("Location: index.php");
    exit();
}

/* Fetch categories */
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

/* Update */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    if ($title === '' || $author === '') {
        $_SESSION['flash_error'] = "Title and Author are required.";
        header("Location: edit.php?id=" . $id);
        exit();
    }

    // Optional duplicate check: same title+author but different id
    $check = $conn->prepare("SELECT id FROM books WHERE title = ? AND author = ? AND id <> ? LIMIT 1");
    $check->bind_param("ssi", $title, $author, $id);
    $check->execute();
    $exists = $check->get_result();
    if ($exists && $exists->num_rows > 0) {
        $_SESSION['flash_error'] = "Another book already has the same Title and Author.";
        header("Location: edit.php?id=" . $id);
        exit();
    }

    $upd = $conn->prepare("UPDATE books SET title=?, author=?, category_id=NULLIF(?,0) WHERE id=?");
    $upd->bind_param("ssii", $title, $author, $category_id, $id);

    if ($upd->execute()) {
        $_SESSION['flash_success'] = "Book updated successfully.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['flash_error'] = "Failed to update book.";
        header("Location: edit.php?id=" . $id);
        exit();
    }
}

include '../includes/layout_top.php';
?>

<div class="center-page">
  <div class="center-card">

    <h2 style="margin:0 0 12px 0; text-align:center;">✏️ Edit Book</h2>

    <div class="panel">
      <h3 class="panel-title" style="text-align:center;">Book Details</h3>

      <form class="form dark-form" method="POST">
        <div class="row">
          <label class="label">Book Title</label>
          <input class="input" type="text" name="title" value="<?= htmlspecialchars($book['title']); ?>" required>
        </div>

        <div class="row">
          <label class="label">Author</label>
          <input class="input" type="text" name="author" value="<?= htmlspecialchars($book['author']); ?>" required>
        </div>

        <div class="row">
          <label class="label">Category</label>
          <select name="category_id">
            <option value="0">No Category</option>
            <?php if ($categories && $categories->num_rows > 0): ?>
              <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?= (int)$cat['id']; ?>"
                  <?= ((int)$book['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($cat['name']); ?>
                </option>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="row">
          <label class="label">Status</label>
          <input class="input" type="text" value="<?= htmlspecialchars($book['status']); ?>" disabled>
          <div class="small">Status is managed automatically by Borrow/Return.</div>
        </div>

        <button class="btn primary w-100" type="submit" name="update_book">Update</button>

        <div style="margin-top:10px;">
          <a class="btn" href="index.php">Back</a>
        </div>
      </form>
    </div>

  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>