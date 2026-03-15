# CI/CD Setup – PHPStorm + GitHub + Docker

## Übersicht

```
PHPStorm → GitHub Push → GitHub Actions → GHCR (Docker Image) → SSH Deploy → VPS
```

**Pipeline-Stufen:**
1. **Lint** – PHP CodeSniffer + PHPStan
2. **Test** – PHPUnit (mit MySQL Service)
3. **Build** – Docker Image bauen & zu GitHub Container Registry (GHCR) pushen
4. **Deploy** – Per SSH auf dem VPS: `docker compose up -d`

---

## Einmaliges Setup

### 1. GitHub Secrets anlegen

Gehe zu: `Repo → Settings → Secrets and variables → Actions`

| Secret | Beschreibung |
|---|---|
| `SSH_HOST` | IP oder Domain deines VPS |
| `SSH_USER` | SSH-Benutzer (z.B. `deploy`) |
| `SSH_PRIVATE_KEY` | Privater SSH-Key (Inhalt von `~/.ssh/id_ed25519`) |
| `SSH_PORT` | SSH-Port (optional, Standard: 22) |

> `GITHUB_TOKEN` ist automatisch vorhanden – kein manuelles Anlegen nötig.

### 2. SSH-Key generieren (einmalig)

```bash
# Lokaler Rechner
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy

# Public Key auf den Server kopieren
ssh-copy-id -i ~/.ssh/github_actions_deploy.pub user@dein-server.de

# Inhalt des Private Keys → in GitHub Secret SSH_PRIVATE_KEY einfügen
cat ~/.ssh/github_actions_deploy
```

### 3. Server vorbereiten

```bash
# Docker & Docker Compose installieren (Ubuntu)
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# App-Verzeichnis anlegen
sudo mkdir -p /opt/app
sudo chown $USER:$USER /opt/app
cd /opt/app

# docker-compose.yml und .env hochladen
scp docker-compose.yml user@server:/opt/app/
scp .env.production user@server:/opt/app/.env

# GHCR Package auf "public" stellen oder Token hinterlegen:
# Repo → Packages → Package Settings → Change visibility
```

### 4. PHPStorm konfigurieren

**Docker Integration:**
- `Settings → Build, Execution, Deployment → Docker`
- Docker Desktop oder TCP Socket hinzufügen

**Remote PHP Interpreter (über Docker):**
- `Settings → PHP → CLI Interpreter → + → From Docker`
- Image: `app_local` (nach erstem `docker compose up`)

**Xdebug:**
- `Settings → PHP → Debug → Xdebug`
- Port: `9003`
- `Run → Edit Configurations → PHP Remote Debug`
- Server: `localhost`, Port `8080`

---

## Lokale Entwicklung

```bash
# Starten (nutzt docker-compose.override.yml automatisch)
docker compose up -d

# App verfügbar unter:
# http://localhost:8080     – App
# http://localhost:8025     – Mailpit (E-Mail Preview)

# Logs anschauen
docker compose logs -f app

# In den Container
docker compose exec app sh

# Tests lokal laufen lassen
docker compose exec app vendor/bin/phpunit
```

---

## Deployment-Flow

1. Code in `main` pushen (oder PR mergen)
2. GitHub Actions startet automatisch:
   - Lint → Tests → Docker Build → Deploy
3. Deployment dauert ca. 2–5 Minuten
4. Status in: `Repo → Actions`

### Manueller Deploy-Trigger

```bash
# Über GitHub CLI
gh workflow run ci-cd.yml --ref main
```

---

## Dateistruktur

```
.
├── .github/
│   └── workflows/
│       └── ci-cd.yml          ← GitHub Actions Pipeline
├── docker/
│   ├── nginx/
│   │   └── default.conf       ← Nginx Konfiguration
│   ├── php/
│   │   ├── php.ini            ← PHP Einstellungen
│   │   └── opcache.ini        ← OPcache (Production)
│   └── supervisor/
│       └── supervisord.conf   ← Nginx + PHP-FPM Manager
├── Dockerfile                 ← Multi-Stage Build
├── docker-compose.yml         ← Production
├── docker-compose.override.yml← Lokale Entwicklung (nicht committen!)
├── phpcs.xml                  ← PHP CodeSniffer Regeln
└── phpstan.neon               ← PHPStan Konfiguration
```

---

## Tipps

- `docker-compose.override.yml` in `.gitignore` eintragen
- `.env` niemals committen – `.env.example` stattdessen
- GHCR Images sind standardmäßig privat; entweder auf public stellen oder `docker login` im Deploy-Script nutzen (bereits im Workflow enthalten)
- Für Zero-Downtime: Traefik als Reverse Proxy ist bereits in `docker-compose.yml` konfiguriert
