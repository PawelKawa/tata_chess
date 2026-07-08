# Chess Site — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Zbudować stronę szachową z panelem admina Filament (Laravel 13) i SPA Vue 3 w monorepo, hostowaną na Railway.

**Architecture:** Laravel 13 wystawia publiczne API JSON (`GET /api/posts`, `GET /api/posts/{slug}`) oraz panel Filament pod `/admin`. Vue 3 SPA pobiera dane z API i renderuje posty z treścią WYSIWYG i galerią zdjęć. Pliki przechowywane na Cloudflare R2 (S3-compatible).

**Tech Stack:** Laravel 13, Filament 3, awcodes/filament-tiptap-editor, Vue 3, Vite, Vue Router, PostgreSQL, Cloudflare R2

---

## Mapa plików

```
chess/
├── backend/
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Post.php                          # Model z relacją images(), scope published(), accessor excerpt
│   │   │   └── PostImage.php                     # Model z relacją post()
│   │   ├── Http/Controllers/Api/
│   │   │   └── PostController.php                # index() i show() — publiczne endpointy
│   │   └── Filament/Resources/
│   │       ├── PostResource.php                  # Formularz i tabela w panelu admina
│   │       └── PostResource/Pages/
│   │           ├── ListPosts.php
│   │           ├── CreatePost.php                # afterCreate() → zapis gallery do post_images
│   │           └── EditPost.php                  # afterSave() + mutateFormDataBeforeFill()
│   ├── config/
│   │   └── filesystems.php                       # Dodać dysk 'r2'
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── xxxx_create_posts_table.php
│   │   │   └── xxxx_create_post_images_table.php
│   │   └── factories/
│   │       ├── PostFactory.php
│   │       └── PostImageFactory.php
│   ├── routes/
│   │   └── api.php                               # Dodać dwa endpointy
│   └── tests/Feature/Api/
│       └── PostApiTest.php                       # Testy feature dla API
└── frontend/
    ├── src/
    │   ├── pages/
    │   │   ├── Home.vue                          # Lista postów z excerpt
    │   │   ├── Post.vue                          # Pełny post + galeria
    │   │   ├── About.vue                         # Statyczna strona
    │   │   └── Contact.vue                       # Statyczna strona
    │   ├── components/
    │   │   ├── Navbar.vue
    │   │   ├── Footer.vue
    │   │   ├── PostCard.vue                      # Preview posta na Home
    │   │   └── PhotoGallery.vue                  # Siatka miniaturek + lightbox
    │   ├── services/
    │   │   └── api.js                            # fetch wrapper
    │   └── router/
    │       └── index.js
    ├── .env.example
    └── vite.config.js
```

---

## Task 1: Inicjalizacja monorepo

**Files:**
- Create: `chess/.gitignore`
- Create: `chess/README.md`

- [ ] **Krok 1: Zainicjuj git i dodaj .gitignore**

```bash
cd /Users/pav/Desktop/chess
git init
cat > .gitignore << 'EOF'
# Backend
backend/.env
backend/vendor/
backend/storage/logs/
backend/storage/app/
backend/bootstrap/cache/

# Frontend
frontend/node_modules/
frontend/dist/
frontend/.env
frontend/.env.local

# System
.DS_Store
EOF
```

- [ ] **Krok 2: Commit**

```bash
git add .gitignore
git commit -m "chore: init monorepo"
```

---

## Task 2: Inicjalizacja Laravel 13 backend

**Files:**
- Create: `backend/` (cały projekt Laravel)
- Modify: `backend/.env`

- [ ] **Krok 1: Utwórz projekt Laravel 13**

```bash
cd /Users/pav/Desktop/chess
composer create-project laravel/laravel:"^13.0" backend
```

- [ ] **Krok 2: Skonfiguruj .env dla PostgreSQL**

Edytuj `backend/.env` — zmień sekcję DB:

```dotenv
APP_NAME="Chess"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chess_local
DB_USERNAME=postgres
DB_PASSWORD=

FILESYSTEM_DISK=r2
```

- [ ] **Krok 3: Utwórz lokalną bazę danych**

```bash
psql -U postgres -c "CREATE DATABASE chess_local;"
```

Oczekiwane: `CREATE DATABASE`

- [ ] **Krok 4: Wygeneruj APP_KEY i zweryfikuj połączenie**

```bash
cd backend
php artisan key:generate
php artisan migrate
```

Oczekiwane: migracje domyślnych tabel Laravel (`users`, `cache`, `jobs`) przeszły.

