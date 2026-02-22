<?php
session_start();
include '../config/db.php';

// Librarian only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../login.php");
    exit();
}

// Validate loan id
$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($loan_id <= 0) {
    $_SESSION['flash_error'] = "Invalid loan ID.";
    header("Location: index.php");
    exit();
}

// Detect optional columns in loans table
$hasStatus = false;
$hasActualReturn = false;

$c1 = $conn->query("SHOW COLUMNS FROM loans LIKE 'status'");
if ($c1 && $c1->num_rows > 0) $hasStatus = true;

$c2 = $conn->query("SHOW COLUMNS FROM loans LIKE 'actual_return_date'");
if ($c2 && $c2->num_rows > 0) $hasActualReturn = true;

// Fetch loan + book_id
$stmt = $conn->prepare("SELECT book_id FROM loans WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$res = $stmt->get_result();
$loan = $res->fetch_assoc();

if (!$loan) {
    $_SESSION['flash_error'] = "Loan not found.";
    header("Location: index.php");
    exit();
}

$book_id = (int)$loan['book_id'];

// Update loan (returned)
if ($hasActualReturn && $hasStatus) {
    $upd = $conn->prepare("UPDATE loans SET actual_return_date = CURDATE(), status = 'returned' WHERE id = ?");
    $upd->bind_param("i", $loan_id);
    $ok = $upd->execute();
} elseif ($hasActualReturn) {
    $upd = $conn->prepare("UPDATE loans SET actual_return_date = CURDATE() WHERE id = ?");
    $upd->bind_param("i", $loan_id);
    $ok = $upd->execute();
} elseif ($hasStatus) {
    $upd = $conn->prepare("UPDATE loans SET status = 'returned' WHERE id = ?");
    $upd->bind_param("i", $loan_id);
    $ok = $upd->execute();
} else {
    // No columns to mark return -> still update book status, but warn
    $ok = true;
}

// Update book status to available
$ub = $conn->prepare("UPDATE books SET status='available' WHERE id = ?");
$ub->bind_param("i", $book_id);
$ok2 = $ub->execute();

if ($ok && $ok2) {
    $_SESSION['flash_success'] = "Book returned successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to return book.";
}

header("Location: index.php");
exit();