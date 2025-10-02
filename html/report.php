<?php
$pdo = require __DIR__ . '/db.php';

$errors = [];
$success = false;
$name = '';
$email = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $screenshotData = $_POST['screenshot_data'] ?? '';

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid e-mail address is required.';
    }

    if ($description === '') {
        $errors[] = 'Please describe the bug.';
    }

    $imageBinary = null;
    $imageMime = null;
    $imageFilename = null;

    if (!empty($_FILES['screenshot']['tmp_name'])) {
        $tmpPath = $_FILES['screenshot']['tmp_name'];
        $imageBinary = file_get_contents($tmpPath);
        $imageMime = function_exists('mime_content_type') ? mime_content_type($tmpPath) : ($_FILES['screenshot']['type'] ?? null);
        $imageFilename = basename($_FILES['screenshot']['name']);
    } elseif ($screenshotData !== '') {
        if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/', $screenshotData, $matches)) {
            $imageMime = $matches[1];
            $imageBinary = base64_decode($matches[2]);
            $imageFilename = 'pasted-' . date('YmdHis');
        } else {
            $errors[] = 'Unable to read the pasted screenshot.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO bug_reports (reporter_name, reporter_email, description, screenshot, screenshot_mime, screenshot_filename) VALUES (:name, :email, :description, :screenshot, :mime, :filename)');
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':screenshot', $imageBinary, $imageBinary === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
        if ($imageMime !== null) {
            $stmt->bindValue(':mime', $imageMime, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':mime', null, PDO::PARAM_NULL);
        }
        if ($imageFilename !== null) {
            $stmt->bindValue(':filename', $imageFilename, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':filename', null, PDO::PARAM_NULL);
        }

        $stmt->execute();

        $success = true;
        $name = '';
        $email = '';
        $description = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Bug</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Report a Bug</h1>
        <?php if ($success): ?>
            <div class="notice success">Thank you! Your bug report has been saved.</div>
        <?php elseif (!empty($errors)): ?>
            <div class="notice error">
                <strong>There were problems with your submission:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p class="notice">Fill out the details below and paste or upload a screenshot if available.</p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="description">Bug description</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="screenshot">Screenshot</label>
            <input type="file" id="screenshot" name="screenshot" accept="image/*">
            <div id="pasteZone" class="contenteditable-box" contenteditable="true" spellcheck="false">
                Paste screenshot here (Ctrl/⌘ + V)
            </div>
            <p class="helper-text">You can paste directly into the box above or choose a file. Only the latest image will be kept.</p>
            <input type="hidden" id="screenshotData" name="screenshot_data">
            <img id="screenshotPreview" class="screenshot-preview" alt="Screenshot preview" style="display:none;">

            <div class="form-actions">
                <button class="button" type="submit">Send report</button>
                <a class="button secondary" href="index.php">Back to main page</a>
            </div>
        </form>
    </div>
    <script>
        const fileInput = document.getElementById('screenshot');
        const pasteZone = document.getElementById('pasteZone');
        const hiddenField = document.getElementById('screenshotData');
        const preview = document.getElementById('screenshotPreview');
        const MAX_PREVIEW_PERCENT = 0.94;

        function resetPreview() {
            preview.style.display = 'none';
            preview.src = '';
            preview.style.width = '';
            preview.style.height = '';
        }

        function showPreview(dataUrl) {
            preview.src = dataUrl;
            preview.style.display = 'block';
        }

        preview.addEventListener('load', function () {
            const parentWidth = preview.parentElement ? preview.parentElement.clientWidth : preview.naturalWidth;
            preview.style.height = 'auto';
            if (!preview.naturalWidth) {
                preview.style.width = 'auto';
                return;
            }
            const maxWidthPx = parentWidth ? parentWidth * MAX_PREVIEW_PERCENT : preview.naturalWidth;
            if (parentWidth && preview.naturalWidth > maxWidthPx) {
                preview.style.width = (MAX_PREVIEW_PERCENT * 100) + '%';
            } else {
                preview.style.width = preview.naturalWidth + 'px';
            }
        });

        function handleImageFile(file) {
            if (!file || !file.type.startsWith('image/')) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function (event) {
                hiddenField.value = event.target.result;
                showPreview(event.target.result);
                pasteZone.textContent = 'Image captured. Paste again to replace.';
            };
            reader.readAsDataURL(file);
        }

        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                handleImageFile(fileInput.files[0]);
            } else {
                hiddenField.value = '';
                resetPreview();
            }
        });

        pasteZone.addEventListener('paste', function (event) {
            const items = event.clipboardData.items;
            let imageFound = false;

            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    const file = item.getAsFile();
                    handleImageFile(file);
                    imageFound = true;
                    break;
                }
            }

            if (!imageFound) {
                setTimeout(() => {
                    pasteZone.textContent = 'Only images are supported here. Try pasting a screenshot.';
                }, 0);
            }

            event.preventDefault();
        });

        pasteZone.addEventListener('focus', function () {
            if (pasteZone.textContent.includes('Paste screenshot here')) {
                pasteZone.textContent = '';
            }
        });

        pasteZone.addEventListener('blur', function () {
            if (pasteZone.textContent.trim() === '' && hiddenField.value === '') {
                pasteZone.textContent = 'Paste screenshot here (Ctrl/⌘ + V)';
                resetPreview();
            }
        });
    </script>
</body>
</html>