- [ ] **Krok 5: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/
git commit -m "chore: init Laravel 13 backend"
```

---

## Task 3: Migracje i modele Post + PostImage

**Files:**
- Create: `backend/database/migrations/xxxx_create_posts_table.php`
- Create: `backend/database/migrations/xxxx_create_post_images_table.php`
- Create: `backend/app/Models/Post.php`
- Create: `backend/app/Models/PostImage.php`

- [ ] **Krok 1: Wygeneruj migracje**

```bash
cd backend
php artisan make:migration create_posts_table
php artisan make:migration create_post_images_table
```

- [ ] **Krok 2: Wypełnij migrację posts**

Otwórz `backend/database/migrations/xxxx_create_posts_table.php` i zastąp metodę `up()`:

```php
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();
        $table->longText('content')->nullable();
        $table->string('cover_image')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
}
```

- [ ] **Krok 3: Wypełnij migrację post_images**

Otwórz `backend/database/migrations/xxxx_create_post_images_table.php` i zastąp metodę `up()`:

```php
public function up(): void
{
    Schema::create('post_images', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->string('path');
        $table->integer('order')->default(0);
        $table->timestamps();
    });
}
```

- [ ] **Krok 4: Uruchom migracje**

```bash
php artisan migrate
```

Oczekiwane: `posts` i `post_images` tabele utworzone.

- [ ] **Krok 5: Utwórz model Post**

Zastąp zawartość `backend/app/Models/Post.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'content', 'cover_image', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('order');
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags((string) $this->content), 300);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }
}
```

- [ ] **Krok 6: Utwórz model PostImage**

Zastąp zawartość `backend/app/Models/PostImage.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImage extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'path', 'order'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
```

- [ ] **Krok 7: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/database/migrations/ backend/app/Models/
git commit -m "feat: add Post and PostImage models with migrations"
```

---

## Task 4: Factories dla testów

**Files:**
- Create: `backend/database/factories/PostFactory.php`
- Create: `backend/database/factories/PostImageFactory.php`

- [ ] **Krok 1: Utwórz PostFactory**

```bash
cd backend
php artisan make:factory PostFactory --model=Post
```

Zastąp zawartość `backend/database/factories/PostFactory.php`:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'title'        => $title,
            'slug'         => Str::slug($title),
            'content'      => '<p>' . implode('</p><p>', $this->faker->paragraphs(3)) . '</p>',
            'cover_image'  => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(['published_at' => now()]);
    }
}
```

- [ ] **Krok 2: Utwórz PostImageFactory**

```bash
php artisan make:factory PostImageFactory --model=PostImage
```

Zastąp zawartość `backend/database/factories/PostImageFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'path'    => 'galleries/' . $this->faker->uuid() . '.jpg',
            'order'   => 0,
        ];
    }
}
```

- [ ] **Krok 3: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/database/factories/
git commit -m "feat: add PostFactory and PostImageFactory"
```

---

## Task 5: Testy Feature API (TDD — napisz testy przed implementacją)

**Files:**
- Create: `backend/tests/Feature/Api/PostApiTest.php`

- [ ] **Krok 1: Utwórz plik testu**

```bash
cd backend
mkdir -p tests/Feature/Api
```

