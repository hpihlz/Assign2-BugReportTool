# Bug Report Tool

A minimal web application for capturing bug reports with optional screenshots. The UI is built with PHP templates, persistent data is stored in SQLite, and the stack is packaged for containerised deployment behind nginx.

## Application Structure
- `html/index.php` – Landing page with navigation, version info, and quick links into the tool.
- `html/report.php` – Bug submission form, handling validation, image uploads/paste, and persistence into SQLite.
- `html/view_reports.php` – Read-only listing of reports with inline screenshot previews.
- `html/db.php` – Shared PDO bootstrap that creates/opens the SQLite database and initialises the schema.
- `html/css/styles.css` – Shared styling for layout, forms, buttons, notices, and screenshot previews.
- `html/assets/img/bug-icon.png` – Corner icon shown on the landing page card.
- `Dockerfile`, `docker-compose.yml`, `nginx/default.conf` – Container build + runtime configuration.
- `scripts/*.sh` – Helper scripts for Azure setup/teardown and diagnostics.

## Runtime Architecture
- **nginx (reverse proxy)** – Serves static assets from `/var/www/html` and forwards dynamic requests to PHP-FPM. Configured via `nginx/default.conf` with basic caching for CSS/JS and a 10 MB upload limit for screenshots.
- **php-fpm** – Runs the PHP application code. The Docker image installs `pdo_sqlite`, copies the `html/` tree, and keeps the writable `data/` directory for the SQLite database.
- **sqlite** – Lightweight relational database stored on a named Docker volume (`bug_reports_data`). The PHP layer handles migrations (table creation) on first run. For production the volume can be backed by Azure Files when deployed to Azure Container Apps.

## Local Development
1. Ensure Docker Desktop (or an equivalent Docker runtime) is running.
2. Start the stack:
   ```bash
   docker-compose up --build
   ```
3. Visit <http://localhost:8080>. nginx proxies requests to the php-fpm container; all changes in `html/` hot-reload because the directory is bind-mounted.
4. Stop with `Ctrl+C` (or `docker-compose down`). The SQLite data persists in the named volume.

## Deployment Pipeline
- Container images are tagged and pushed to Azure Container Registry via GitHub Actions (`.github/workflows/deploy.yml`).
- Azure Container Apps hosts the trio of containers with external ingress; scripts under `scripts/` provision the resource group, Container Apps environment, Log Analytics, ACR, and the GitHub OIDC Federated Credential.
- Secrets (Azure credentials, storage connection strings, etc.) live in GitHub Actions secrets and are injected into Container Apps—no credentials are committed to the repository.

## Maintenance
- Use `scripts/setup-oidc-and-aca.sh` to (re)create Azure infrastructure, and `scripts/cleanup-oidc-and-aca.sh` to tear it down.
- The SQLite database file is ignored by git (`html/data/`), so download backups directly from the running environment or the mounted storage share.
