<?php
$pdo = require __DIR__ . '/db.php';

$stmt = $pdo->query('SELECT id, reporter_name, reporter_email, description, screenshot, screenshot_mime, screenshot_filename, created_at FROM bug_reports ORDER BY datetime(created_at) DESC');
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Reports</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Submitted Bug Reports</h1>
        <div class="form-actions" style="margin-bottom: 20px;">
            <a class="button" href="report.php">Report another bug</a>
            <a class="button secondary" href="index.php">Back to main page</a>
        </div>
        <?php if (empty($reports)): ?>
            <div class="table-placeholder">No bug reports yet. Be the first to submit one!</div>
        <?php else: ?>
            <div class="report-list">
                <?php foreach ($reports as $report): ?>
                    <article class="report-card">
                        <header>
                            <h2>Report #<?php echo (int) $report['id']; ?></h2>
                            <div class="report-meta">
                                Reported by <?php echo htmlspecialchars($report['reporter_name'], ENT_QUOTES, 'UTF-8'); ?>
                                (<?php echo htmlspecialchars($report['reporter_email'], ENT_QUOTES, 'UTF-8'); ?>)
                                on <?php echo htmlspecialchars($report['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </header>
                        <p class="report-description"><?php echo nl2br(htmlspecialchars($report['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php if (!empty($report['screenshot'])): ?>
                            <?php
                                $binary = $report['screenshot'];
                                if (is_resource($binary)) {
                                    $binary = stream_get_contents($binary);
                                }
                                $mime = $report['screenshot_mime'] ?: 'image/png';
                                $dataUri = 'data:' . $mime . ';base64,' . base64_encode($binary);
                                $escapedDataUri = htmlspecialchars($dataUri, ENT_QUOTES, 'UTF-8');
                                $dimensions = @getimagesizefromstring($binary);
                                $sizeAttributes = '';
                                if ($dimensions) {
                                    $sizeAttributes = sprintf(' width="%d" height="%d"', (int) $dimensions[0], (int) $dimensions[1]);
                                }
                            ?>
                            <figure>
                                <img class="screenshot-preview" src="<?php echo $escapedDataUri; ?>" alt="Screenshot for report #<?php echo (int) $report['id']; ?>"<?php echo $sizeAttributes; ?>>
                                <?php if (!empty($report['screenshot_filename'])): ?>
                                    <figcaption>Source: <?php echo htmlspecialchars($report['screenshot_filename'], ENT_QUOTES, 'UTF-8'); ?></figcaption>
                                <?php endif; ?>
                            </figure>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