Utwórz `backend/tests/Feature/Api/PostApiTest.php`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('r2');
    }

    // --- GET /api/posts ---

    public function test_posts_index_returns_only_published(): void
    {
        Post::factory()->published()->create(['title' => 'Opublikowany']);
        Post::factory()->create(['title' => 'Szkic', 'published_at' => null]);
        Post::factory()->create(['title' => 'Przyszły', 'published_at' => now()->addDay()]);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.title', 'Opublikowany');
    }

    public function test_posts_index_returns_excerpt_not_content(): void
    {
        Post::factory()->published()->create([
            'content' => '<p>' . str_repeat('a', 500) . '</p>',
        ]);

        $response = $this->getJson('/api/posts');

        $data = $response->json('0');
        $this->assertArrayHasKey('excerpt', $data);
        $this->assertArrayNotHasKey('content', $data);
        $this->assertLessThanOrEqual(303, strlen($data['excerpt']));
    }

    public function test_posts_index_returns_required_fields(): void
    {
        Post::factory()->published()->create();

        $response = $this->getJson('/api/posts');

        $response->assertOk()
                 ->assertJsonStructure([['id', 'title', 'slug', 'cover_image', 'excerpt', 'published_at']]);
    }

    public function test_posts_index_ordered_by_published_at_desc(): void
    {
        Post::factory()->published()->create(['title' => 'Starszy', 'published_at' => now()->subDays(2)]);
        Post::factory()->published()->create(['title' => 'Nowszy',  'published_at' => now()->subDay()]);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
                 ->assertJsonPath('0.title', 'Nowszy')
                 ->assertJsonPath('1.title', 'Starszy');
    }

    // --- GET /api/posts/{slug} ---

    public function test_posts_show_returns_full_post(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->getJson("/api/posts/{$post->slug}");

        $response->assertOk()
                 ->assertJsonStructure(['id', 'title', 'slug', 'cover_image', 'content', 'published_at', 'images'])
                 ->assertJsonPath('slug', $post->slug);
    }

    public function test_posts_show_returns_images_ordered(): void
    {
        $post = Post::factory()->published()->create();
        PostImage::factory()->create(['post_id' => $post->id, 'path' => 'img2.jpg', 'order' => 1]);
        PostImage::factory()->create(['post_id' => $post->id, 'path' => 'img1.jpg', 'order' => 0]);

        $response = $this->getJson("/api/posts/{$post->slug}");

        $images = $response->json('images');
        $this->assertCount(2, $images);
        $this->assertEquals('img1.jpg', $images[0]['path']);
        $this->assertEquals('img2.jpg', $images[1]['path']);
    }

    public function test_posts_show_returns_404_for_draft(): void
    {
        $post = Post::factory()->create(['published_at' => null]);

        $this->getJson("/api/posts/{$post->slug}")->assertNotFound();
    }

    public function test_posts_show_returns_404_for_nonexistent_slug(): void
    {
        $this->getJson('/api/posts/nie-istnieje')->assertNotFound();
    }
}
```

- [ ] **Krok 2: Uruchom testy — muszą FAILOWAĆ (kontroler jeszcze nie istnieje)**

```bash
cd backend
php artisan test tests/Feature/Api/PostApiTest.php
```

Oczekiwane: wszystkie testy FAIL z błędem `404` lub `Route not found`.

- [ ] **Krok 3: Commit testów**

```bash
cd /Users/pav/Desktop/chess
git add backend/tests/
git commit -m "test: add failing PostApiTest (TDD red phase)"
```

---

## Task 6: API PostController + routes

**Files:**
- Create: `backend/app/Http/Controllers/Api/PostController.php`
- Modify: `backend/routes/api.php`

- [ ] **Krok 1: Utwórz katalog i kontroler**

```bash
cd backend
mkdir -p app/Http/Controllers/Api
```

Utwórz `backend/app/Http/Controllers/Api/PostController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::published()
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(fn(Post $post) => [
                'id'           => $post->id,
                'title'        => $post->title,
                'slug'         => $post->slug,
                'cover_image'  => $post->cover_image
                                    ? Storage::disk('r2')->url($post->cover_image)
                                    : null,
                'excerpt'      => $post->excerpt,
                'published_at' => $post->published_at?->toIso8601String(),
            ]);

        return response()->json($posts);
    }

    public function show(string $slug): JsonResponse
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with('images')
            ->firstOrFail();

        return response()->json([
            'id'           => $post->id,
            'title'        => $post->title,
            'slug'         => $post->slug,
            'cover_image'  => $post->cover_image
                                ? Storage::disk('r2')->url($post->cover_image)
                                : null,
            'content'      => $post->content,
            'published_at' => $post->published_at?->toIso8601String(),
            'images'       => $post->images->map(fn($img) => [
                'path'  => Storage::disk('r2')->url($img->path),
                'order' => $img->order,
            ]),
        ]);
    }
}
```

- [ ] **Krok 2: Dodaj routes do api.php**

Zastąp zawartość `backend/routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
```

- [ ] **Krok 3: Uruchom testy — muszą PRZEJŚĆ**

```bash
cd backend
php artisan test tests/Feature/Api/PostApiTest.php
```

Oczekiwane: wszystkie 8 testów PASS.

> Uwaga: testy dla `cover_image` i `images.path` mogą zwracać błąd z Storage — jeśli tak, skonfiguruj R2 lub zmień na `local` disk w `.env.testing`. Jeśli testy przechodzą — nie dotykaj.

- [ ] **Krok 4: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/app/Http/Controllers/ backend/routes/api.php
git commit -m "feat: add PostController and API routes (TDD green phase)"
```

---

## Task 7: Konfiguracja CORS

**Files:**
- Modify: `backend/config/cors.php`

- [ ] **Krok 1: Sprawdź aktualny cors.php**

```bash
cat backend/config/cors.php
```

- [ ] **Krok 2: Zaktualizuj allowed_origins**

W `backend/config/cors.php` zmień wartość `allowed_origins`:

```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
```

- [ ] **Krok 3: Dodaj FRONTEND_URL do .env**

W `backend/.env` dodaj:

```dotenv
FRONTEND_URL=http://localhost:5173
```

- [ ] **Krok 4: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/config/cors.php
git commit -m "feat: configure CORS for Vue frontend"
```

---

## Task 8: Konfiguracja Cloudflare R2

**Files:**
- Modify: `backend/config/filesystems.php`
- Modify: `backend/.env`

- [ ] **Krok 1: Zainstaluj AWS SDK (wymagany przez S3 Flysystem driver)**

```bash
cd backend
composer require league/flysystem-aws-s3-v3
```

- [ ] **Krok 2: Dodaj dysk r2 do config/filesystems.php**

W `backend/config/filesystems.php` w tablicy `'disks'` dodaj:

```php
'r2' => [
    'driver'                  => 's3',
    'key'                     => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
    'secret'                  => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
    'region'                  => 'auto',
    'bucket'                  => env('CLOUDFLARE_R2_BUCKET'),
    'url'                     => env('CLOUDFLARE_R2_URL'),
    'endpoint'                => env('CLOUDFLARE_R2_ENDPOINT'),
    'use_path_style_endpoint' => false,
    'throw'                   => false,
    'visibility'              => 'public',
],
```

- [ ] **Krok 3: Dodaj R2 zmienne do .env**

W `backend/.env` dodaj (wypełnisz po założeniu konta R2):

```dotenv
CLOUDFLARE_R2_ACCESS_KEY_ID=
CLOUDFLARE_R2_SECRET_ACCESS_KEY=
CLOUDFLARE_R2_BUCKET=chess
CLOUDFLARE_R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://<BUCKET_PUBLIC_URL>
```

- [ ] **Krok 4: Dodaj .env.example z placeholderami**

```bash
cd backend
cp .env .env.example
# Usuń wartości sekretów z .env.example ręcznie
```

W `.env.example` zmień wrażliwe wartości na puste lub placeholder:

```dotenv
CLOUDFLARE_R2_ACCESS_KEY_ID=
CLOUDFLARE_R2_SECRET_ACCESS_KEY=
CLOUDFLARE_R2_BUCKET=chess
CLOUDFLARE_R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://<BUCKET_PUBLIC_URL>
```

- [ ] **Krok 5: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/config/filesystems.php backend/.env.example
git commit -m "feat: configure Cloudflare R2 storage disk"
```

