<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (($_SESSION['role'] ?? '') !== 'librarian') {
    // رجّعه للـ dashboard الرئيسي (عدّل المسار إذا اسم الداشبورد مختلف عندك)
    header("Location: ../dashboard.php");
    exit();
}

/* Detect optional column actual_return_date */
$hasActualReturn = false;
$col = $conn->query("SHOW COLUMNS FROM loans LIKE 'actual_return_date'");
if ($col && $col->num_rows > 0) $hasActualReturn = true;

/* Build condition for "not returned yet" */
$notReturnedCond = $hasActualReturn ? "loans.actual_return_date IS NULL" : "1=1";

/* الكتب المتأخرة */
$lateBooks = $conn->query("
    SELECT books.title, users.name, loans.return_date
    FROM loans
    JOIN books ON loans.book_id = books.id
    JOIN users ON loans.user_id = users.id
    WHERE $notReturnedCond
      AND loans.return_date < CURDATE()
    ORDER BY loans.return_date ASC
");

/* أكثر الكتب إعارة */
$popularBooks = $conn->query("
    SELECT books.title, COUNT(loans.book_id) AS total
    FROM loans
    JOIN books ON loans.book_id = books.id
    GROUP BY loans.book_id
    ORDER BY total DESC
    LIMIT 5
");

/* المستخدمون المتأخرون */
$lateUsers = $conn->query("
    SELECT users.name, COUNT(loans.id) AS late_count
    FROM loans
    JOIN users ON loans.user_id = users.id
    WHERE $notReturnedCond
      AND loans.return_date < CURDATE()
    GROUP BY users.id
    ORDER BY late_count DESC
");

/* Layout */
include '../includes/layout_top.php';
?>

<h2>📊 Reports & Alerts</h2>

<h3>⏰ Late Books</h3>
<table class="table">
    <thead>
        <tr>
            <th>Book</th>
            <th>User</th>
            <th>Return Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($lateBooks && $lateBooks->num_rows > 0): ?>
            <?php while ($row = $lateBooks->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['return_date']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No late books.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

<h3>🔥 Most Borrowed Books</h3>
<table class="table">
    <thead>
        <tr>
            <th>Book</th>
            <th>Borrow Count</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($popularBooks && $popularBooks->num_rows > 0): ?>
            <?php while ($row = $popularBooks->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= (int)$row['total']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2">No data.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

<h3>⚠️ Late Users</h3>
<table class="table">
    <thead>
        <tr>
            <th>User</th>
            <th>Late Count</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($lateUsers && $lateUsers->num_rows > 0): ?>
            <?php while ($row = $lateUsers->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= (int)$row['late_count']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2">No late users.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/layout_bottom.php'; ?>