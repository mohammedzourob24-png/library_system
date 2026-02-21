<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$books = $conn->query("
  SELECT books.id, books.title, books.author, categories.name AS category, books.status
  FROM books
  LEFT JOIN categories ON books.category_id = categories.id
  ORDER BY books.id DESC
");

include '../includes/layout_top.php';
?>

<h2 class="page-title">📚 Browse Books</h2>

<div class="panel panel-wide">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php while($b = $books->fetch_assoc()): 
          $status = $b['status'] ?? '';
          $badge = ($status==='available')?'available':'borrowed';
      ?>
        <tr>
          <td><?= htmlspecialchars($b['title']) ?></td>
          <td><?= htmlspecialchars($b['author']) ?></td>
          <td><?= htmlspecialchars($b['category'] ?? '—') ?></td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
          <td class="actions">
            <?php if($status==='available'): ?>
              <a class="btn primary" href="borrow.php?book_id=<?= (int)$b['id'] ?>" data-confirm="Borrow this book?">Borrow</a>
            <?php else: ?>
              <span class="small">Not available</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>