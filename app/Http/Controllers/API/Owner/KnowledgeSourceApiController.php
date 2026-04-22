<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Owner\UploadKnowledgeRequest;
use App\Http\Resources\Owner\KnowledgeSourceResource;
use App\Models\KnowledgeSource;
use App\Services\Knowledge\KnowledgeExtractionService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KnowledgeSourceApiController extends Controller
{
    use ApiResponse;

    protected $extractionService;

    public function __construct(KnowledgeExtractionService $extractionService)
    {
        $this->extractionService = $extractionService;
    }

    public function index(Request $request): JsonResponse
    {
        $sources = KnowledgeSource::where('user_id', auth()->id())
            ->latest()
            ->get();

        return $this->sendResponse(
            KnowledgeSourceResource::collection($sources),
            'Knowledge sources retrieved successfully.'
        );
    }

    public function store(UploadKnowledgeRequest $request): JsonResponse
    {
        $user = auth()->user();

        // Check if AI is configured
        if (! $user->isAiConfigured()) {
            return $this->sendError('AI setup is incomplete. Please configure your AI settings (API key and Provider) in your profile first.', [], 403);
        }
        try {
            $data = $request->validated();
            $data['user_id'] = $user->id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('knowledge-base', 'public');

                $data['file_path'] = $path;
                $data['file_size'] = $this->formatBytes($file->getSize());
                $data['content_type'] = $file->getClientOriginalExtension();
                $data['name'] = $data['name'] ?? $file->getClientOriginalName();
            }

            if ($data['type'] === 'text') {
                $data['content'] = $request->text;
            }

            if ($data['type'] === 'url') {
                $data['file_path'] = $request->url; // Store URL in file_path column
            }

            $source = KnowledgeSource::create($data);

            // Trigger extraction
            $this->extractionService->extractContent($source);

            return $this->sendResponse(
                new KnowledgeSourceResource($source),
                'Knowledge source added and indexed successfully.'
            );

        } catch (Exception $e) {
            return $this->sendError('Failed to upload knowledge source: '.$e->getMessage());
        }
    }

    public function destroy(KnowledgeSource $knowledgeSource): JsonResponse
    {
        if ($knowledgeSource->user_id !== auth()->id()) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        if ($knowledgeSource->file_path) {
            Storage::disk('public')->delete($knowledgeSource->file_path);
        }

        $knowledgeSource->delete();

        return $this->sendResponse([], 'Knowledge source deleted successfully.');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
