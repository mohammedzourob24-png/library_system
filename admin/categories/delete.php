<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../../login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Optional: prevent delete if books use this category
$chk = $conn->prepare("SELECT COUNT(*) AS c FROM books WHERE category_id=?");
$chk->bind_param("i", $id);
$chk->execute();
$cnt = (int)($chk->get_result()->fetch_assoc()['c'] ?? 0);

if ($cnt > 0) {
    $_SESSION['flash_error'] = "Cannot delete category: used by books.";
    header("Location: index.php");
    exit();
}

$del = $conn->prepare("DELETE FROM categories WHERE id=?");
$del->bind_param("i", $id);

if ($del->execute()) $_SESSION['flash_success'] = "Category deleted successfully.";
else $_SESSION['flash_error'] = "Failed to delete category.";

header("Location: index.php");
exit();