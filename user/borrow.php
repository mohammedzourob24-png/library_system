<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$user_id = (int)$_SESSION['user_id'];
$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

if ($book_id <= 0) { header("Location: books.php"); exit(); }

// check availability
$chk = $conn->prepare("SELECT status FROM books WHERE id=? LIMIT 1");
$chk->bind_param("i", $book_id);
$chk->execute();
$r = $chk->get_result()->fetch_assoc();

if (!$r || $r['status'] !== 'available') {
    $_SESSION['flash_error'] = "Book is not available.";
    header("Location: books.php");
    exit();
}

// create loan: return after 7 days (you can change)
$ins = $conn->prepare("
  INSERT INTO loans (user_id, book_id, loan_date, return_date, status)
  VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'loaned'
)
");
$ins->bind_param("ii", $user_id, $book_id);

if ($ins->execute()) {
    $up = $conn->prepare("UPDATE books SET status='borrowed' WHERE id=?");
    $up->bind_param("i", $book_id);
    $up->execute();

    $_SESSION['flash_success'] = "Borrowed successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to borrow.";
}

header("Location: my_loans.php");
exit();