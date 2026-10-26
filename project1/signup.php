<?php
session_start();
require 'db.php';

$errors = [];
$success = false;

$form = [
    'username' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = 'user';
    $form['username'] = trim($_POST['username'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($form['username'] === '') {
        $errors[] = "Please enter a username.";
    }

    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password === '' || strlen($password) < 4) {
        $errors[] = "Password must be at least 4 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (role, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$role, $form['username'], $form['email'], $hashed_password]);

            $success = true;
            $form = ['username' => '', 'email' => ''];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "That username or email is already registered.";
            } else {
                $errors[] = "Something went wrong creating your account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MyShareApp — Create Account</title>
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

    .signup-card {
      background: var(--bg-surface-solid);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 32px 30px;
      width: 380px;
      max-width: 100%;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .signup-card .brand {
      text-align: center;
      color: var(--accent);
      font-weight: 800;
      font-size: 20px;
      letter-spacing: -0.01em;
      margin-bottom: 4px;
    }

    .signup-card h2 {
      text-align: center;
      margin: 0 0 24px;
      font-size: 20px;
      font-weight: 700;
      color: var(--text-primary);
    }

    .signup-card label {
      display: block;
      margin-top: 14px;
      font-weight: 600;
      font-size: 13px;
      color: var(--text-secondary);
    }

    .signup-card label:first-of-type { margin-top: 0; }

    .signup-card input {
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

    .signup-card input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-soft);
    }

    .signup-card small.hint {
      display: block;
      margin-top: 4px;
      color: var(--text-muted);
      font-size: 12px;
    }

    .signup-card button {
      margin-top: 22px;
      width: 100%;
      padding: 11px;
      background: var(--accent);
      color: #06201c;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      font-size: 14px;
    }

    .signup-card button:hover { background: var(--accent-dark); }

    .signup-card .login-link {
      text-align: center;
      margin-top: 18px;
      font-size: 13.5px;
      color: var(--text-muted);
    }

    .signup-card .login-link a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
    }

    .signup-card .login-link a:hover { color: var(--accent-dark); }

    .alert {
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 13.5px;
      margin-top: 18px;
      line-height: 1.5;
    }

    .alert.success {
      background: var(--green-soft);
      color: var(--green);
      border: 1px solid rgba(47, 208, 108, 0.3);
    }

    .alert.error {
      background: var(--red-soft);
      color: var(--red);
      border: 1px solid rgba(240, 71, 94, 0.3);
    }

    .alert ul {
      margin: 4px 0 0 18px;
      padding: 0;
    }
  </style>
</head>

<body>
  <div class="signup-card">
    <div class="brand">MyShareApp</div>
    <h2>Create your account</h2>

    <?php if ($success): ?>
      <div class="alert success">
        Account created successfully — you can <a href="login.php" style="color:inherit; font-weight:700;">log in here</a>.
      </div>
    <?php else: ?>

      <?php if (!empty($errors)): ?>
        <div class="alert error">
          Please fix the following:
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="signup.php" method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($form['username']); ?>" required autofocus>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form['email']); ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="4">
        <small class="hint">At least 6 characters.</small>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="4">

        <button type="submit">Register</button>
      </form>

      <p class="login-link">Already have an account? <a href="login.php">Log in</a></p>

    <?php endif; ?>
  </div>
</body>
</html>