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
