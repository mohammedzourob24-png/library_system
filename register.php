<?php
session_start();
include 'config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // 0) Required fields
    if ($name === '' || $email === '' || $pass === '') {
        $error = "All fields are required.";
    }

    // 1) Validate email format (only if no previous error)
    if ($error === "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }

    // 2) Allowed domains (only if no previous error)
    if ($error === "") {

        $global_providers = [
            'gmail.com','hotmail.com','outlook.com','live.com','yahoo.com','icloud.com',
            'aol.com','proton.me','protonmail.com','zoho.com','gmx.com'
        ];

        $institution_domains = [
            'edu.ps' // add more if you want: 'unrwa.org', 'yourdomain.com'
        ];

        $domain = strtolower(substr(strrchr($email, "@"), 1));

        if (!in_array($domain, $global_providers) && !in_array($domain, $institution_domains)) {
            $error = "Please use a trusted email provider (Gmail/Outlook/Yahoo/iCloud/Proton) or an approved institution domain (edu.ps).";
        }
    }

    // 3) Continue only if no error: check exists + insert
    if ($error === "") {

        // check email exists
        $chk = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $chk->bind_param("s", $email);
        $chk->execute();
        $res = $chk->get_result();

        if ($res && $res->num_rows > 0) {
            $error = "Email already exists.";
        } else {

            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $role = "user";

            $ins = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $ins->bind_param("ssss", $name, $email, $hash, $role);

            if ($ins->execute()) {
                $_SESSION['flash_success'] = "Account created successfully. Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Failed to create account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="/library_system/assets/css/style.css">
</head>
<body class="login-page">
  <div class="login-wrap">
    <div class="login-card">
      <h2>Register</h2>
      <p>Create a user account</p>

      <?php if($error): ?>
        <div class="alert danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form class="form" method="POST" action="">
        <div class="row">
          <label class="label">Name</label>
          <input class="input" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
        </div>

        <div class="row">
          <label class="label">Email</label>
          <input class="input" type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
        </div>

        <div class="row">
          <label class="label">Password</label>
          <input class="input" type="password" name="password" required>
        </div>

        <button class="btn primary" type="submit" name="register">Create Account</button>
        <a class="btn" href="login.php" style="margin-top:10px; display:inline-block;">Back to Login</a>
      </form>
    </div>
  </div>
</body>
</html>