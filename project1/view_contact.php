<?php

$pageTitle = 'View Message';
require_once 'header.php';
require_once 'sidebar.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: contacts.php');
    exit;
}
$contactId = (int) $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM contacts WHERE id = :id');
$stmt->execute(['id' => $contactId]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    header('Location: contacts.php');
    exit;
}
?>

<div class="page-header">
    <div>
        <h1>Contact Message</h1>
        <p>Full details of the submitted message.</p>
    </div>
    <a href="contacts.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Back to Messages
    </a>
</div>

<div class="card" style="max-width:640px;">
    <div class="detail-list">
        <div class="detail-row">
            <span class="detail-label">Name</span>
            <span class="detail-value"><?php echo htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value">
                <a href="mailto:<?php echo htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Subject</span>
            <span class="detail-value"><?php echo htmlspecialchars($contact['subject'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Message</span>
            <span class="detail-value" style="white-space:pre-wrap;"><?php echo htmlspecialchars($contact['message'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date Received</span>
            <span class="detail-value"><?php echo date('F d, Y \a\t g:i A', strtotime($contact['created_at'])); ?></span>
        </div>
    </div>

    <div class="form-group" style="margin-top:22px; display:flex; gap:12px;">
        <a href="mailto:<?php echo htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8'); ?>?subject=<?php echo rawurlencode('Re: ' . $contact['subject']); ?>"
           class="btn btn-primary" title="Reply to <?php echo htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <i class="fa-solid fa-reply"></i> Reply
        </a>
        <a href="delete_contact.php?id=<?php echo (int) $contact['id']; ?>"
           class="btn btn-danger js-delete-btn"
           data-message="Delete this message from '<?php echo htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8'); ?>'?">
            <i class="fa-solid fa-trash"></i> Delete Message
        </a>
        <a href="contacts.php" class="btn btn-outline">Back</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>