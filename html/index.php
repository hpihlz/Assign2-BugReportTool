<?php
// Landing page for the bug report tool.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Report Tool</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to the Bug Report Tool</h1>
        <p>This is a simple website (alpha state) that will be served by nginx on Azure.</p>
        <div class="info">
            <h2>Version Information</h2>
            <p>Version: 1.0.1</p>
            <p>Environment: Development</p>
            <p>Last Updated: <span id="date"></span></p>
        </div>
        <div class="actions">
            <a class="button" href="report.php">Report bug</a>
            <a class="button secondary" href="view_reports.php">View all reports</a>
        </div>
    </div>
    <script>
        document.getElementById('date').textContent = new Date().toLocaleDateString();
    </script>
</body>
</html>
