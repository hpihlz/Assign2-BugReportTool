# Bug Report Tool

A minimal PHP/SQLite web app for collecting bug reports with optional screenshots.

## Local Development

```bash
docker-compose up --build
```

The stack launches three containers:
- **bugtool-nginx** – serves static assets and proxies requests to PHP-FPM on port 8080.
- **bugtool-php** – runs PHP-FPM with the application code.
- **bugtool-sqlite** – keeps the shared SQLite data volume initialised and ready for backups or inspection.

Visit http://localhost:8080 to use the app. The SQLite database is stored in the named volume `bug_reports_data`.
