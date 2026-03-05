<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'librarian') {
    header("Location: ../login.php");
    exit();
}

// Delete user (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)($_POST['delete_id'] ?? 0);

    // Prevent deleting yourself
    if ($delete_id === (int)$_SESSION['user_id']) {
        $_SESSION['flash_error'] = "You cannot delete your own account.";
        header("Location: users.php");
        exit();
    }

    // Prevent deleting librarians/admins
    $chk = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
    $chk->bind_param("i", $delete_id);
    $chk->execute();
    $r = $chk->get_result()->fetch_assoc();

    if (!$r) {
        $_SESSION['flash_error'] = "User not found.";
        header("Location: users.php");
        exit();
    }

    if ($r['role'] === 'librarian') {
        $_SESSION['flash_error'] = "You cannot delete a librarian account.";
        header("Location: users.php");
        exit();
    }

    // Do not delete user if he has active loans
    $active = $conn->prepare("SELECT COUNT(*) AS c FROM loans WHERE user_id=? AND status='loaned'");
    $active->bind_param("i", $delete_id);
    $active->execute();
    $cnt = (int)($active->get_result()->fetch_assoc()['c'] ?? 0);

    if ($cnt > 0) {
        $_SESSION['flash_error'] = "Cannot delete user: user has active loans.";
        header("Location: users.php");
        exit();
    }

    $del = $conn->prepare("DELETE FROM users WHERE id=?");
    $del->bind_param("i", $delete_id);

    if ($del->execute()) {
        $_SESSION['flash_success'] = "User deleted successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to delete user.";
    }

    header("Location: users.php");
    exit();
}

// Fetch users
$users = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");

include '../includes/layout_top.php';
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert success"><?= htmlspecialchars($_SESSION['flash_success']); ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert danger"><?= htmlspecialchars($_SESSION['flash_error']); ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<h2 class="page-title">👥 All Users</h2>

<div class="panel panel-wide">
  <h3 class="panel-title">Users Table</h3>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="min-width:60px;">ID</th>
          <th style="min-width:160px;">Name</th>
          <th style="min-width:220px;">Email</th>
          <th style="min-width:110px;">Role</th>
          <th style="min-width:170px;">Created At</th>
          <th style="min-width:140px;">Action</th>
        </tr>
      </thead>

      <tbody>
        <?php if ($users && $users->num_rows > 0): ?>
          <?php while($u = $users->fetch_assoc()): ?>
            <tr>
              <td><?= (int)$u['id']; ?></td>
              <td><?= htmlspecialchars($u['name']); ?></td>
              <td><?= htmlspecialchars($u['email']); ?></td>
              <td>
                <?php if ($u['role'] === 'librarian'): ?>
                  <span class="badge returned">Librarian</span>
                <?php else: ?>
                  <span class="badge borrowed">User</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($u['created_at'] ?? '—'); ?></td>

              <td class="actions">
                <?php if ($u['role'] === 'librarian'): ?>
                  <span class="badge returned">Admin</span>
                <?php else: ?>
                  <?php $formId = "delUserForm" . (int)$u['id']; ?>
                  <form method="POST" class="table-form" id="<?= $formId; ?>">
  <input type="hidden" name="delete_id" value="<?= (int)$u['id']; ?>">
  <input type="hidden" name="delete_user" value="1">

  <button class="btn danger" type="submit"
    onclick="openDeleteModalForForm(document.getElementById('<?= $formId; ?>'),'Delete this user? This action cannot be undone.'); return false;">
    🗑 Delete
  </button>
</form>

                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;">No users found.</td></tr>
        <?php endif; ?>
      </tbody>

    </table>
  </div>
</div>

<?php include '../includes/layout_bottom.php'; ?>
