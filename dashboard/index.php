<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Detect optional columns in loans table
$hasStatus = false;
$hasActualReturn = false;

$c1 = $conn->query("SHOW COLUMNS FROM loans LIKE 'status'");
if ($c1 && $c1->num_rows > 0) $hasStatus = true;

$c2 = $conn->query("SHOW COLUMNS FROM loans LIKE 'actual_return_date'");
if ($c2 && $c2->num_rows > 0) $hasActualReturn = true;

// Total books
$total_books = (int)($conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'] ?? 0);

// Borrowed / Loaned count (works even if loans.status does NOT exist)
$total_loans = 0;

if ($hasStatus) {
    // use loans.status if it exists
    $qBorrow = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE status - ('loaned')");
    if ($qBorrow) $total_loans = (int)($qBorrow->fetch_assoc()['total'] ?? 0);

    // if actual_return_date exists, you can count only not returned (optional)
    if ($hasActualReturn) {
        $qBorrow2 = $conn->query("SELECT COUNT(*) AS total FROM loans 
                                  WHERE status = ('loaned') 
                                  AND actual_return_date IS NULL");
        if ($qBorrow2) $total_loans = (int)($qBorrow2->fetch_assoc()['total'] ?? 0);
    }
} else {
    // fallback: count borrowed books from books table (your system updates books.status)
    $qBorrow = $conn->query("SELECT COUNT(*) AS total FROM books WHERE status='borrowed'");
    if ($qBorrow) $total_loans = (int)($qBorrow->fetch_assoc()['total'] ?? 0);
}

// Late books
$late_books = 0;

if ($hasStatus) {
    $lateQuery = "SELECT COUNT(*) AS total FROM loans 
                  WHERE status = ('loaned') 
                  AND return_date < CURDATE()";

    if ($hasActualReturn) {
        $lateQuery .= " AND actual_return_date IS NULL";
    }

    $qLate = $conn->query($lateQuery);
    if ($qLate) $late_books = (int)($qLate->fetch_assoc()['total'] ?? 0);
} else {
    // fallback: late loans based on return_date (status column not available)
    $qLate = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE return_date < CURDATE()");
    if ($qLate) $late_books = (int)($qLate->fetch_assoc()['total'] ?? 0);
}

// Load modern layout
include '../includes/layout_top.php';
?>

<h2 class="page-title">🏠 Dashboard</h2>

<div class="grid">
    <div class="card">
        <h3>📚 Total Books</h3>
        <div class="num"><?= $total_books ?></div>
    </div>

    <div class="card">
        <h3>📖 Borrowed / Loaned</h3>
        <div class="num"><?= $total_loans ?></div>
    </div>

    <div class="card">
        <h3>⏰ Late Books</h3>
        <div class="num"><?= $late_books ?></div>
    </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>