---

## Task 9: Instalacja Filament 3

**Files:**
- Modify: `backend/` (instalacja paczek)

- [ ] **Krok 1: Zainstaluj Filament 3 i TipTap editor**

```bash
cd backend
composer require filament/filament:"^3.0"
composer require awcodes/filament-tiptap-editor:"^3.0"
```

- [ ] **Krok 2: Zainstaluj panel Filament**

```bash
php artisan filament:install --panels
```

Pojawi się pytanie o ID panelu — wpisz `admin` i Enter.

- [ ] **Krok 3: Opublikuj konfigurację TipTap**

```bash
php artisan vendor:publish --tag=filament-tiptap-editor-config
```

- [ ] **Krok 4: Uruchom migracje (Filament może dodawać tabele)**

```bash
php artisan migrate
```

- [ ] **Krok 5: Utwórz konto admina (Taty)**

```bash
php artisan make:filament-user
```

Wpisz: Name: `Tata`, Email: adres email Taty, Password: bezpieczne hasło.

- [ ] **Krok 6: Zweryfikuj że panel działa**

```bash
php artisan serve
```

Wejdź na `http://localhost:8000/admin` i zaloguj się. Oczekiwane: pusty panel Filament.

- [ ] **Krok 7: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/
git commit -m "feat: install Filament 3 + TipTap editor"
```

---

## Task 10: PostResource — panel admina Filament

**Files:**
- Create: `backend/app/Filament/Resources/PostResource.php`
- Create: `backend/app/Filament/Resources/PostResource/Pages/ListPosts.php`
- Create: `backend/app/Filament/Resources/PostResource/Pages/CreatePost.php`
- Create: `backend/app/Filament/Resources/PostResource/Pages/EditPost.php`

- [ ] **Krok 1: Wygeneruj PostResource**

```bash
cd backend
php artisan make:filament-resource Post --generate
```

- [ ] **Krok 2: Zastąp PostResource.php**

Zastąp całą zawartość `backend/app/Filament/Resources/PostResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $modelLabel       = 'Post';
    protected static ?string $pluralModelLabel = 'Posty';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Tytuł')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(
                    fn(string $operation, $state, Forms\Set $set) =>
                        $operation === 'create'
                            ? $set('slug', Str::slug($state))
                            : null
                ),

            Forms\Components\TextInput::make('slug')
                ->label('Slug (adres URL posta)')
                ->required()
                ->maxLength(255)
                ->unique(Post::class, 'slug', ignoreRecord: true),

            Forms\Components\FileUpload::make('cover_image')
                ->label('Zdjęcie główne')
                ->image()
                ->disk('r2')
                ->directory('covers')
                ->nullable(),

            TiptapEditor::make('content')
                ->label('Treść')
                ->tools([
                    'heading', 'hr', 'bullet-list', 'ordered-list',
                    'bold', 'italic', 'underline',
                    'link', 'table', 'media',
                ])
                ->nullable()
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('gallery')
                ->label('Galeria zdjęć')
                ->image()
                ->multiple()
                ->disk('r2')
                ->directory('galleries')
                ->reorderable()
                ->nullable()
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('published_at')
                ->label('Data publikacji')
                ->nullable()
                ->helperText('Zostaw puste aby zapisać jako szkic'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Opublikowany' : 'Szkic')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Krok 3: Zastąp CreatePost.php**

Zastąp zawartość `backend/app/Filament/Resources/PostResource/Pages/CreatePost.php`:

```php
<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected ?array $galleryPaths = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);
        return $data;
    }

    protected function afterCreate(): void
    {
        foreach (array_values($this->galleryPaths ?? []) as $order => $path) {
            $this->record->images()->create(['path' => $path, 'order' => $order]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

- [ ] **Krok 4: Zastąp EditPost.php**

Zastąp zawartość `backend/app/Filament/Resources/PostResource/Pages/EditPost.php`:

```php
<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected ?array $galleryPaths = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['gallery'] = $this->record->images()
            ->orderBy('order')
            ->pluck('path')
            ->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->galleryPaths = $data['gallery'] ?? [];
        unset($data['gallery']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->images()->delete();
        foreach (array_values($this->galleryPaths ?? []) as $order => $path) {
            $this->record->images()->create(['path' => $path, 'order' => $order]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

- [ ] **Krok 5: Zweryfikuj panel ręcznie**

```bash
cd backend && php artisan serve
```

Wejdź na `http://localhost:8000/admin/posts/create`. Oczekiwane: formularz z polami Tytuł, Slug, Zdjęcie główne, Treść (TipTap), Galeria, Data publikacji.

- [ ] **Krok 6: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/app/Filament/
git commit -m "feat: add PostResource with Filament admin panel"
```

---

## Task 11: Inicjalizacja Vue 3 frontend

**Files:**
- Create: `frontend/` (cały projekt Vue 3 + Vite)

- [ ] **Krok 1: Utwórz projekt Vue 3**

```bash
cd /Users/pav/Desktop/chess
npm create vue@latest frontend
```

Na pytania odpowiedz:
- TypeScript: **No**
- JSX: **No**
- Vue Router: **Yes**
- Pinia: **No**
- Vitest: **Yes**
- End-to-End Testing: **No**
- ESLint: **Yes**
- Prettier: **Yes**

- [ ] **Krok 2: Zainstaluj zależności**

```bash
cd frontend
npm install
npm install vue-easy-lightbox
```

- [ ] **Krok 3: Utwórz .env i .env.example**

Utwórz `frontend/.env`:

```dotenv
VITE_API_URL=http://localhost:8000
```

Utwórz `frontend/.env.example`:

```dotenv
VITE_API_URL=http://localhost:8000
```

- [ ] **Krok 4: Sprawdź że Vue działa**

```bash
npm run dev
```

Wejdź na `http://localhost:5173`. Oczekiwane: domyślna strona powitalna Vue.

- [ ] **Krok 5: Commit**

```bash
cd /Users/pav/Desktop/chess
git add frontend/
git commit -m "chore: init Vue 3 + Vite frontend"
```

---

## Task 12: Vue Router + api.js service

**Files:**
- Modify: `frontend/src/router/index.js`
- Create: `frontend/src/services/api.js`

- [ ] **Krok 1: Napisz test dla api.js**

Utwórz `frontend/src/services/__tests__/api.test.js`:

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { api } from '../api.js'

describe('api service', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
  })

  it('getPosts calls correct URL', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, title: 'Test' }],
    })

    const posts = await api.getPosts()

    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/posts')
    )
    expect(posts).toHaveLength(1)
  })

  it('getPost calls correct URL with slug', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 1, slug: 'test-post' }),
    })

    const post = await api.getPost('test-post')

    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/posts/test-post')
    )
    expect(post.slug).toBe('test-post')
  })

  it('getPosts throws on non-ok response', async () => {
    fetch.mockResolvedValueOnce({ ok: false, status: 500 })

    await expect(api.getPosts()).rejects.toThrow()
  })
})
```

- [ ] **Krok 2: Uruchom test — musi FAILOWAĆ**

```bash
cd frontend
npm run test:unit -- src/services/__tests__/api.test.js
```

Oczekiwane: FAIL — `api` not found.

- [ ] **Krok 3: Utwórz api.js**

Utwórz `frontend/src/services/api.js`:

```javascript
const BASE_URL = import.meta.env.VITE_API_URL ?? ''

