# Railway Deployment — Backend

## Zmienne środowiskowe do ustawienia w Railway:

| Zmienna | Wartość |
|---|---|
| APP_ENV | production |
| APP_KEY | (generuj: php artisan key:generate --show) |
| APP_URL | https://chess-backend.up.railway.app |
| DB_CONNECTION | pgsql |
| DATABASE_URL | (automatycznie z Railway Postgres) |
| FRONTEND_URL | https://chess-frontend.up.railway.app |
| FILESYSTEM_DISK | r2 |
| CLOUDFLARE_R2_ACCESS_KEY_ID | (z panelu Cloudflare) |
| CLOUDFLARE_R2_SECRET_ACCESS_KEY | (z panelu Cloudflare) |
| CLOUDFLARE_R2_BUCKET | chess |
| CLOUDFLARE_R2_ENDPOINT | https://<ID>.r2.cloudflarestorage.com |
| CLOUDFLARE_R2_URL | https://<publiczny-url-bucketa> |

## Konfiguracja serwisów w Railway:

### Serwis 1: chess-backend (Laravel)
- Source: GitHub repo, Root Directory: `/backend`
- Builder: Nixpacks (auto-wykrywa PHP)
- Start Command: z Procfile

### Serwis 2: chess-frontend (Vue)
- Source: GitHub repo, Root Directory: `/frontend`
- Builder: Nixpacks (Node.js)
- Build Command: `npm run build`
- Start Command: `npx serve dist`

### Serwis 3: chess-db (PostgreSQL)
- Dodaj PostgreSQL plugin w Railway
- DATABASE_URL wstrzykiwany automatycznie do backendu

## Pierwsze kroki po deploymencie:

1. Ustaw wszystkie zmienne środowiskowe w Railway
2. Stwórz konto admina:
   ```
   php artisan make:filament-user
   ```
   (przez Railway shell lub local artisan z produkcyjną bazą)
3. Zaktualizuj `frontend/.env.production` z prawdziwym URL backendu i zrób redeploy frontendu

## Konfiguracja Cloudflare R2 (jednorazowo):
1. Zaloguj się na dash.cloudflare.com
2. R2 → Create bucket → nazwa: `chess`
3. Bucket Settings → Public Access → Allow Access
4. Manage R2 API Tokens → Create Token (Object Read & Write dla bucketa `chess`)
5. Skopiuj Access Key ID, Secret Access Key, Endpoint URL
