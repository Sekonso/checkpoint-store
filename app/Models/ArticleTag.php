<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ArticleTagFactory;
use App\Models\Article;
use App\Models\ArticleTagRelation;

class ArticleTag extends Model
{
    /** @use HasFactory<ArticleTagFactory> */
    use HasFactory;

    protected $table = 'article_tags';

    protected $fillable = [
        'name'
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            ArticleTagRelation::class,
            'tag_id',
            'article_id'
        );
    }
}
