<?php
$role = $_SESSION['role'] ?? '';
$isLibrarian = ($role === 'librarian');
?>

<aside class="sidebar">
  <div class="brand">
    <div class="logo">📚</div>
    <div>
      <div class="title">Library System</div>
      <div class="sub">Role: <?= htmlspecialchars($role) ?></div>
    </div>
  </div>

  <nav class="nav">

    <!-- Common -->
    <a href="/library_system/dashboard/index.php">🏠 Dashboard</a>

    <?php if ($isLibrarian): ?>
      <!-- Librarian Menu -->
      <a href="/library_system/books/index.php">📚 Books List</a>
      <a href="/library_system/books/add_form.php">➕ Add Book</a>
      <a href="/library_system/books/search.php">🔍 Search Books</a>


      <a href="/library_system/loans/index.php">🔁 Loans (Borrow/Return)</a>
      <a href="/library_system/admin/categories/index.php">📁 Categories</a>

      <a href="/library_system/reports/index.php">📊 Reports</a>
      <a href="/library_system/admin/users.php">👥 Users</a>


    <?php else: ?>
      <!-- User Menu -->
      <a href="/library_system/user/books.php">📚 Browse Books</a>
      <a href="/library_system/books/search.php">🔍 Search</a>
      <a href="/library_system/user/my_loans.php">📌 My Loans</a>
    <?php endif; ?>

  </nav>

  <div class="small" style="margin-top:12px; padding:10px 12px; border:1px solid rgba(148,163,184,.12); border-radius:12px; background: rgba(148,163,184,.06);">
    Tip: Use the menu to navigate.
  </div>
</aside>