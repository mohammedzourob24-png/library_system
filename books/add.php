<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_book'])) {
    header("Location: index.php");
    exit();
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);

if ($title === '' || $author === '') {
    $_SESSION['flash_error'] = "Title and Author are required.";
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("INSERT INTO books (title, author, category_id, status)
                        VALUES (?, ?, NULLIF(?,0), 'available')");
$stmt->bind_param("ssi", $title, $author, $category_id);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = "Book added successfully.";
} else {
    // Handle duplicate title (UNIQUE)
    if ($conn->errno == 1062) {
        $_SESSION['flash_error'] = "This title already exists. Please choose another title.";
    } else {
        $_SESSION['flash_error'] = "Failed to add book. Error: " . $conn->error;
    }
}

header("Location: index.php");
exit();