async function request(path) {
  const response = await fetch(`${BASE_URL}${path}`)
  if (!response.ok) {
    throw new Error(`API error: ${response.status} ${path}`)
  }
  return response.json()
}

export const api = {
  getPosts: () => request('/api/posts'),
  getPost:  (slug) => request(`/api/posts/${slug}`),
}
```

- [ ] **Krok 4: Uruchom test — musi PRZEJŚĆ**

```bash
npm run test:unit -- src/services/__tests__/api.test.js
```

Oczekiwane: 3 testy PASS.

- [ ] **Krok 5: Zaktualizuj router**

Zastąp zawartość `frontend/src/router/index.js`:

```javascript
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../pages/Home.vue'),
    },
    {
      path: '/post/:slug',
      name: 'post',
      component: () => import('../pages/Post.vue'),
    },
    {
      path: '/o-mnie',
      name: 'about',
      component: () => import('../pages/About.vue'),
    },
    {
      path: '/kontakt',
      name: 'contact',
      component: () => import('../pages/Contact.vue'),
    },
  ],
})

export default router
```

- [ ] **Krok 6: Commit**

```bash
cd /Users/pav/Desktop/chess
git add frontend/src/services/ frontend/src/router/
git commit -m "feat: add api service and Vue Router config"
```

---

## Task 13: Komponenty układu — Navbar i Footer

**Files:**
- Create: `frontend/src/components/Navbar.vue`
- Create: `frontend/src/components/Footer.vue`
- Modify: `frontend/src/App.vue`

- [ ] **Krok 1: Utwórz Navbar.vue**

Utwórz `frontend/src/components/Navbar.vue`:

```vue
<template>
  <nav class="navbar">
    <RouterLink to="/" class="navbar__brand">♟ Szachy Taty</RouterLink>
    <ul class="navbar__links">
      <li><RouterLink to="/">Aktualności</RouterLink></li>
      <li><RouterLink to="/o-mnie">O mnie</RouterLink></li>
      <li><RouterLink to="/kontakt">Kontakt</RouterLink></li>
    </ul>
  </nav>
