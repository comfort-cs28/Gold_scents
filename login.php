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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,500&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/store.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-card {
            width: min(420px, 92%);
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 36px;
        }
        .login-card h2 {
            text-align: center;
            margin: 0 0 8px;
            color: var(--gold);
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .login-sub {
            text-align: center;
            color: var(--muted);
            margin: 0 0 28px;
        }
        .error-message {
            color: var(--danger);
            text-align: center;
            font-weight: 700;
            margin-bottom: 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Gold Scents</h2>
        <p class="login-sub">Staff Portal Authentication</p>

        <?php if ($error_message !== ''): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
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
            <button type="submit" class="btn checkout-btn">Log In</button>
        </form>

        <a href="index.html" class="back-link">← Return to Storefront</a>
    </div>
</body>
</html>
