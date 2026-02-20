<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$type   = $_GET['type'] ?? 'title';
$results = null;

if ($search !== '') {
    if ($type === 'author') {
        $sql = "SELECT books.title, books.author, categories.name AS category, books.status
                FROM books
                LEFT JOIN categories ON books.category_id = categories.id
                WHERE books.author LIKE ?";
    } elseif ($type === 'category') {
        $sql = "SELECT books.title, books.author, categories.name AS category, books.status
                FROM books
                LEFT JOIN categories ON books.category_id = categories.id
                WHERE categories.name LIKE ?";
    } else {
        $sql = "SELECT books.title, books.author, categories.name AS category, books.status
                FROM books
                LEFT JOIN categories ON books.category_id = categories.id
                WHERE books.title LIKE ?";
    }

    $stmt = $conn->prepare($sql);
    $like = "%".$search."%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $results = $stmt->get_result();
}

include '../includes/layout_top.php';
?>

<div class="center-page">
  <div class="center-card">

    <h2 style="margin:0 0 12px 0; text-align:center;">🔍 Search Books</h2>

    <div class="panel">
      <h3 class="panel-title" style="text-align:center;">Search</h3>

      <form class="form dark-form search-form" method="GET" action="">
        <div class="row">
          <label class="label">Keyword</label>
          <input class="input" type="text" name="search"
                 value="<?= htmlspecialchars($search); ?>"
                 placeholder="Type to search..." required>
        </div>

        <div class="row">
          <label class="label">Search By</label>
          <select name="type">
            <option value="title" <?= $type==='title'?'selected':''; ?>>Title</option>
            <option value="author" <?= $type==='author'?'selected':''; ?>>Author</option>
            <option value="category" <?= $type==='category'?'selected':''; ?>>Category</option>
          </select>
        </div>

        <button class="btn primary w-100" type="submit">Search</button>
      </form>
    </div>

    <?php if ($search !== ''): ?>
      <div class="panel" style="margin-top:16px;">
        <h3 class="panel-title" style="text-align:center;">Results</h3>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th style="min-width:180px;">Title</th>
                <th style="min-width:140px;">Author</th>
                <th style="min-width:160px;">Category</th>
                <th style="min-width:110px;">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($results && $results->num_rows > 0): ?>
                <?php while($row = $results->fetch_assoc()): ?>
                  <?php
                    $status = $row['status'] ?? '';
                    $badgeClass = 'available';
                    if ($status === 'borrowed' || $status === 'loaned') $badgeClass = 'borrowed';
                    if ($status === 'returned') $badgeClass = 'returned';
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= htmlspecialchars($row['author']); ?></td>
                    <td><?= htmlspecialchars($row['category'] ?? '—'); ?></td>
                    <td><span class="badge <?= $badgeClass; ?>"><?= htmlspecialchars(ucfirst($status)); ?></span></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="4">No results found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>