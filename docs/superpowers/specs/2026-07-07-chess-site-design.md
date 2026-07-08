# Chess Site — Design Spec
**Data:** 2026-07-07  
**Projekt:** Strona internetowa dla Taty (szachy, turnieje)

---

## Cel

Prosta strona internetowa dla miłośnika szachów. Tata (nieznający się na technikaliach) może samodzielnie publikować posty z relacjami z turniejów, wynikami i aktualnościami. Odwiedzający tylko czytają — brak rejestracji, komentarzy ani innych interaktywnych funkcji. SEO nieistotne (strona udostępniana znajomym linkiem).

---

## Stack

| Warstwa | Technologia |
|---|---|
| Backend | Laravel 13 |
| Panel admina | Filament 3 |
| Frontend | Vue 3 + Vite + Vue Router |
| Baza danych | PostgreSQL |
| Przechowywanie plików | Cloudflare R2 (S3-compatible) |
| Hosting | Railway (monorepo → 2 serwisy) |

---

## Struktura repozytorium

Jedno monorepo z dwoma podprojektami, dwa osobne serwisy na Railway:

```
chess/
├── backend/          # Laravel 13 + Filament 3
│   ├── app/
│   ├── database/
│   ├── .env.example
│   └── ...
├── frontend/         # Vue 3 + Vite
│   ├── src/
│   ├── .env.example
│   └── ...
└── docs/
    └── superpowers/
        └── specs/
```

---

## Model danych

### Tabela `posts`

| Kolumna | Typ | Opis |
|---|---|---|
| `id` | bigint PK | |
| `title` | string | Tytuł posta |
| `slug` | string unique | Auto-generowany z tytułu, używany w URL |
| `content` | longtext | Rich text HTML z edytora TipTap (tekst, nagłówki, tabelki) |
| `cover_image` | string nullable | URL głównego zdjęcia (Cloudflare R2) |
| `published_at` | timestamp nullable | null = szkic; data w przeszłości = opublikowany |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Tabela `post_images` (galeria)

| Kolumna | Typ | Opis |
|---|---|---|
| `id` | bigint PK | |
| `post_id` | bigint FK → posts.id | Kaskadowe usuwanie |
| `path` | string | Publiczny URL zdjęcia z Cloudflare R2 |
| `order` | integer | Kolejność w galerii (domyślnie 0) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## Backend — Laravel 13 + Filament 3

### API (publiczne, bez autoryzacji)

| Method | URL | Opis |
|---|---|---|
| GET | `/api/posts` | Lista opublikowanych postów: `id, title, slug, cover_image, excerpt, published_at` |
| GET | `/api/posts/{slug}` | Pełny post wraz z listą `images[]` (path, order) |

Tylko posty z `published_at` != null i <= now() są zwracane. Pole `excerpt` jest generowane automatycznie przez Laravel — pierwsze ~300 znaków `content` po usunięciu tagów HTML (Tata nie wypełnia go ręcznie). Odpowiedzi w JSON. CORS skonfigurowany na URL frontendu.

### Panel admina Filament (`/admin`)

Jeden zasób: **PostResource** z formularzem:
- `TextInput` — Tytuł (wymagany)
- `FileUpload` — Zdjęcie główne (opcjonalne, upload do R2)
- `TiptapEditor` — Treść WYSIWYG (tekst, nagłówki H1-H3, bold, italic, listy, tabele)
- `FileUpload::make()->multiple()` — Galeria (wiele plików, przeciąganie do zmiany kolejności, upload do R2)
- `DateTimePicker` — Data publikacji (null = szkic)

Slug generowany automatycznie z tytułu (można edytować ręcznie).

Tabela postów w panelu pokazuje: tytuł, status (szkic/opublikowany), datę publikacji.

### Autoryzacja admina

Jeden użytkownik tworzony przez `php artisan make:filament-user`. Brak rejestracji. Brak ról — jeden admin (Tata).

### Cloudflare R2

Konfiguracja przez standardowy S3 Flysystem driver z własnym endpointem R2. Cztery zmienne środowiskowe:

```
CLOUDFLARE_R2_ACCESS_KEY_ID=
CLOUDFLARE_R2_SECRET_ACCESS_KEY=
CLOUDFLARE_R2_BUCKET=
CLOUDFLARE_R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
```

Pliki są publicznie dostępne przez custom domain lub publiczny bucket URL.

---

## Frontend — Vue 3 + Vite

### Strony (Vue Router)

| Ścieżka | Komponent | Opis |
|---|---|---|
| `/` | `Home.vue` | Lista postów — każdy post jako preview: zdjęcie główne, tytuł, data, skrót treści (~300 znaków), przycisk "Czytaj więcej" |
| `/post/:slug` | `Post.vue` | Pełny post: tytuł, data, treść WYSIWYG, galeria zdjęć |
| `/o-mnie` | `About.vue` | Statyczna strona hardkodowana w Vue |
| `/kontakt` | `Contact.vue` | Statyczna strona hardkodowana w Vue |

### Struktura `src/`

```
src/
├── pages/
│   ├── Home.vue
│   ├── Post.vue
│   ├── About.vue
│   └── Contact.vue
├── components/
│   ├── Navbar.vue
│   ├── PostCard.vue     # preview posta: zdjęcie, tytuł, data, excerpt, "Czytaj więcej"
│   ├── PhotoGallery.vue # siatka miniaturek + lightbox
│   └── Footer.vue
├── services/
│   └── api.js           # fetch wrapper dla endpointów Laravel
└── router/
    └── index.js
```

### Galeria zdjęć

Pod treścią posta siatka miniaturek (np. 3 kolumny). Kliknięcie otwiera lightbox. Implementacja przez lekką bibliotekę (np. `vue-easy-lightbox`) lub własny prosty komponent.

### Zmienna środowiskowa

```
VITE_API_URL=http://localhost:8000  # lokalnie
VITE_API_URL=https://chess-backend.up.railway.app  # produkcja
```

---

## Lokalne środowisko

```bash
# Backend
cd backend && php artisan serve    # → http://localhost:8000
# + php artisan queue:work (jeśli potrzebne)

# Frontend
cd frontend && npm run dev         # → http://localhost:5173
```

Lokalna baza: PostgreSQL, baza `chess_local`.  
Zdjęcia lokalnie: można używać R2 (test bucket) lub lokalnego storage Laravel podczas developmentu.

---

## Deployment — Railway

### Serwis 1: `chess-backend` (Laravel)
- Root directory: `/backend`
- Build: Nixpacks (auto-wykrywa PHP, konfiguruje nginx + php-fpm automatycznie)
- Start command: obsługiwany przez Nixpacks (nginx + php-fpm)
- Zmienne środowiskowe: `DATABASE_URL`, `APP_KEY`, `APP_URL`, R2 credentials, `FRONTEND_URL` (dla CORS)

### Serwis 2: `chess-frontend` (Vue)
- Root directory: `/frontend`
- Build: `npm run build` → `/dist`
- Serwowany statycznie przez Railway Static Sites
- Zmienne środowiskowe: `VITE_API_URL`

### Serwis 3: `chess-db` (PostgreSQL)
- Istniejący serwis Railway Postgres
- `DATABASE_URL` wstrzykiwany automatycznie do backendu

---

## Co jest poza zakresem (YAGNI)

- Komentarze, rejestracja użytkowników
- Wielojęzyczność
- Kategorie/tagi postów
- SEO (meta tagi, sitemap) — można dodać później
- Wyszukiwarka
- Newsletter
