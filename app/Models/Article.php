<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ArticleFactory;
use App\Models\User;
use App\Models\ArticleTag;
use App\Models\ArticleTagRelation;

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
        'status'
    ];

    // Accesssor, Mutator, Casts
    
    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => [
                'title' => $value,
                'slug' => \Str::slug($value),
            ]
        );
    }

    // Relationships

    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => [
                'status' => $value,
                'published_at' => $value === 'published' ? now() : null,
            ]
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
