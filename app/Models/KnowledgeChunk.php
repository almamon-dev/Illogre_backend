<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_source_id',
        'title',
        'content',
        'category',
        'status',
    ];

    public function source()
    {
        return $this->belongsTo(KnowledgeSource::class, 'knowledge_source_id');
    }
}
