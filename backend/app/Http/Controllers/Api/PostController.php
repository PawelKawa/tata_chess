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
