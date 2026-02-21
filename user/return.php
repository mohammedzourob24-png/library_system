<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($loan_id <= 0) {
    $_SESSION['flash_error'] = "Invalid loan.";
    header("Location: my_loans.php");
    exit();
}

/* Get loan and make sure it belongs to this user and is currently loaned */
$stmt = $conn->prepare("SELECT book_id, status FROM loans WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param("ii", $loan_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$loan = $res->fetch_assoc();

if (!$loan) {
    $_SESSION['flash_error'] = "Loan not found.";
    header("Location: my_loans.php");
    exit();
}

if ($loan['status'] !== 'loaned') {
    $_SESSION['flash_error'] = "This loan is already returned.";
    header("Location: my_loans.php");
    exit();
}

$book_id = (int)$loan['book_id'];

/* 1) Mark loan returned */
$upd = $conn->prepare("UPDATE loans SET status='returned' WHERE id=? AND user_id=?");
$upd->bind_param("ii", $loan_id, $user_id);
$ok1 = $upd->execute();

/* 2) Make the book available again */
$ub = $conn->prepare("UPDATE books SET status='available' WHERE id=?");
$ub->bind_param("i", $book_id);
$ok2 = $ub->execute();

if ($ok1 && $ok2) {
    $_SESSION['flash_success'] = "Book returned successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to return book.";
}

header("Location: my_loans.php");
exit();