</template>

<style scoped>
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  background: #1a1a2e;
  color: white;
}
.navbar__brand {
  font-size: 1.4rem;
  font-weight: bold;
  color: white;
  text-decoration: none;
}
.navbar__links {
  display: flex;
  gap: 1.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
.navbar__links a {
  color: #ccc;
  text-decoration: none;
}
.navbar__links a:hover,
.navbar__links a.router-link-active {
  color: white;
}
</style>
```

- [ ] **Krok 2: Utwórz Footer.vue**

Utwórz `frontend/src/components/Footer.vue`:

```vue
<template>
  <footer class="footer">
    <p>© {{ year }} Szachy Taty</p>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>

<style scoped>
.footer {
  text-align: center;
  padding: 2rem;
  background: #1a1a2e;
  color: #aaa;
  margin-top: auto;
}
</style>
```

- [ ] **Krok 3: Zaktualizuj App.vue**

Zastąp zawartość `frontend/src/App.vue`:

```vue
<template>
  <div class="app">
    <Navbar />
    <main class="main">
      <RouterView />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
</script>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #f5f5f5; color: #222; }
.app { display: flex; flex-direction: column; min-height: 100vh; }
.main { flex: 1; max-width: 860px; margin: 0 auto; padding: 2rem 1rem; width: 100%; }
</style>
```

- [ ] **Krok 4: Commit**

```bash
cd /Users/pav/Desktop/chess
git add frontend/src/components/ frontend/src/App.vue
git commit -m "feat: add Navbar and Footer components"
```

---

## Task 14: PostCard component

**Files:**
- Create: `frontend/src/components/PostCard.vue`

- [ ] **Krok 1: Utwórz PostCard.vue**

Utwórz `frontend/src/components/PostCard.vue`:

```vue
<template>
  <article class="post-card">
    <RouterLink :to="{ name: 'post', params: { slug: post.slug } }" class="post-card__link">
      <img
        v-if="post.cover_image"
        :src="post.cover_image"
        :alt="post.title"
        class="post-card__image"
      />
      <div class="post-card__body">
        <time class="post-card__date">{{ formattedDate }}</time>
        <h2 class="post-card__title">{{ post.title }}</h2>
        <p class="post-card__excerpt">{{ post.excerpt }}</p>
        <span class="post-card__more">Czytaj więcej →</span>
      </div>
    </RouterLink>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
})

