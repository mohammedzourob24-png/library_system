<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'] ?? '';
$isLibrarian = ($role === 'librarian');

/* Borrow (Admin/Librarian only) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
    if (!$isLibrarian) {
        $_SESSION['flash_error'] = "Not allowed.";
        header("Location: index.php");
        exit();
    }

    $user_id = (int)($_POST['user_id'] ?? 0);
    $book_id = (int)($_POST['book_id'] ?? 0);
    $return_date = $_POST['return_date'] ?? '';

    if ($user_id <= 0 || $book_id <= 0 || $return_date === '') {
        $_SESSION['flash_error'] = "All fields are required.";
        header("Location: index.php");
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO loans (user_id, book_id, loan_date, return_date, status)
        VALUES (?, ?, CURDATE(), ?, 'loaned')
    ");
    $stmt->bind_param("iis", $user_id, $book_id, $return_date);

    if ($stmt->execute()) {
        $up = $conn->prepare("UPDATE books SET status='borrowed' WHERE id=?");
        $up->bind_param("i", $book_id);
        $up->execute();

        $_SESSION['flash_success'] = "Book loaned successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to loan book.";
    }

    header("Location: index.php");
    exit();
}

/* Return (Admin/Librarian only) via link */
if (isset($_GET['return_id'])) {
    if (!$isLibrarian) {
        $_SESSION['flash_error'] = "Not allowed.";
        header("Location: index.php");
        exit();
    }

    $loan_id = (int)$_GET['return_id'];

    $q = $conn->prepare("SELECT book_id FROM loans WHERE id=? LIMIT 1");
    $q->bind_param("i", $loan_id);
    $q->execute();
    $loan = $q->get_result()->fetch_assoc();

    if ($loan) {
        $book_id = (int)$loan['book_id'];

        $upd = $conn->prepare("UPDATE loans SET status='returned' WHERE id=?");
        $upd->bind_param("i", $loan_id);
        $upd->execute();

        $ub = $conn->prepare("UPDATE books SET status='available' WHERE id=?");
        $ub->bind_param("i", $book_id);
        $ub->execute();

        $_SESSION['flash_success'] = "Book returned successfully.";
    } else {
        $_SESSION['flash_error'] = "Loan not found.";
    }

    header("Location: index.php");
    exit();
}

/* Data for form */
$users = $isLibrarian ? $conn->query("SELECT id, name FROM users WHERE role='user' ORDER BY name ASC") : null;
$books = $isLibrarian ? $conn->query("SELECT id, title FROM books WHERE status='available' ORDER BY title ASC") : null;

/* Loaned list */
$loaned = $conn->query("
    SELECT loans.id, users.name AS user_name, books.title, loans.loan_date, loans.return_date
    FROM loans
    JOIN users ON loans.user_id = users.id
    JOIN books ON loans.book_id = books.id
    WHERE loans.status='loaned'
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

<h2 class="page-title">🔁 Loans</h2>

<?php if ($isLibrarian): ?>
<div class="center-page">
  <div class="center-card">
    <div class="panel">
      <h3 class="panel-title" style="text-align:center;">📚 Loan a Book</h3>

      <form class="form dark-form loans-form" method="POST">
        <div class="row">
          <label class="label">User</label>
          <select name="user_id" required>
            <option value="">Select User</option>
            <?php if ($users && $users->num_rows > 0): ?>
              <?php while($u = $users->fetch_assoc()): ?>
                <option value="<?= (int)$u['id']; ?>"><?= htmlspecialchars($u['name']); ?></option>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="row">
          <label class="label">Book (Available)</label>
          <select name="book_id" required>
            <option value="">Select Book</option>
            <?php if ($books && $books->num_rows > 0): ?>
              <?php while($b = $books->fetch_assoc()): ?>
                <option value="<?= (int)$b['id']; ?>"><?= htmlspecialchars($b['title']); ?></option>
              <?php endwhile; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="row">
          <label class="label">Return Date</label>
          <input class="input" type="date" name="return_date" required>
        </div>

        <button class="btn primary w-100" type="submit" name="borrow">Loan</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="panel panel-wide" style="max-width:1100px; margin:16px auto 0 auto;">
  <h3 class="panel-title" style="text-align:center;">📌 Loaned Books</h3>

  <div class="table-wrap" style="max-height:520px;">
    <table class="table">
      <thead>
        <tr>
          <th>User</th>
          <th>Book</th>
          <th>Loan Date</th>
          <th>Return Date</th>
          <?php if ($isLibrarian): ?><th>Action</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($loaned && $loaned->num_rows > 0): ?>
          <?php while($r = $loaned->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($r['user_name']); ?></td>
              <td><?= htmlspecialchars($r['title']); ?></td>
              <td><?= htmlspecialchars($r['loan_date']); ?></td>
              <td><?= htmlspecialchars($r['return_date']); ?></td>
              <?php if ($isLibrarian): ?>
                <td class="actions">
                  <a class="btn success"
                     href="index.php?return_id=<?= (int)$r['id']; ?>"
                     onclick="openDeleteModal(this.href,'Return this book?'); return false;">
                     ✅ Return
                  </a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="<?= $isLibrarian ? 5 : 4; ?>" style="text-align:center;">No loaned books.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>