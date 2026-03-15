# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Laravel (PHP 8.3) web application with full Docker containerization and an automated CI/CD pipeline deploying to a VPS via GitHub Actions.

**Stack:** Laravel · MySQL 8.0 · Redis 7 · Nginx + PHP-FPM · Supervisor · Traefik (SSL)

## Development Commands

**Install dependencies:**
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
```

**Local dev environment:**
```bash
docker compose up -d          # Start all services
docker compose logs -f app    # Follow app logs
docker compose exec app sh    # Shell into app container
```

**Linting & static analysis:**
```bash
vendor/bin/phpcs --standard=phpcs.xml           # PSR-12 code style (app/, config/, database/, routes/, tests/)
vendor/bin/phpstan analyse --no-progress        # Static analysis at level 6
```

**Tests:**
```bash
vendor/bin/phpunit --coverage-clover coverage.xml   # Full suite with coverage
docker compose exec app vendor/bin/phpunit          # Inside container
```

**Migrations:**
```bash
php artisan migrate --force
```

## Architecture

```
app/                  Laravel application code
config/               Laravel configuration
database/             Migrations, seeders, factories
routes/               Route definitions
tests/                PHPUnit test suite
public/               Web root (Nginx points here)
docker/
  nginx/default.conf  Reverse proxy; static file cache (1yr); blocks .env/.git
  php/php.ini         256M memory, 60s timeout, Europe/Berlin timezone
  php/opcache.ini     256MB bytecode cache, timestamp validation disabled
  supervisor/         Manages Nginx + PHP-FPM (both auto-restart, no daemon)
Dockerfile            Multi-stage build for production image
docker-compose.yml    Production orchestration (app, db, redis, traefik)
```

**Container layout:** A single `app` container runs both Nginx and PHP-FPM managed by Supervisor. Traefik sits in front handling SSL termination via automatic Let's Encrypt. The health check endpoint is `GET /health`.

**Local overrides:** `docker-compose.override.yml` is gitignored — use it for local customizations (Mailpit on :8025, Xdebug on :9003). It is picked up automatically by `docker compose`.

## CI/CD Pipeline (`.github/workflows/ci-cd.yml`)

Four sequential jobs triggered on push to `main`/`develop` and PRs to `main`:

1. **lint** — phpcs + phpstan
2. **test** — phpunit against a real MySQL service (credentials: root/root); uploads coverage to Codecov
3. **build** *(main only)* — builds multi-platform Docker image, pushes to GHCR with `sha-<commit>` and `latest` tags
4. **deploy** *(main only, requires environment approval)* — SSHs into VPS, pulls image, runs `docker compose up -d --remove-orphans`, runs migrations, prunes old images

**Required GitHub Secrets:** `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`, `SSH_PORT` (optional), `ACME_EMAIL` (Traefik Let's Encrypt).

**VPS app directory:** `/opt/app`

## Code Quality Rules

- **phpcs.xml:** PSR-12, PHP ≥ 8.0.3. Warning at 120 chars, error at 160 chars.
- **phpstan.neon:** Level 6. Paths: `app/`, `config/`, `database/`, `routes/`.
