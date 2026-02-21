<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}

$user_id = (int)$_SESSION['user_id'];

$loans = $conn->query("
  SELECT loans.id, books.title, loans.loan_date, loans.return_date, loans.status
  FROM loans
  JOIN books ON loans.book_id = books.id
  WHERE loans.user_id = $user_id AND loans.status='loaned'
  ORDER BY loans.id DESC
");

include '../includes/layout_top.php';
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert success"><?= htmlspecialchars($_SESSION['flash_success']); ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert danger"><?= htmlspecialchars($_SESSION['flash_error']); ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<h2 class="page-title">📌 My Loans</h2>

<div class="panel panel-wide">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Book</th>
          <th>Loan Date</th>
          <th>Return Date</th>
          <th>Status</th>
          <th>Late?</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        <?php if ($loans && $loans->num_rows > 0): ?>
          <?php while($row = $loans->fetch_assoc()): 
              $status = $row['status'] ?? '';
              $isLate = ($status === 'loaned' && $row['return_date'] < date('Y-m-d'));
              $lateText = $isLate ? "Yes" : "No";

              $badgeClass = ($status === 'returned') ? 'returned' : 'borrowed'; // reuse existing css classes
          ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['loan_date']) ?></td>
              <td><?= htmlspecialchars($row['return_date']) ?></td>

              <td>
                <span class="badge <?= $badgeClass; ?>">
                  <?= htmlspecialchars(ucfirst($status)) ?>
                </span>
              </td>

              <td><?= $lateText ?></td>

              <td class="actions">
                <?php if ($status === 'loaned'): ?>
                  <a class="btn success"
                     href="return.php?id=<?= (int)$row['id']; ?>"
                     data-confirm="Return this book?">
                    ✅ Return
                  </a>
                <?php else: ?>
                  <span class="small">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center;">No loans found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>