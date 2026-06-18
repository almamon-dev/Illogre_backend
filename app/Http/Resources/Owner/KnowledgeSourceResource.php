<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeSourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'file_size' => $this->file_size,
            'content_type' => $this->content_type,
            'error_message' => $this->error_message,
            'is_indexed' => $this->is_indexed,
            'article_count' => $this->whenLoaded('chunks', function() { return $this->chunks->count(); }, 0),
            'chunks' => $this->whenLoaded('chunks', function() {
                return $this->chunks->map(function($chunk) {
                    return [
                        'id' => $chunk->id,
                        'title' => $chunk->title,
                        'content' => $chunk->content,
                        'category' => $chunk->category,
                        'status' => $chunk->status,
                        'lastUpdated' => $chunk->updated_at->format('M d, Y'),
                    ];
                });
            }),
            'created_at' => $this->created_at->format('M d, Y'),
            'updated_at' => $this->updated_at->format('M d, Y'),
        ];
    }
}
