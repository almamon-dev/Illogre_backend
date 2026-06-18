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
     * @param mixed $recentOrders
     * @return array|null
     */
    public static function analyzeTicket(string $subject, string $body, int $ownerId, string $customerName = 'Customer', $recentOrders = [])
    {
        $owner = \App\Models\User::find($ownerId);

        if (!$owner) {
            Log::warning("Owner ID {$ownerId} not found. Skipping AI analysis.");
            return null;
        }

        // Fetch API key from Global Settings first
        $apiKey = env('OPENAI_API_KEY');

        if (empty($apiKey)) {
            // Fallback to UserSettings if not set globally
            $apiKey = $owner->getSetting('secret_key');
        }

        if (empty($apiKey)) {
            Log::warning("OpenAI API Key is missing. Skipping AI analysis.");
            return null;
        }

        // Clean body to save tokens
        $cleanBody = strip_tags($body);
        $cleanBody = substr($cleanBody, 0, 1500); // Limit length
        
        // Format recent orders context
        $orderContext = "No recent orders found for this customer.";
        if (!empty($recentOrders) && count($recentOrders) > 0) {
            $orderContext = json_encode($recentOrders);
        }

        $prompt = <<<EOT
            You are an elite, empathetic, and highly professional customer success specialist. Your objective is to analyze the incoming customer inquiry, interpret their intent accurately, and draft a flawless, brand-aligned response using the provided system context.

            --- CUSTOMER INQUIRY ---
            Customer Name: {$customerName}
            Subject: {$subject}
            Email Body: {$cleanBody}

            --- SYSTEM CONTEXT (Recent Orders) ---
            {$orderContext}

            --- RULES FOR CONFIDENCE RATING & REPLIES ---
            1. ORDER INQUIRIES: If the inquiry concerns order status, tracking, or refunds, verify against the 'System Context'. 
            - If the relevant order data is present, set Confidence to 85-100 and draft a precise, helpful reply.
            - If the data is MISSING or ambiguous, you MUST set Confidence below 50 and politely ask the customer to provide their exact order number.
            2. TONE & STYLE: Draft the 'suggested_reply' with a warm, professional, and empathetic tone. Speak on behalf of the company (e.g., "We are happy to help"). Avoid overly robotic phrasing.
            3. NO HALLUCINATION: Never invent policies, timelines, or promises. If the inquiry requires human judgment, set Confidence below 50 and draft a courteous message assuring them an agent is reviewing their case.
            4. CONCISENESS: Keep the response clear, directly addressing the customer's concern without unnecessary filler.

            --- REQUIRED JSON OUTPUT FORMAT ---
            {
                "category": "(One of: Order Status, Refund/Return, Product Inquiry, Technical Issue, General Inquiry, Other)",
                "confidence": (Integer 0-100 based strictly on the rules above),
                "tags": ["(2-3 relevant descriptive tags, e.g., 'urgent', 'shipping')"],
                "reason": "(A brief analytical explanation justifying your category and confidence score)",
                "summary": "(A concise 1-2 sentence summary of the customer's core issue)",
                "suggested_reply": "(The complete, polished email response ready to be sent to the customer)"
            }
            EOT;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => env('AI_MODEL', 'gpt-4o'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an elite customer support AI. You must adhere strictly to instructions and always output valid JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 600,
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