const formattedDate = computed(() => {
  if (!props.post.published_at) return ''
  return new Date(props.post.published_at).toLocaleDateString('pl-PL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})
</script>

<style scoped>
.post-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
  margin-bottom: 2rem;
}
.post-card__link {
  display: block;
  text-decoration: none;
  color: inherit;
}
.post-card__image {
  width: 100%;
  height: 280px;
  object-fit: cover;
  display: block;
}
.post-card__body {
  padding: 1.5rem;
}
.post-card__date {
  font-size: .85rem;
  color: #888;
  display: block;
  margin-bottom: .4rem;
}
.post-card__title {
  font-size: 1.4rem;
  margin-bottom: .75rem;
  color: #1a1a2e;
}
.post-card__excerpt {
  color: #555;
  line-height: 1.6;
  margin-bottom: 1rem;
}
.post-card__more {
  color: #4f46e5;
  font-weight: 600;
  font-size: .9rem;
}
.post-card:hover .post-card__more {
  text-decoration: underline;
}
</style>
```

- [ ] **Krok 2: Commit**

```bash
cd /Users/pav/Desktop/chess
git add frontend/src/components/PostCard.vue
git commit -m "feat: add PostCard component"
```

---

## Task 15: Home.vue — lista postów

**Files:**
- Create: `frontend/src/pages/Home.vue`

- [ ] **Krok 1: Usuń przykładowe pliki Vue (jeśli istnieją)**

```bash
rm -f frontend/src/views/HomeView.vue frontend/src/views/AboutView.vue
rmdir frontend/src/views 2>/dev/null || true
mkdir -p frontend/src/pages
```

- [ ] **Krok 2: Utwórz Home.vue**

Utwórz `frontend/src/pages/Home.vue`:

```vue
<template>
  <div>
    <h1 class="page-title">Aktualności</h1>

    <div v-if="loading" class="state">Ładowanie...</div>

    <div v-else-if="error" class="state state--error">
      Nie udało się załadować postów. Spróbuj ponownie później.
    </div>

    <div v-else-if="posts.length === 0" class="state">
      Brak postów.
    </div>

    <template v-else>
      <PostCard v-for="post in posts" :key="post.id" :post="post" />
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../services/api.js'
import PostCard from '../components/PostCard.vue'

const posts   = ref([])
const loading = ref(true)
const error   = ref(false)

onMounted(async () => {
  try {
    posts.value = await api.getPosts()
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page-title {
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: 1.5rem;
}
.state {
  text-align: center;
  padding: 3rem;
  color: #888;
}
.state--error { color: #c0392b; }
</style>
```

- [ ] **Krok 3: Commit**

```bash
cd /Users/pav/Desktop/chess
git add frontend/src/pages/Home.vue
git commit -m "feat: add Home page with post list"
```

---

## Task 16: PhotoGallery component + Post.vue

**Files:**
- Create: `frontend/src/components/PhotoGallery.vue`
- Create: `frontend/src/pages/Post.vue`

- [ ] **Krok 1: Utwórz PhotoGallery.vue**

Utwórz `frontend/src/components/PhotoGallery.vue`:

```vue
<template>
  <div v-if="images.length > 0" class="gallery">
    <h3 class="gallery__title">Zdjęcia</h3>
    <div class="gallery__grid">
      <button
        v-for="(img, index) in images"
        :key="img.path"
        class="gallery__thumb-btn"
        @click="openLightbox(index)"
      >
        <img :src="img.path" :alt="`Zdjęcie ${index + 1}`" class="gallery__thumb" />
      </button>
    </div>

    <vue-easy-lightbox
      :visible="lightboxVisible"
      :imgs="lightboxImages"
      :index="lightboxIndex"
      @hide="lightboxVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import VueEasyLightbox from 'vue-easy-lightbox'

const props = defineProps({
  images: {
    type: Array,
    default: () => [],
  },
})

const lightboxVisible = ref(false)
const lightboxIndex   = ref(0)

const lightboxImages = computed(() =>
  props.images.map((img) => ({ src: img.path }))
)

function openLightbox(index) {
  lightboxIndex.value   = index
  lightboxVisible.value = true
}
</script>

<style scoped>
.gallery { margin-top: 2.5rem; }
.gallery__title {
  font-size: 1.1rem;
  color: #1a1a2e;
  margin-bottom: 1rem;
}
.gallery__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: .75rem;
}
.gallery__thumb-btn {
  border: none;
  padding: 0;
  cursor: pointer;
  border-radius: 6px;
  overflow: hidden;
  aspect-ratio: 1;
}
.gallery__thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: opacity .2s;
}
.gallery__thumb-btn:hover .gallery__thumb { opacity: .85; }
</style>
```

- [ ] **Krok 2: Utwórz Post.vue**

Utwórz `frontend/src/pages/Post.vue`:

```vue
<template>
  <div>
    <div v-if="loading" class="state">Ładowanie...</div>

    <div v-else-if="error" class="state state--error">
      Post nie został znaleziony.
    </div>

    <article v-else-if="post" class="post">
      <img
        v-if="post.cover_image"
        :src="post.cover_image"
        :alt="post.title"
        class="post__cover"
      />

      <header class="post__header">
        <time class="post__date">{{ formattedDate }}</time>
        <h1 class="post__title">{{ post.title }}</h1>
      </header>

      <div class="post__content" v-html="post.content" />

      <PhotoGallery :images="post.images ?? []" />
    </article>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute }                 from 'vue-router'
import { api }                      from '../services/api.js'
import PhotoGallery                 from '../components/PhotoGallery.vue'

const route   = useRoute()
const post    = ref(null)
const loading = ref(true)
const error   = ref(false)

onMounted(async () => {
  try {
    post.value = await api.getPost(route.params.slug)
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
})

const formattedDate = computed(() => {
  if (!post.value?.published_at) return ''
  return new Date(post.value.published_at).toLocaleDateString('pl-PL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})
</script>

<style scoped>
.state { text-align: center; padding: 3rem; color: #888; }
.state--error { color: #c0392b; }

.post__cover {
  width: 100%;
  max-height: 420px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  display: block;
}
.post__header { margin-bottom: 1.5rem; }
.post__date { font-size: .85rem; color: #888; display: block; margin-bottom: .5rem; }
.post__title { font-size: 2rem; color: #1a1a2e; }

.post__content {
  line-height: 1.75;
  color: #333;
}
/* Style dla treści WYSIWYG z TipTap */
.post__content :deep(h1),
.post__content :deep(h2),
.post__content :deep(h3) {
  color: #1a1a2e;
  margin: 1.5rem 0 .75rem;
}
.post__content :deep(p)  { margin-bottom: 1rem; }
.post__content :deep(ul),
.post__content :deep(ol) { padding-left: 1.5rem; margin-bottom: 1rem; }
.post__content :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 1rem;
}
.post__content :deep(th),
.post__content :deep(td) {
  border: 1px solid #ddd;
  padding: .5rem .75rem;
  text-align: left;
}
.post__content :deep(th) { background: #f0f0f0; font-weight: 600; }
</style>
```

- [ ] **Krok 3: Commit**

```bash
cd /Users/pav/Desktop/chess
git add frontend/src/components/PhotoGallery.vue frontend/src/pages/Post.vue
git commit -m "feat: add Post page and PhotoGallery component"
```

---

## Task 17: Strony statyczne — About i Contact

**Files:**
- Create: `frontend/src/pages/About.vue`
- Create: `frontend/src/pages/Contact.vue`

- [ ] **Krok 1: Utwórz About.vue**

Utwórz `frontend/src/pages/About.vue`:

```vue
<template>
  <div class="static-page">
    <h1>O mnie</h1>
    <p>
      Gram w szachy od wielu lat. Organizuję turnieje lokalne i uczestniczę
      w rozgrywkach klubowych. Ta strona to miejsce, gdzie dzielę się relacjami
      z turniejów i aktualnościami ze świata szachów.
    </p>
  </div>
</template>

<style scoped>
.static-page h1 {
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: 1rem;
}
.static-page p {
  line-height: 1.75;
  color: #444;
  max-width: 640px;
}
</style>
```

- [ ] **Krok 2: Utwórz Contact.vue**

Utwórz `frontend/src/pages/Contact.vue`:

```vue
<template>
  <div class="static-page">
    <h1>Kontakt</h1>
    <p>W razie pytań dotyczących turniejów możesz napisać na adres:</p>
    <p><a href="mailto:tata@example.com">tata@example.com</a></p>
  </div>
</template>

<style scoped>
.static-page h1 {
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: 1rem;
}
.static-page p {
  line-height: 1.75;
  color: #444;
  margin-bottom: .75rem;
}
.static-page a { color: #4f46e5; }
</style>
```

> Zmień treść i email na rzeczywiste dane Taty.

- [ ] **Krok 3: Commit**

```bash
cd /Users/pav/Desktop/chess
git add frontend/src/pages/About.vue frontend/src/pages/Contact.vue
git commit -m "feat: add static About and Contact pages"
```

---

## Task 18: Końcowa weryfikacja lokalna

- [ ] **Krok 1: Uruchom backend**

```bash
cd /Users/pav/Desktop/chess/backend
php artisan serve
```

- [ ] **Krok 2: Uruchom frontend (osobny terminal)**

```bash
cd /Users/pav/Desktop/chess/frontend
npm run dev
```

- [ ] **Krok 3: Utwórz testowy post przez panel admina**

1. Wejdź na `http://localhost:8000/admin`
2. Kliknij "Posty" → "Nowy post"
3. Wpisz tytuł, dodaj treść w TipTap (z tabelką), wstaw datę publikacji
4. Dodaj kilka zdjęć do galerii
5. Zapisz

- [ ] **Krok 4: Zweryfikuj frontend**

Wejdź na `http://localhost:5173`. Oczekiwane:
- Post widoczny na liście z excerpt i przyciskiem "Czytaj więcej"
- Po kliknięciu — pełny post z treścią i galerią miniaturek
- Kliknięcie miniaturki otwiera lightbox

- [ ] **Krok 5: Uruchom wszystkie testy**

```bash
cd /Users/pav/Desktop/chess/backend && php artisan test
cd /Users/pav/Desktop/chess/frontend && npm run test:unit
```

Oczekiwane: wszystkie testy PASS.

---

## Task 19: Konfiguracja Railway deployment

**Files:**
- Create: `backend/nixpacks.toml`
- Create: `frontend/.env.production`

- [ ] **Krok 1: Utwórz Procfile dla backendu**

Utwórz `backend/Procfile`:

```
web: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

Utwórz `backend/nixpacks.toml` (tylko build, start obsługuje Procfile):

```toml
[phases.build]
cmds = [
  "composer install --no-dev --optimize-autoloader",
  "php artisan config:cache",
  "php artisan route:cache",
  "php artisan view:cache"
]
```

- [ ] **Krok 2: Utwórz .env.production dla frontendu**

Utwórz `frontend/.env.production`:

```dotenv
VITE_API_URL=https://chess-backend.up.railway.app
```

> Zmień URL na faktyczny URL backendu po deploymencie na Railway.

- [ ] **Krok 3: Dodaj README z instrukcją deploymentu**

Utwórz `backend/RAILWAY.md`:

```markdown
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

## Konfiguracja R2 (jednorazowo):
1. Zaloguj się na dash.cloudflare.com
2. R2 → Create bucket → nazwa: `chess`
3. Settings → Public Access → Enable
4. Manage API Tokens → Create Token (Object Read & Write)
```

- [ ] **Krok 4: Commit**

```bash
cd /Users/pav/Desktop/chess
git add backend/nixpacks.toml backend/RAILWAY.md frontend/.env.production
git commit -m "chore: add Railway deployment config"
```

---

## Podsumowanie

Po wykonaniu wszystkich tasków masz:
- ✅ Monorepo z backendem Laravel 13 i frontendem Vue 3
- ✅ Panel admina Filament pod `/admin` z edytorem TipTap i uploadem galerii
- ✅ Publiczne API JSON (`/api/posts`, `/api/posts/{slug}`)
- ✅ SPA Vue z listą postów (z excerpt), widokiem posta i lightbox galerią
- ✅ Statyczne strony O mnie i Kontakt
- ✅ Testy feature dla API i unit testy dla api.js
- ✅ Konfiguracja Railway deployment
