<?php

$pageTitle = 'Contact Messages';
require_once 'header.php';
require_once 'sidebar.php';




$contacts = $pdo->query('SELECT id, name, email, subject, created_at FROM contacts ORDER BY created_at DESC')
                 ->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h1>Contact Messages</h1>
        <p>Messages submitted through the public Contact Us form.</p>
    </div>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Message deleted successfully.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>All Messages</h2>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" class="search-input" data-search-target="contactsTable" placeholder="Search messages...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="contactsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr><td colspan="5" class="no-data">No contact messages yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($contact['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                            <td class="action-buttons">
                                <a href="view_contact.php?id=<?php echo (int) $contact['id']; ?>"
                                   class="btn btn-outline btn-sm btn-icon" title="Read message">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="delete_contact.php?id=<?php echo (int) $contact['id']; ?>"
                                   class="btn btn-danger btn-sm btn-icon js-delete-btn"
                                   data-message="Delete the message from '<?php echo htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8'); ?>'?"
                                   title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
