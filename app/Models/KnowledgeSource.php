<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeSource extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'file_path',
        'file_size',
        'content_type',
        'error_message',
        'is_indexed',
        'content',
    ];

    protected $casts = [
        'is_indexed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
