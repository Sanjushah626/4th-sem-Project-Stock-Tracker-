<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter your username and password.";
    } else {
        // Look up user by username only — role comes from the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // Auto-redirect based on the role stored in the database
            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: hello.html");
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MyShareApp — Login</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
  --bg-app: #f8fafc;
  --bg-surface: #ffffff;
  --bg-surface-solid: #ffffff;
  --bg-elevated: #f1f5f9;
  --bg-row-alt: #f8fafc;
  --bg-row-hover: #e2e8f0;
  --border: #e2e8f0;
  --border-soft: #f1f5f9;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --text-muted: #94a3b8;
  --accent: #2563eb;
  --accent-dark: #1d4ed8;
  --accent-soft: rgba(37, 99, 235, 0.12);
  --green: #22c55e;
  --green-soft: rgba(34, 197, 94, 0.12);
  --red: #ef4444;
  --red-soft: rgba(239, 68, 68, 0.12);
  --amber: #f59e0b;
  --amber-soft: rgba(245, 158, 11, 0.14);
  --font-ui: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --font-num: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --primary: #2563eb;
  --primary-dark: #1d4ed8;
}

    * { box-sizing: border-box; }

    html, body {
      height: 100%;
      margin: 0;
    }

    body {
      background-color: var(--bg-app);
      color: var(--text-primary);
      font-family: var(--font-ui);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-card {
      background: var(--bg-surface-solid);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 32px 30px;
      width: 360px;
      max-width: 100%;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .login-card .brand {
      text-align: center;
      color: var(--accent);
      font-weight: 800;
      font-size: 20px;
      letter-spacing: -0.01em;
      margin-bottom: 4px;
    }

    .login-card h2 {
      text-align: center;
      margin: 0 0 24px;
      font-size: 20px;
      font-weight: 700;
      color: var(--text-primary);
    }

    .login-card label {
      display: block;
      margin-top: 14px;
      font-weight: 600;
      font-size: 13px;
      color: var(--text-secondary);
    }

    .login-card label:first-of-type { margin-top: 0; }

    .login-card input {
      width: 100%;
      padding: 10px 12px;
      margin-top: 6px;
      background: var(--bg-elevated);
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 14px;
      color: var(--text-primary);
      font-family: var(--font-ui);
    }

    .login-card input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-soft);
    }

    .login-card button {
      margin-top: 22px;
      width: 100%;
      padding: 11px;
      background: var(--accent);
      color: #ffffff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      font-size: 14px;
    }

    .login-card button:hover { background: var(--accent-dark); }

    .login-card .signup-link {
      text-align: center;
      margin-top: 18px;
      font-size: 13.5px;
      color: var(--text-muted);
    }

    .login-card .signup-link a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
    }

    .login-card .signup-link a:hover { color: var(--accent-dark); }

    .alert-error {
      background: var(--red-soft);
      color: var(--red);
      border: 1px solid rgba(240, 71, 94, 0.3);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 13.5px;
      margin-top: 18px;
    }
  </style>
</head>

<body>
  <div class="login-card">
    <div class="brand">MyShareApp</div>
    <h2>Log in to your account</h2>

    <?php if ($error): ?>
      <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Login</button>
    </form>

    <p class="signup-link">Don't have an account? <a href="signup.php">Create one</a></p>
  </div>
</body>
</html>