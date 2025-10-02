<?php
// Provides a lightweight SQLite-backed PDO connection for the bug report tool.

$databaseDirectory = __DIR__ . '/data';
$databasePath = $databaseDirectory . '/bug_reports.sqlite';

if (!is_dir($databaseDirectory)) {
    mkdir($databaseDirectory, 0775, true);
}

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS bug_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reporter_name TEXT NOT NULL,
    reporter_email TEXT NOT NULL,
    description TEXT NOT NULL,
    screenshot BLOB,
    screenshot_mime TEXT,
    screenshot_filename TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

return $pdo;
