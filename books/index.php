<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$role = $_SESSION['role'] ?? '';
$isLibrarian = ($role === 'librarian');

/* Books + Category name */
$booksSql = "
    SELECT 
        books.id,
        books.title,
        books.author,
        categories.name AS category,
        books.status
    FROM books
    LEFT JOIN categories ON books.category_id = categories.id
    ORDER BY books.id DESC
";
$books = $conn->query($booksSql);

include '../includes/layout_top.php';
?>

<!-- Flash Messages -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert success"><?= htmlspecialchars($_SESSION['flash_success']); ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert danger"><?= htmlspecialchars($_SESSION['flash_error']); ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<h2>📚 Books</h2>

<div class="panel panel-wide">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px;">
    <h3 class="panel-title" style="margin:0;">📖 Books List</h3>

    <?php if ($isLibrarian): ?>
      <a class="btn primary" href="add_form.php">➕ Add Book</a>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="min-width:180px;">Title</th>
          <th style="min-width:140px;">Author</th>
          <th style="min-width:160px;">Category</th>
          <th style="min-width:110px;">Status</th>
          <?php if ($isLibrarian): ?><th style="min-width:170px;">Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($books && $books->num_rows > 0): ?>
          <?php while ($book = $books->fetch_assoc()): ?>
            <?php
              $status = $book['status'] ?? '';
              $badgeClass = 'available';
              if ($status === 'borrowed' || $status === 'loaned') $badgeClass = 'borrowed';
              if ($status === 'returned') $badgeClass = 'returned';
            ?>
            <tr>
              <td><?= htmlspecialchars($book['title']); ?></td>
              <td><?= htmlspecialchars($book['author']); ?></td>
              <td><?= htmlspecialchars($book['category'] ?? '—'); ?></td>
              <td><span class="badge <?= $badgeClass; ?>"><?= htmlspecialchars(ucfirst($status)); ?></span></td>

              <?php if ($isLibrarian): ?>
              <td class="actions">
                <a class="btn" href="edit.php?id=<?= (int)$book['id']; ?>">✏️ Edit</a>
           <a class="btn danger"
   href="delete.php?id=<?= (int)$book['id']; ?>"
   onclick="openDeleteModal(this.href,'Delete this book? This action cannot be undone.'); return false;">
   🗑 Delete
</a>


              </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="<?= $isLibrarian ? 5 : 4; ?>">No books found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>