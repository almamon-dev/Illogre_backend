<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeSource;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class KnowledgeExtractionService
{
    /**
     * Extract text content from a knowledge source.
     */
    public function extractContent(KnowledgeSource $source): bool
    {
        try {
            $content = '';

            switch ($source->type) {
                case 'file':
                    $content = $this->extractFromFile($source);
                    break;
                case 'url':
                    $content = $this->extractFromUrl($source->file_path); // file_path stores URL for type=url
                    break;
                case 'text':
                    $content = $source->content; // Already stored in store()
                    break;
            }

            if (empty($content)) {
                throw new Exception('No content could be extracted from the source.');
            }

            $source->update([
                'content' => $content,
                'is_indexed' => true,
                'error_message' => null,
            ]);

            // Create Chunks
            $this->createChunks($source, $content);

            return true;

        } catch (Exception $e) {
            Log::error("Knowledge extraction failed for source #{$source->id}: " . $e->getMessage());
            
            $source->update([
                'is_indexed' => false,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Extract text from a file (PDF, TXT, etc.)
     */
    private function extractFromFile(KnowledgeSource $source): string
    {
        $filePath = Storage::disk('public')->path($source->file_path);

        if (!file_exists($filePath)) {
            throw new Exception("File not found at: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        }

        if ($extension === 'txt') {
            return file_get_contents($filePath);
        }

        if ($extension === 'docx') {
            // Basic docx text extraction (XML parsing)
            return $this->extractFromDocx($filePath);
        }

        throw new Exception("Unsupported file type for extraction: {$extension}");
    }

    /**
     * Simple docx text extraction.
     */
    private function extractFromDocx($filePath): string
    {
        $content = '';
        $zip = zip_open($filePath);

        if (!$zip || is_numeric($zip)) return $content;

        while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }

        zip_close($zip);

        $content = str_replace('</w:r></w:p><w:p>', " ", $content);
        $content = str_replace('</w:r>', " ", $content);

        return strip_tags($content);
    }

    /**
     * Extract text from a URL.
     */
    private function extractFromUrl($url): string
    {
        // For now, just a simple strip_tags of the HTML
        // In a real app, you'd use a better scraper or headless browser
        $html = file_get_contents($url);
        if ($html === false) {
            throw new Exception("Failed to fetch content from URL: {$url}");
        }

        return strip_tags($html);
    }

    /**
     * Split text into chunks and save to database.
     */
    private function createChunks(KnowledgeSource $source, string $content): void
    {
        // Simple paragraph splitting for now
        $paragraphs = array_filter(array_map('trim', explode("\n", $content)));
        
        $chunkIndex = 1;
        $currentChunk = '';
        $maxLength = 1000; // rough character limit per chunk

        foreach ($paragraphs as $paragraph) {
            if (empty($paragraph)) continue;

            if (strlen($currentChunk) + strlen($paragraph) > $maxLength) {
                // Save current chunk
                if (!empty(trim($currentChunk))) {
                    \App\Models\KnowledgeChunk::create([
                        'knowledge_source_id' => $source->id,
                        'title' => 'Article Section ' . $chunkIndex,
                        'content' => trim($currentChunk),
                        'category' => 'General',
                        'status' => 'Indexed', // Or Processing if we add OpenAI vectors later
                    ]);
                    $chunkIndex++;
                }
                $currentChunk = $paragraph;
            } else {
                $currentChunk .= "\n\n" . $paragraph;
            }
        }

        // Save the last chunk
        if (!empty(trim($currentChunk))) {
            \App\Models\KnowledgeChunk::create([
                'knowledge_source_id' => $source->id,
                'title' => 'Article Section ' . $chunkIndex,
                'content' => trim($currentChunk),
                'category' => 'General',
                'status' => 'Indexed',
            ]);
        }
    }
}
