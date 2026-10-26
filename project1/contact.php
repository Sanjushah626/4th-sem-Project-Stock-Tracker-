<?php
/*
  Expected table (create this if it doesn't exist yet):

  CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  );
*/

session_start();
require 'db.php';

$logged_in = isset($_SESSION['user_id']);

// Pre-fill name/email for logged-in users
$prefill_name = '';
$prefill_email = '';

if ($logged_in) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $prefill_name = $user['username'] ?? '';
        $prefill_email = $user['email'] ?? '';
    }
}

$success = false;
$errors = [];

// Keep submitted values so the form doesn't clear on a validation error
$form = [
    'name' => $prefill_name,
    'email' => $prefill_email,
    'subject' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['name'] = trim($_POST['name'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['subject'] = trim($_POST['subject'] ?? '');
    $form['message'] = trim($_POST['message'] ?? '');

    if ($form['name'] === '') {
        $errors[] = "Please enter your name.";
    }

    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($form['message'] === '') {
        $errors[] = "Please enter a message.";
    }

    if (empty($errors)) {
        try {
          $stmt = $pdo->prepare(
    "INSERT INTO contacts(name, email, subject, message)
     VALUES (?, ?, ?, ?)"
);

$stmt->execute([
    $form['name'],
    $form['email'],
    $form['subject'],
    $form['message']
]);

            $success = true;
        
            $form = [
                'name' => $prefill_name,
                'email' => $prefill_email,
                'subject' => '',
                'message' => ''
            ];
        // } catch (Exception $e) {
        //     $errors[] = "Something went wrong saving your message. Please try again.";
         }
        catch (Exception $e) {
    die($e->getMessage());
}
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MyShareApp — Contact Us</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

    html { max-width: 100%; overflow-x: hidden; }

    body {
      background-color: var(--bg-app);
      color: var(--text-primary);
      font-family: var(--font-ui);
      max-width: 100vw;
      overflow-x: hidden;
    }

    header, nav, main, footer {
      max-width: 100%;
      overflow-x: hidden;
      box-sizing: border-box;
    }

    header { flex-wrap: wrap; }

    main {
      max-width: 640px;
      margin: 0 auto;
      padding: 0 16px 40px;
    }

    .page-heading {
      margin: 28px 0 6px;
    }

    .page-heading h2 {
      color: var(--text-primary);
      font-weight: 700;
      letter-spacing: -0.01em;
      margin: 0 0 6px;
    }

    .page-heading p {
      color: var(--text-muted);
      font-size: 13.5px;
      margin: 0;
    }

    .contact-card {
      background: var(--bg-surface-solid);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 28px 30px;
      margin-top: 20px;
    }

    .contact-card label {
      display: block;
      margin-top: 16px;
      font-weight: 600;
      font-size: 13px;
      color: var(--text-secondary);
    }

    .contact-card label:first-of-type { margin-top: 0; }

    .contact-card input,
    .contact-card textarea {
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

    .contact-card textarea {
      resize: vertical;
      min-height: 140px;
    }

    .contact-card input:focus,
    .contact-card textarea:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-soft);
    }

    .contact-card button {
      margin-top: 22px;
      width: 100%;
      padding: 12px;
      background: var(--accent);
      color: #06201c;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      font-size: 14px;
    }

    .contact-card button:hover { background: var(--accent-dark); }

    .alert {
      border-radius: 10px;
      padding: 14px 16px;
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
  <header>
    <h1>MyShareApp</h1>
    <nav>
      <a href="hello.html">Home</a>
      <a href="market.html">Market</a>
      <?php if ($logged_in): ?><a href="portfolio.php">Portfolio</a><?php endif; ?>
      <a href="news.php">News</a>
      <a href="contact.php">Contact Us</a>
    </nav>
    <div id="right-section">
      <div id="market-status" aria-live="polite">
        <span class="dot closed"></span> Checking status...
      </div>
      <?php if ($logged_in): ?>
        <button class="login-btn" onclick="window.location.href='logout.php'">Logout</button>
      <?php else: ?>
        <button class="login-btn" onclick="window.location.href='login.php'">Login</button>
      <?php endif; ?>
    </div>
  </header>

  <main>
    <div class="page-heading">
      <h2>Contact Us</h2>
      <p>Have a question, found a bug, or want to suggest something? Send us a message below.</p>
    </div>

    <div class="contact-card">

      <?php if ($success): ?>
        <div class="alert success">Thanks — your message has been sent. We'll get back to you soon.</div>
      <?php endif; ?>

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

      <form action="contact.php" method="POST">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($form['name']); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form['email']); ?>" required>

        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($form['subject']); ?>" placeholder="What's this about?">

        <label for="message">Message</label>
        <textarea id="message" name="message" required placeholder="Tell us what's on your mind..."><?php echo htmlspecialchars($form['message']); ?></textarea>

        <button type="submit">Send Message</button>
      </form>
    </div>
  </main>

  <footer class="foot" style="margin-top:80px;">
    <div class="footer-content">
      <p>© 2026 MyShareApp. All rights reserved.</p>
      <p>Data provided by NEPSE API | For Educational purposes only</p>
      <nav class="footer-nav">
        <a href="hello.html">Home</a> |
        <a href="market.html">Market</a> |
        <a href="news.php">News</a> |
        <a href="contact.php">Contact Us</a>
      </nav>
      <div class="social-icons">
        <a href="#">Facebook</a>
        <a href="#">Twitter</a>
        <a href="#">LinkedIn</a>
      </div>
    </div>
  </footer>
  <script>
        async function checkMarketStatus() {
      try {
        const response = await fetch("http://localhost:8000/IsNepseOpen");
        const data = await response.json();

        const statusDiv = document.getElementById("market-status");

        if (data.isOpen === "OPEN") {
          statusDiv.innerHTML = '<span class="dot open"></span> Market is Open';
        } else {
          statusDiv.innerHTML = '<span class="dot closed"></span> Market is Closed';
        }
      } catch (error) {
        console.error("Error fetching market status:", error);
        document.getElementById("market-status").innerHTML =
          '<span class="dot closed"></span> Error fetching status';
      }
    }

    checkMarketStatus();
    setInterval(checkMarketStatus, 60000);
  </script>
</body>
</html>
