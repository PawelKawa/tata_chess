# Szachy Taty

Strona internetowa dla miłośnika szachów z panelem admina do zarządzania postami, zdjęciami i relacjami z turniejów.

## Stack

- **Backend:** Laravel 13 + Filament 3 (panel admina) + TipTap (edytor WYSIWYG)
- **Frontend:** Vue 3 + Vite + Vue Router
- **Baza danych:** PostgreSQL
- **Storage zdjęć:** Cloudflare R2 (S3-compatible)
- **Email:** Resend
- **Hosting:** Railway (dwa serwisy z jednego monorepo)

---

## Wymagania lokalne

- PHP 8.3+
- Composer
- Node.js 20+
- PostgreSQL 15+

---

## Uruchomienie lokalne

### 1. Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Edytuj `.env` — uzupełnij dane (patrz sekcja niżej), potem:

```bash
php artisan migrate
php artisan db:seed          # przykładowe posty
php artisan make:filament-user   # konto admina
php artisan serve
```

Backend dostępny na: **http://localhost:8000**
Panel admina: **http://localhost:8000/admin**

### 2. Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Frontend dostępny na: **http://localhost:5173**

---

## Zmienne środowiskowe — backend (`backend/.env`)

### Aplikacja

```dotenv
APP_NAME="Chess"
APP_ENV=local                    # local | production
APP_DEBUG=true                   # false na produkcji
APP_URL=http://localhost:8000
APP_TIMEZONE=Europe/Warsaw
```

### Baza danych (PostgreSQL)

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chess_local          # nazwa lokalnej bazy
DB_USERNAME=postgres             # domyślny user Homebrew to nazwa systemowa (np. pav)
DB_PASSWORD=                     # zazwyczaj puste lokalnie
```

Stwórz lokalną bazę:
```bash
psql -U postgres -c "CREATE DATABASE chess_local;"
# lub jeśli Homebrew:
createdb chess_local
```

### Storage (Cloudflare R2)

```dotenv
FILESYSTEM_DISK=r2

CLOUDFLARE_R2_ACCESS_KEY_ID=     # z panelu Cloudflare → R2 → API Tokens
CLOUDFLARE_R2_SECRET_ACCESS_KEY= # jak wyżej (pokazuje się tylko raz!)
CLOUDFLARE_R2_BUCKET=chess
CLOUDFLARE_R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://pub-<HASH>.r2.dev   # publiczny URL bucketu
```

Jak znaleźć:
- **Endpoint** → Cloudflare Dashboard → R2 → bucket → Settings → S3 API endpoint
- **URL** → Cloudflare Dashboard → R2 → bucket → Settings → Public Access (musi być włączone)
- **Tokeny** → Cloudflare Dashboard → R2 → Manage R2 API Tokens → Create Token

### Email (Resend)

```dotenv
MAIL_MAILER=resend

RESEND_API_KEY=re_xxxxxxxxxxxxx  # z resend.com → API Keys

MAIL_FROM_ADDRESS="noreply@twoja-domena.pl"  # domena musi być zweryfikowana w Resend
MAIL_FROM_NAME="Szachy Taty"
CONTACT_TO_EMAIL=email-taty@gmail.com        # tu przychodzą wiadomości z formularza
```

### CORS

```dotenv
FRONTEND_URL=http://localhost:5173   # URL frontendu (zmień jeśli Vite użył innego portu)
```

### Panel admina (dev)

```dotenv
FILAMENT_DEV_EMAIL=admin@chess.local    # pre-fill na stronie logowania (tylko lokalnie)
FILAMENT_DEV_PASSWORD=password123
```

---

## Zmienne środowiskowe — frontend (`frontend/.env`)

```dotenv
VITE_API_URL=http://localhost:8000   # URL backendu
```

---

## Przydatne komendy

```bash
# Odśwież bazę i załaduj przykładowe posty
php artisan migrate:fresh --seed

# Stwórz nowe konto admina
php artisan make:filament-user

# Wyczyść cache konfiguracji (po zmianie .env)
php artisan config:clear

# Testy
php artisan test                   # backend
npm run test:unit -- --run         # frontend
```

---

## Deployment (Railway)

1. Wrzuć repo na GitHub
2. Na Railway stwórz projekt → Add Service → GitHub Repo
3. Dodaj **dwa serwisy** z tego samego repo:
   - `chess-backend` → Root Directory: `/backend`
   - `chess-frontend` → Root Directory: `/frontend`
4. Dodaj **PostgreSQL** plugin (Railway automatycznie wstrzykuje `DATABASE_URL`)
5. Ustaw zmienne środowiskowe w każdym serwisie wg sekcji wyżej (zamiast `local` → `production`)

Szczegółowe instrukcje: [`backend/RAILWAY.md`](backend/RAILWAY.md)

---

## Struktura projektu

```
chess/
├── backend/          # Laravel 13 — API + panel admina Filament
├── frontend/         # Vue 3 — publiczny SPA
└── docs/
    └── superpowers/
        ├── specs/    # design spec
        └── plans/    # plan implementacji
```
