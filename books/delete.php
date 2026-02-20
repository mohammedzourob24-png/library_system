<?php
session_start();
include '../config/db.php';

// Only librarian
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../login.php");
    exit();
}

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = "Invalid book ID.";
    header("Location: index.php");
    exit();
}

// Check book exists + status
$check = $conn->prepare("SELECT status FROM books WHERE id = ? LIMIT 1");
$check->bind_param("i", $id);
$check->execute();
$res = $check->get_result();
$book = $res->fetch_assoc();

if (!$book) {
    $_SESSION['flash_error'] = "Book not found.";
    header("Location: index.php");
    exit();
}

// Prevent deleting borrowed/loaned books
$status = $book['status'] ?? '';
if ($status !== 'available') {
    $_SESSION['flash_error'] = "You cannot delete this book because it is not available (it may be borrowed).";
    header("Location: index.php");
    exit();
}

// Delete
$del = $conn->prepare("DELETE FROM books WHERE id = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    $_SESSION['flash_success'] = "Book deleted successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to delete book.";
}

header("Location: index.php");
exit();