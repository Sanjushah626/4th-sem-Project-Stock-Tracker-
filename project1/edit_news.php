<?php

$pageTitle = 'Edit News';
require_once 'header.php';
require_once 'sidebar.php';

// Validate and fetch the requested news id
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: news.php');
    exit;
}
$newsId = (int) $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
$stmt->execute(['id' => $newsId]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    header('Location: news.php');
    exit;
}

$errors = [];
$title = $news['title'];
$description = $news['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // ---------- VALIDATION ----------
    if ($title === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Title must be 255 characters or fewer.';
    }

    if ($description === '') {
        $errors[] = 'Description is required.';
    }

    $imageFileName = $news['image']; // keep existing image by default

  
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'There was an error uploading the image.';
        } else {
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!isset($allowedTypes[$mimeType])) {
                $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Image must be smaller than 5MB.';
            } else {
                $ext = $allowedTypes[$mimeType];
                $newFileName = 'news_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads/news/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                    // Delete old image file if it exists
                    if (!empty($news['image']) && file_exists($uploadDir . $news['image'])) {
                        unlink($uploadDir . $news['image']);
                    }
                    $imageFileName = $newFileName;
                } else {
                    $errors[] = 'Failed to save the uploaded image.';
                }
            }
        }
    }

   
    if (empty($errors)) {
        $updateStmt = $pdo->prepare('UPDATE news SET title = :title, description = :description, image = :image WHERE id = :id');
        $updateStmt->execute([
            'title' => $title,
            'description' => $description,
            'image' => $imageFileName,
            'id' => $newsId,
        ]);

        header('Location: news.php?status=updated');
        exit;
    }
}
?>

<div class="page-header">
    <div>
        <h1>Edit News</h1>
        <p>Update the details of this news article.</p>
    </div>
    <a href="news.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Back to News
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div>
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="edit_news.php?id=<?php echo $newsId; ?>" enctype="multipart/form-data" novalidate>

        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="form-control"
                   value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
                   maxlength="255" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="form-group">
            <label>Image</label>

            <?php if (!empty($news['image'])): ?>
                <div style="margin-bottom:12px;">
                    <img src="uploads/news/<?php echo htmlspecialchars($news['image'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="Current image" style="max-width:200px; border-radius:12px; box-shadow:var(--shadow-sm);">
                    <p class="form-hint">Current image. Upload a new one below to replace it.</p>
                </div>
            <?php endif; ?>

            <div class="image-upload-box" id="imageUploadBox">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <p>Click or drag a new image here to replace</p>
                <p class="form-hint">JPG, PNG, GIF or WEBP, max 5MB</p>
            </div>
            <input type="file" id="newsImageInput" name="image" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">

            <div id="imagePreviewContainer">
                <img id="imagePreviewTag" src="" alt="Preview">
            </div>
        </div>

        <div class="form-group" style="display:flex; gap:12px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Update News
            </button>
            <a href="news.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
