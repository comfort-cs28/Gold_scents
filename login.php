<?php
ob_start();
session_start();

require_once __DIR__ . '/config/auth_config.php';

if (isset($_SESSION[AdminAuth::SESSION_KEY]) && $_SESSION[AdminAuth::SESSION_KEY] === true) {
    header('Location: admin.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $username_matches = hash_equals(AdminAuth::USERNAME, $username);
    $password_matches = password_verify($password, AdminAuth::PASSWORD_HASH);

    if ($username_matches && $password_matches) {
        $_SESSION[AdminAuth::SESSION_KEY] = true;
        header('Location: admin.php');
        exit;
    }

    $error_message = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Gold Scents</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/store.css">
    <link rel="stylesheet" href="css/store-pages.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
    <div class="login-shell">
        <div class="login-brand">
            <img class="logo-image" src="image/gold_scents_logo.png" alt="">
            <h1 class="logo-text">gold<span>scents</span></h1>
            <small>Fine Fragrances</small>
        </div>

        <div class="login-panel">
            <p class="login-kicker">Staff Portal</p>
            <h2 class="login-title">Sign in</h2>
            <div class="login-rule" aria-hidden="true"></div>

            <?php if ($error_message !== ''): ?>
                <div class="login-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn checkout-btn login-submit">Log In</button>
            </form>

            <a href="index.html" class="login-back">← Return to storefront</a>
        </div>
    </div>
</body>
</html>
