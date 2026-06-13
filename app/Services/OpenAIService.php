<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    /**
     * Analyze a ticket using OpenAI API
     *
     * @param string $subject
     * @param string $body
     * @param int $ownerId
     * @param string $customerName
     * @return array|null
     */
    public static function analyzeTicket(string $subject, string $body, int $ownerId, string $customerName = 'Customer')
    {
        $owner = \App\Models\User::find($ownerId);

        if (!$owner) {
            Log::warning("Owner ID {$ownerId} not found. Skipping AI analysis.");
            return null;
        }

        // Fetch API key from UserSettings
        $apiKey = $owner->getSetting('secret_key');

        if (empty($apiKey)) {
            Log::warning("OpenAI API Key is missing for Owner ID {$ownerId}. Skipping AI analysis.");
            return null;
        }

        // Clean body to save tokens
        $cleanBody = strip_tags($body);
        $cleanBody = substr($cleanBody, 0, 1500); // Limit length

        $prompt = "You are a customer support AI assistant. Analyze the following customer email and provide a JSON response.
        Customer Name: {$customerName}
        Subject: {$subject}
        Email Body: {$cleanBody}
        Respond ONLY with a valid JSON object in this exact format:
        {
            \"category\": \"(Determine the category, e.g., Order Status, Refund Request, Product Inquiry, General Inquiry)\",
            \"confidence\": (integer between 0 and 100 representing how confident you are in your understanding and suggested reply),
            \"tags\": [\"(array of 2-3 relevant tags)\"],
            \"reason\": \"(Brief explanation of why you gave this category and confidence score)\",
            \"summary\": \"(A short 1-2 sentence summary of the customer's issue)\",
            \"suggested_reply\": \"(Write a complete, professional, and helpful reply to the customer addressing their issue)\"
        }";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful customer support assistant. You must output only valid JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                
                // Try to parse the JSON
                $data = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
            }

            Log::error('OpenAI API Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
        }

        return null;
    }
}
