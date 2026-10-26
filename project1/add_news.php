<?php

$pageTitle = 'Add News';

require_once 'header.php';
require_once 'sidebar.php';

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$errors = [];
$success = false;
$title = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if ($title === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Title must be 255 characters or fewer.';
    }

    if ($description === '') {
        $errors[] = 'Description is required.';
    }

    $imageFileName = null;

   
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'There was an error uploading the image.';

        } else {

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!isset($allowedTypes[$mimeType])) {

                $errors[] = 'Only JPG, PNG, GIF and WEBP images are allowed.';

            } elseif ($file['size'] > 5 * 1024 * 1024) {

                $errors[] = 'Image must be smaller than 5MB.';

            } else {

                $uploadDir = __DIR__ . '/uploads/news/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $imageFileName = 'news_' . uniqid() . '.' . $allowedTypes[$mimeType];

                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageFileName)) {
                    $errors[] = 'Unable to save uploaded image.';
                    $imageFileName = null;
                }
            }
        }
    }

    if (empty($errors)) {

        $sql = "INSERT INTO news
                (user_id, title, description, image, created_at)
                VALUES
                (:user_id, :title, :description, :image, NOW())";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $user_id,
            ':title' => $title,
            ':description' => $description,
            ':image' => $imageFileName
        ]);

      
        $success = true;
        $title = '';
        $description = '';
    }
}

?>

<div class="page-header">
    <div>
        <h1>Add News</h1>
        <p>Create a new news article to publish to users.</p>
    </div>

    <a href="news.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i>
        Back to News
    </a>
</div>

<?php if (!empty($errors)): ?>

<div class="alert alert-danger">

    <i class="fa-solid fa-circle-exclamation"></i>

    <div>

        <?php foreach ($errors as $error): ?>

            <div><?= htmlspecialchars($error) ?></div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>

<div class="card">

<form method="POST" enctype="multipart/form-data">

    <div class="form-group">

        <label>Title</label>

        <input
            type="text"
            name="title"
            class="form-control"
            maxlength="255"
            value="<?= htmlspecialchars($title) ?>"
            required>

    </div>

    <div class="form-group">

        <label>Description</label>

        <textarea
            name="description"
            class="form-control"
            required><?= htmlspecialchars($description) ?></textarea>

    </div>

    <div class="form-group">

        <label>Image (Optional)</label>

        <input
            type="file"
            name="image"
            class="form-control"
            accept=".jpg,.jpeg,.png,.gif,.webp">

    </div>

    <div class="form-group">

        <button class="btn btn-primary" type="submit">
            Save News
        </button>

        <a href="news.php" class="btn btn-outline">
            Cancel
        </a>

    </div>

</form>

</div>

<?php if ($success): ?>
<div id="successToast" class="toast-popup">
    <i class="fa-solid fa-circle-check"></i>
    <span>News uploaded successfully!</span>
</div>

<style>
.toast-popup {
    position: fixed;
    top: 24px;
    right: 24px;
    background: #22c55e;
    color: #fff;
    padding: 14px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 500;
    z-index: 9999;
    opacity: 0;
    transform: translateY(-10px);
    animation: toastIn 0.3s ease forwards, toastOut 0.4s ease forwards 3s;
}

@keyframes toastIn {
    to { opacity: 1; transform: translateY(0); }
}

@keyframes toastOut {
    to { opacity: 0; transform: translateY(-10px); }
}
</style>

<script>
    // Remove the toast from the DOM after the animation finishes
    setTimeout(function () {
        var toast = document.getElementById('successToast');
        if (toast) {
            toast.remove();
        }
    }, 3500);
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>