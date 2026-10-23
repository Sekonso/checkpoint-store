<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected $table = 'articles';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'featured_image',
        'status',
    ];

    // Accesssor, Mutator, Casts

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => [
                'title' => $value,
                'slug' => $this->generateUniqueSlug($value),
            ]
        );
    }

    /**
     * Generate a unique slug for the article title.
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = \Str::slug($title);
        $slug = $baseSlug;
        $suffix = 2;

        while (
            self::query()
                ->where('slug', $slug)
                ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    // Relationships

    protected function status(): Attribute
    {
        return Attribute::make(
            set: function (string $value) {
                $attributes = ['status' => $value];

                if ($value !== 'published') {
                    $attributes['published_at'] = null;
                } elseif ($this->getOriginal('status') !== 'published' || $this->published_at === null) {
                    $attributes['published_at'] = now();
                }

                return $attributes;
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            ArticleTag::class,
            ArticleTagRelation::class,
            'article_id',
            'article_tag_id'
        );
    }
}
