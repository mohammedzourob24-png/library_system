<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'librarian') {
    header("Location: ../dashboard/index.php");
    exit();
}

include '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$users = $conn->query("SELECT id, name FROM users WHERE role='user'");
$books = $conn->query("SELECT id, title FROM books WHERE status='available'");

if (isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $loan_date = $_POST['loan_date'];
    $return_date = $_POST['return_date'];

    $stmt = $conn->prepare("INSERT INTO loans (user_id, book_id, loan_date, return_date) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss", $user_id, $book_id, $loan_date, $return_date);
    $stmt->execute();

    $conn->query("UPDATE books SET status='loaned' WHERE id=$book_id");

    echo "<p class='success'>تمت الإعارة بنجاح</p>";
}
?>
<link rel="stylesheet" href="../assets/css/style.css">

<div class="content">
    <h2>📚 إعارة كتاب</h2>

    <form method="POST">
        <label>المستخدم:</label>
        <select name="user_id" required>
            <?php while ($u = $users->fetch_assoc()) { ?>
                <option value="<?= $u['id'] ?>"><?= $u['name'] ?></option>
            <?php } ?>
        </select>

        <label>الكتاب:</label>
        <select name="book_id" required>
            <?php while ($b = $books->fetch_assoc()) { ?>
                <option value="<?= $b['id'] ?>"><?= $b['title'] ?></option>
            <?php } ?>
        </select>

        <label>تاريخ الإعارة:</label>
        <input type="date" name="loan_date" required>

        <label>تاريخ الإرجاع:</label>
        <input type="date" name="return_date" required>

        <button type="submit" name="submit">تأكيد الإعارة</button>
    </form>
</div>