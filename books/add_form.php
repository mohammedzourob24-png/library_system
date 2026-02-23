<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../login.php");
    exit();
}

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

include '../includes/layout_top.php';
?>

<div class="center-page">
  <div class="center-card">

    <h2 style="margin:0 0 12px 0;">➕ Add Book</h2>

    <div class="panel">
      <h3 class="panel-title">New Book</h3>

      <form class="form dark-form" method="POST" action="add.php">
        <div class="row">
          <label class="label">Book Title</label>
          <input class="input" type="text" name="title" placeholder="Enter book title" required>
        </div>

        <div class="row">
          <label class="label">Author</label>
          <input class="input" type="text" name="author" placeholder="Enter author name" required>
        </div>

        <div class="row">
          <label class="label">Category</label>
          <select name="category_id">
            <option value="0">No Category</option>
            <?php if ($categories && $categories->num_rows > 0): ?>
              <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?= (int)$cat['id']; ?>"><?= htmlspecialchars($cat['name']); ?></option>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>

        <button class="btn primary w-100" type="submit" name="add_book">Add Book</button>

        <div style="margin-top:10px;">
          <a class="btn" href="index.php">Back</a>
        </div>
      </form>
    </div>

  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>