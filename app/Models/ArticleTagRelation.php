<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTagRelation extends Model
{
    protected $table = 'article_tag_relations';

    protected $fillable = [
        'article_id',
        'article_tag_id',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function article_tag(): BelongsTo
    {
        return $this->belongsTo(ArticleTag::class);
    }
}
