<?php
$name = $_SESSION['user_name'] ?? ($_SESSION['name'] ?? 'User');
$role = $_SESSION['role'] ?? '';
?>

<header class="header">
  <div class="left">
    <span class="pill">👤 <?= htmlspecialchars($name) ?></span>
    <span class="pill">🔐 <?= htmlspecialchars($role) ?></span>
  </div>

  <div class="right">
    <a class="btn danger"
       href="/library_system/logout.php"
       data-confirm="Are you sure you want to logout?">
      Logout
    </a>
  </div>
</header>