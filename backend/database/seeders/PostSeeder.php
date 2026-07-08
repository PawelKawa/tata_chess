<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Post 1 — relacja z turnieju z tabelą wyników i galerią
        $post1 = Post::create([
            'title'        => 'Turniej Majowy 2026 — relacja',
            'slug'         => 'turniej-majowy-2026-relacja',
            'cover_image'  => 'https://picsum.photos/seed/chesscover1/1200/600',
            'published_at' => now()->subDays(5),
            'content'      => <<<HTML
<h2>Wielki sukces turnieju majowego!</h2>
<p>W maju rozegraliśmy kolejną edycję naszego corocznego turnieju szachowego. Tym razem wzięło udział aż 16 zawodników z całego regionu. Poziom był bardzo wyrównany, a walka toczyła się do ostatniej partii.</p>
<p>Turniej rozgrywany był systemem szwajcarskim w 7 rundach. Każdy uczestnik miał po 30 minut na całą partię z 10-sekundowym inkременtem.</p>
<h2>Wyniki końcowe</h2>
<table>
  <thead>
    <tr><th>Miejsce</th><th>Zawodnik</th><th>Punkty</th><th>Buchholz</th></tr>
  </thead>
  <tbody>
    <tr><td>1</td><td>Kowalski Jan</td><td>6,5</td><td>32,5</td></tr>
    <tr><td>2</td><td>Nowak Piotr</td><td>6,0</td><td>31,0</td></tr>
    <tr><td>3</td><td>Wiśniewski Adam</td><td>5,5</td><td>30,5</td></tr>
    <tr><td>4</td><td>Zając Marek</td><td>5,0</td><td>29,0</td></tr>
    <tr><td>5</td><td>Dąbrowski Łukasz</td><td>4,5</td><td>27,5</td></tr>
  </tbody>
</table>
<p>Gratulujemy wszystkim uczestnikom, a zwycięzcy — Janowi Kowalskiemu — szczególnie. Była to jego czwarta z rzędu wygrana w naszym turnieju!</p>
<h2>Nagrody</h2>
<p>Trzej pierwsi zawodnicy otrzymali puchary i nagrody książkowe. Wszyscy uczestnicy dostali dyplomy uczestnictwa.</p>
HTML,
        ]);

        // Galeria — placeholdery z picsum.photos (seed = zawsze te same zdjęcia)
        PostImage::create(['post_id' => $post1->id, 'path' => 'https://picsum.photos/seed/chess1/800/600', 'order' => 0]);
        PostImage::create(['post_id' => $post1->id, 'path' => 'https://picsum.photos/seed/chess2/800/600', 'order' => 1]);
        PostImage::create(['post_id' => $post1->id, 'path' => 'https://picsum.photos/seed/chess3/800/600', 'order' => 2]);
        PostImage::create(['post_id' => $post1->id, 'path' => 'https://picsum.photos/seed/chess4/800/600', 'order' => 3]);

        // Post 2 — zapowiedź turnieju (bez galerii)
        Post::create([
            'title'        => 'Zapraszamy na Turniej Letni 2026',
            'slug'         => 'zapraszamy-na-turniej-letni-2026',
            'cover_image'  => 'https://picsum.photos/seed/chesscover2/1200/600',
            'published_at' => now()->subDays(2),
            'content'      => <<<HTML
<h2>Już wkrótce — Turniej Letni 2026</h2>
<p>Z przyjemnością ogłaszamy, że w sierpniu odbędzie się nasz Turniej Letni! To tradycyjnie największa impreza szachowa w naszym kalendarzu.</p>
<h2>Szczegóły</h2>
<ul>
  <li><strong>Data:</strong> 15 sierpnia 2026</li>
  <li><strong>Miejsce:</strong> Dom Kultury, sala 12</li>
  <li><strong>Format:</strong> System szwajcarski, 9 rund</li>
  <li><strong>Tempo:</strong> 60 minut + 30 sekund inkrement</li>
  <li><strong>Wpisowe:</strong> 20 zł (dzieci do lat 16 bezpłatnie)</li>
</ul>
<p>Zapisy przyjmujemy do 10 sierpnia. Liczba miejsc ograniczona do 24 uczestników. Do zobaczenia przy szachownicy!</p>
HTML,
        ]);

        // Post 3 — szkic (nieopublikowany) — widoczny w panelu Filament, nie na stronie
        Post::create([
            'title'        => 'Wyniki rozgrywek ligowych (szkic)',
            'slug'         => 'wyniki-rozgrywek-ligowych',
            'cover_image'  => null,
            'published_at' => null,
            'content'      => '<p>Ten post jest jeszcze w przygotowaniu...</p>',
        ]);

        // Post 4 — starszy post z galerią
        $post4 = Post::create([
            'title'        => 'Podsumowanie sezonu 2025/2026',
            'slug'         => 'podsumowanie-sezonu-2025-2026',
            'cover_image'  => 'https://picsum.photos/seed/chesscover4/1200/600',
            'published_at' => now()->subMonths(2),
            'content'      => <<<HTML
<h2>Miniony sezon za nami</h2>
<p>Sezon 2025/2026 był dla nas wyjątkowo udany. Rozegraliśmy łącznie 8 turniejów, w których wzięło udział ponad 60 różnych zawodników.</p>
<p>Szczególnie cieszą nas młode talenty, które po raz pierwszy wzięły udział w naszych rozgrywkach.</p>
<h2>Statystyki sezonu</h2>
<table>
  <thead>
    <tr><th>Turniej</th><th>Uczestnicy</th><th>Miesiąc</th></tr>
  </thead>
  <tbody>
    <tr><td>Turniej Noworoczny</td><td>12</td><td>Styczeń 2026</td></tr>
    <tr><td>Walentynkowy Błyskawice</td><td>8</td><td>Luty 2026</td></tr>
    <tr><td>Turniej Wiosenny</td><td>14</td><td>Marzec 2026</td></tr>
    <tr><td>Turniej Majowy</td><td>16</td><td>Maj 2026</td></tr>
  </tbody>
</table>
<p>Dziękujemy wszystkim uczestnikom za wspólną zabawę. Do zobaczenia w nowym sezonie!</p>
HTML,
        ]);

        PostImage::create(['post_id' => $post4->id, 'path' => 'https://picsum.photos/seed/chess5/800/600', 'order' => 0]);
        PostImage::create(['post_id' => $post4->id, 'path' => 'https://picsum.photos/seed/chess6/800/600', 'order' => 1]);
    }
}
