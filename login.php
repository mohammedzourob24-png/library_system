<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['user_name'] = $user['name'];

            header("Location: dashboard/index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert success"><?= htmlspecialchars($_SESSION['flash_success']); ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Library System</title>
  <link rel="stylesheet" href="/library_system/assets/css/style.css">
  <link rel="stylesheet" href="/library_system/assets/css/login.css">
</head>
<body class="login-page">

  <div class="login-shell">

    <div class="login-bg login-bg-left"></div>

    <div class="login-card2">
      <div class="login-card2-inner">   

        <div class="login-brand2">
          <img class="login-logo2" src="/library_system/assets/img/login-logo.png" alt="Logo"
               onerror="this.style.display='none'">
          <div class="login-title2">LIBRARY</div>
          <div class="login-subtitle2">MANAGEMENT SYSTEM</div>
        </div>

        <?php if ($error !== ""): ?>
          <div class="login-alert"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="login-form2" method="POST" action="">
          <div class="login-row2">
            <label class="login-label2">Email</label>
            <input class="login-input2" type="email" name="email" required>
          </div>

          <div class="login-row2">
            <label class="login-label2">Password</label>
            <input class="login-input2" type="password" name="password" required>
          </div>

          <button class="login-btn2" type="submit" name="login">Login</button>
          <a href="register.php" class="create-account-btn" style="margin-top:10px;">
  Create new account
</a>


        </form>

      </div>
    </div>

    <div class="login-bg login-bg-right"></div>

  </div>

</body>
</html>